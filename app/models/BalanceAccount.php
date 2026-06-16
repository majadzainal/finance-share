<?php

namespace App\Models;

use App\Core\Model;

class BalanceAccount extends Model
{
    public function all(): array
    {
        $statement = $this->db()->query(
            'SELECT id, code, name, current_balance, notes, updated_at
             FROM mst_balance_accounts
             WHERE is_active = 1
             ORDER BY code ASC'
        );

        return $statement->fetchAll();
    }

    public function updateBalances(array $rows): void
    {
        $statement = $this->db()->prepare(
            'UPDATE mst_balance_accounts
             SET current_balance = :current_balance,
                 notes = :notes
             WHERE id = :id AND is_active = 1'
        );

        foreach ($rows as $row) {
            $statement->execute([
                'id' => $row['id'],
                'current_balance' => $row['current_balance'],
                'notes' => $row['notes'],
            ]);
        }
    }

    public function totalManualBalance(): float
    {
        $statement = $this->db()->query(
            'SELECT COALESCE(SUM(current_balance), 0)
             FROM mst_balance_accounts
             WHERE is_active = 1'
        );

        return (float) $statement->fetchColumn();
    }

    public function expectedBalance(): float
    {
        $summary = $this->balanceSummary();

        return $summary['expected_balance'];
    }

    public function balanceSummary(): array
    {
        $income = $this->sumColumn('trx_incomes', 'amount', 'deleted_at IS NULL');
        $expense = $this->sumColumn('trx_expenses', 'amount', "approval_status = 'approved'");
        $cashAdvanceDisbursed = $this->sumColumn('trx_cash_advances', 'amount');
        $cashAdvanceOutstanding = $this->sumColumn('trx_cash_advances', 'remaining_amount', 'status = 0 AND remaining_amount > 0');

        return [
            'total_income' => $income,
            'total_expense' => $expense,
            'total_cash_advance' => $cashAdvanceOutstanding,
            'total_cash_advance_disbursed' => $cashAdvanceDisbursed,
            'expected_balance' => $income - $expense - $cashAdvanceDisbursed,
        ];
    }

    private function sumColumn(string $table, string $column, string $where = ''): float
    {
        $sql = "SELECT COALESCE(SUM({$column}), 0) FROM {$table}";

        if ($where !== '') {
            $sql .= " WHERE {$where}";
        }

        $statement = $this->db()->query($sql);

        return (float) $statement->fetchColumn();
    }
}
