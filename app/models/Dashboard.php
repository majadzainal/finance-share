<?php

namespace App\Models;

use App\Core\Model;

class Dashboard extends Model
{
    public function summary(array $filters = []): array
    {
        $income = $this->sum('trx_incomes', 'amount', $this->combineWhere([
            'deleted_at IS NULL',
            $this->dateWhere('transaction_date', $filters),
        ]));
        $expense = $this->sum('trx_expenses', 'amount', $this->combineWhere([
            "approval_status = 'approved'",
            $this->dateWhere('expense_date', $filters),
        ]));
        $cashAdvance = $this->sum('trx_cash_advances', 'remaining_amount', $this->combineWhere([
            'status = 0 AND remaining_amount > 0',
            $this->dateWhere('advance_date', $filters),
        ]));
        $cashAdvanceDisbursed = $this->sum('trx_cash_advances', 'amount', $this->dateWhere('advance_date', $filters));

        return [
            'total_income' => $income,
            'total_expense' => $expense,
            'outstanding_cash_advance' => $cashAdvance,
            'cash_advance_disbursed' => $cashAdvanceDisbursed,
            'expected_balance' => $income - $expense - $cashAdvanceDisbursed,
            'open_closings' => $this->countRows('trx_closings', "status IN ('draft', 'closed')"),
            'pending_distributions' => $this->countRows('trx_profit_distributions', "status = 'pending'"),
            'draft_expenses' => $this->countRows('trx_expenses', "approval_status = 'draft' AND closing_id IS NULL"),
            'active_groups' => $this->countRows('mst_groups', 'is_active = 1'),
            'active_members' => $this->countRows('mst_members', 'is_active = 1'),
        ];
    }

    public function accountHealth(): array
    {
        $income = $this->sum('trx_incomes', 'amount', 'deleted_at IS NULL');
        $expense = $this->sum('trx_expenses', 'amount', "approval_status = 'approved'");
        $cashAdvance = $this->sum('trx_cash_advances', 'remaining_amount', 'status = 0 AND remaining_amount > 0');
        $cashAdvanceDisbursed = $this->sum('trx_cash_advances', 'amount');
        $manualBalance = $this->sum('mst_balance_accounts', 'current_balance', 'is_active = 1');
        $expectedBalance = $income - $expense - $cashAdvanceDisbursed;

        return [
            'total_income' => $income,
            'total_expense' => $expense,
            'outstanding_cash_advance' => $cashAdvance,
            'cash_advance_disbursed' => $cashAdvanceDisbursed,
            'expected_balance' => $expectedBalance,
            'manual_balance' => $manualBalance,
            'balance_difference' => $manualBalance - $expectedBalance,
            'is_balanced' => abs($manualBalance - $expectedBalance) < 0.01,
        ];
    }

    public function actionNeeded(array $summary, array $accountHealth): array
    {
        $actions = [];

        if (! $accountHealth['is_balanced']) {
            $actions[] = [
                'label' => 'Saldo rekening belum balance',
                'description' => 'Selisih Rp ' . number_format(abs($accountHealth['balance_difference']), 0, ',', '.') . ' dari perhitungan terkini.',
                'url' => url('/account-balances'),
                'tone' => 'danger',
            ];
        }

        if ($accountHealth['outstanding_cash_advance'] > 0) {
            $actions[] = [
                'label' => 'Kasbon masih outstanding',
                'description' => 'Total sisa kasbon Rp ' . number_format($accountHealth['outstanding_cash_advance'], 0, ',', '.') . '.',
                'url' => url('/cash-advances'),
                'tone' => 'warning',
            ];
        }

        if ($summary['pending_distributions'] > 0) {
            $actions[] = [
                'label' => 'Distribusi profit pending',
                'description' => $summary['pending_distributions'] . ' distribusi belum ditandai paid.',
                'url' => url('/profit-distribution'),
                'tone' => 'primary',
            ];
        }

        if (($summary['draft_expenses'] ?? 0) > 0) {
            $actions[] = [
                'label' => 'Expense menunggu approval',
                'description' => $summary['draft_expenses'] . ' expense masih draft dan belum ikut closing.',
                'url' => url('/expenses?approval_status=draft'),
                'tone' => 'warning',
            ];
        }

        if ($summary['open_closings'] > 0) {
            $actions[] = [
                'label' => 'Closing perlu ditinjau',
                'description' => $summary['open_closings'] . ' closing masih draft atau closed.',
                'url' => url('/closing'),
                'tone' => 'secondary',
            ];
        }

        return $actions;
    }

    public function cashAdvanceAging(int $limit = 5): array
    {
        $statement = $this->db()->prepare(
            'SELECT ca.id, ca.advance_date, ca.description, ca.remaining_amount,
                    DATEDIFF(CURDATE(), ca.advance_date) AS age_days,
                    m.name AS member_name, m.nickname
             FROM trx_cash_advances ca
             JOIN mst_members m ON m.id = ca.member_id
             WHERE ca.status = 0 AND ca.remaining_amount > 0
             ORDER BY age_days DESC, ca.remaining_amount DESC
             LIMIT :limit'
        );
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function groupBalances(): array
    {
        $statement = $this->db()->query(
            "SELECT g.id, g.name, g.code,
                    COALESCE(i.total_income, 0) AS total_income,
                    COALESCE(e.total_expense, 0) AS total_expense,
                    COALESCE(ca.cash_advance_disbursed, 0) AS cash_advance_disbursed,
                    COALESCE(ca.outstanding_cash_advance, 0) AS outstanding_cash_advance,
                    COALESCE(pd.pending_distribution, 0) AS pending_distribution,
                    COALESCE(i.total_income, 0)
                        - COALESCE(e.total_expense, 0)
                        - COALESCE(ca.cash_advance_disbursed, 0) AS cash_balance
             FROM mst_groups g
             LEFT JOIN (
                SELECT group_id, SUM(amount) AS total_income
                FROM trx_incomes
                WHERE deleted_at IS NULL
                GROUP BY group_id
             ) i ON i.group_id = g.id
             LEFT JOIN (
                SELECT group_id, SUM(amount) AS total_expense
                FROM trx_expenses
                WHERE approval_status = 'approved'
                GROUP BY group_id
             ) e ON e.group_id = g.id
             LEFT JOIN (
                SELECT group_id,
                       SUM(amount) AS cash_advance_disbursed,
                       SUM(CASE WHEN status = 0 AND remaining_amount > 0 THEN remaining_amount ELSE 0 END) AS outstanding_cash_advance
                FROM trx_cash_advances
                WHERE group_id IS NOT NULL
                GROUP BY group_id
             ) ca ON ca.group_id = g.id
             LEFT JOIN (
                SELECT group_id, SUM(paid_amount) AS pending_distribution
                FROM trx_profit_distributions
                WHERE status = 'pending'
                GROUP BY group_id
             ) pd ON pd.group_id = g.id
             WHERE g.is_active = 1
             ORDER BY g.name ASC"
        );

        return $statement->fetchAll();
    }

    public function recentActivity(array $filters = [], int $limit = 6): array
    {
        $incomeWhere = $this->combineWhere([
            'deleted_at IS NULL',
            $this->dateWhere('transaction_date', $filters),
        ]);
        $expenseWhere = $this->dateWhere('expense_date', $filters);
        $cashAdvanceWhere = $this->dateWhere('advance_date', $filters);

        $statement = $this->db()->prepare(
            "(SELECT transaction_date AS activity_date, 'Income' AS type, COALESCE(client_name, username, reference_no, '-') AS title, amount
              FROM trx_incomes {$incomeWhere})
             UNION ALL
             (SELECT expense_date AS activity_date, 'Expense' AS type, description AS title, amount
              FROM trx_expenses {$expenseWhere})
             UNION ALL
             (SELECT advance_date AS activity_date, 'Kasbon' AS type, COALESCE(description, 'Kasbon') AS title, amount
              FROM trx_cash_advances {$cashAdvanceWhere})
             ORDER BY activity_date DESC
             LIMIT :limit"
        );
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    private function dateWhere(string $column, array $filters): string
    {
        $where = [];

        if (($filters['date_from'] ?? '') !== '') {
            $where[] = "{$column} >= " . $this->db()->quote($filters['date_from']);
        }

        if (($filters['date_to'] ?? '') !== '') {
            $where[] = "{$column} <= " . $this->db()->quote($filters['date_to']);
        }

        return $where === [] ? '' : 'WHERE ' . implode(' AND ', $where);
    }

    private function combineWhere(array $parts): string
    {
        $where = [];

        foreach ($parts as $part) {
            $part = trim((string) $part);

            if ($part === '') {
                continue;
            }

            $where[] = preg_replace('/^WHERE\s+/i', '', $part);
        }

        return $where === [] ? '' : 'WHERE ' . implode(' AND ', $where);
    }

    private function sum(string $table, string $column, string $where = ''): float
    {
        $sql = "SELECT COALESCE(SUM({$column}), 0) FROM {$table}";

        if ($where !== '') {
            $sql .= " WHERE " . preg_replace('/^WHERE\s+/i', '', $where);
        }

        return (float) $this->db()->query($sql)->fetchColumn();
    }

    private function countRows(string $table, string $where = ''): int
    {
        $sql = "SELECT COUNT(*) FROM {$table}";

        if ($where !== '') {
            $sql .= " WHERE {$where}";
        }

        return (int) $this->db()->query($sql)->fetchColumn();
    }
}
