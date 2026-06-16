<?php

namespace App\Models;

use App\Core\Model;
use RuntimeException;

class ProfitDistribution extends Model
{
    public function closings(array $filters): array
    {
        $params = [];
        $where = [];

        if (($filters['group_id'] ?? 0) > 0) {
            $where[] = 'c.group_id = :group_id';
            $params['group_id'] = (int) $filters['group_id'];
        }

        if (($filters['period_start'] ?? '') !== '') {
            $where[] = 'c.period_start >= :period_start';
            $params['period_start'] = $filters['period_start'];
        }

        if (($filters['period_end'] ?? '') !== '') {
            $where[] = 'c.period_end <= :period_end';
            $params['period_end'] = $filters['period_end'];
        }

        $whereSql = $where === [] ? '' : 'WHERE ' . implode(' AND ', $where);
        $statement = $this->db()->prepare(
            "SELECT c.id, c.group_id, c.period_start, c.period_end, c.total_income,
                    c.total_expense, c.net_profit, c.status, c.closed_at, g.name AS group_name,
                    COUNT(d.id) AS distribution_count,
                    SUM(CASE WHEN d.status = 'paid' THEN 1 ELSE 0 END) AS paid_count
             FROM trx_closings c
             JOIN mst_groups g ON g.id = c.group_id
             LEFT JOIN trx_profit_distributions d ON d.closing_id = c.id
             {$whereSql}
             GROUP BY c.id, c.group_id, c.period_start, c.period_end, c.total_income,
                      c.total_expense, c.net_profit, c.status, c.closed_at, g.name
             ORDER BY c.period_end DESC, c.id DESC"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function closing(int $id): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT c.id, c.group_id, c.period_start, c.period_end, c.total_income,
                    c.total_expense, c.net_profit, c.status, c.closed_at, g.name AS group_name
             FROM trx_closings c
             JOIN mst_groups g ON g.id = c.group_id
             WHERE c.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $closing = $statement->fetch();

        return $closing ?: null;
    }

    public function distributions(int $closingId): array
    {
        $statement = $this->db()->prepare(
            'SELECT d.id, d.closing_id, d.member_id, d.share_percent,
                    d.profit_amount AS gross_share_amount,
                    GREATEST(d.profit_amount - d.paid_amount, 0) AS cash_advance_cut,
                    d.paid_amount AS final_take_home_out,
                    d.status AS payment_status,
                    d.paid_at,
                    m.name AS member_name,
                    m.nickname
             FROM trx_profit_distributions d
             JOIN mst_members m ON m.id = d.member_id
             WHERE d.closing_id = :closing_id
             ORDER BY m.name ASC'
        );
        $statement->execute(['closing_id' => $closingId]);

        return $statement->fetchAll();
    }

    public function markPaid(int $distributionId): ?int
    {
        $distribution = $this->findDistribution($distributionId);

        if (! $distribution) {
            return null;
        }

        $db = $this->db();
        $db->beginTransaction();

        try {
            $statement = $db->prepare(
                "UPDATE trx_profit_distributions
                 SET status = 'paid',
                     paid_at = NOW()
                 WHERE id = :id
                   AND status <> 'paid'"
            );
            $statement->execute(['id' => $distributionId]);

            if ($statement->rowCount() > 0) {
                $this->createPaidExpense($distribution);
            }

            $this->markClosingPaidIfComplete((int) $distribution['closing_id']);
            $db->commit();
        } catch (\Throwable $exception) {
            $db->rollBack();
            throw $exception;
        }


        return (int) $distribution['closing_id'];
    }

    private function findDistribution(int $id): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT d.id, d.closing_id, d.group_id, d.member_id, d.paid_amount, d.status,
                    c.period_start, c.period_end,
                    m.name AS member_name
             FROM trx_profit_distributions d
             JOIN trx_closings c ON c.id = d.closing_id
             JOIN mst_members m ON m.id = d.member_id
             WHERE d.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $distribution = $statement->fetch();

        return $distribution ?: null;
    }

    private function createPaidExpense(array $distribution): void
    {
        $amount = (float) $distribution['paid_amount'];

        if ($amount <= 0 || $this->paidExpenseExists((int) $distribution['id'])) {
            return;
        }

        $categoryId = $this->profitDistributionCategoryId();
        $statement = $this->db()->prepare(
            'INSERT INTO trx_expenses
                (group_id, category_id, closing_id, expense_date, amount, description,
                 created_by, approval_status, notes)
             VALUES
                (:group_id, :category_id, :closing_id, CURDATE(), :amount, :description,
                 :created_by, :approval_status, :notes)'
        );
        $statement->execute([
            'group_id' => (int) $distribution['group_id'],
            'category_id' => $categoryId,
            'closing_id' => (int) $distribution['closing_id'],
            'amount' => $amount,
            'description' => 'Profit distribution paid - ' . $distribution['member_name']
                . ' periode ' . $distribution['period_start'] . ' s/d ' . $distribution['period_end'],
            'created_by' => 'profit_distribution',
            'approval_status' => 'approved',
            'notes' => 'profit_distribution_id:' . (int) $distribution['id'],
        ]);
    }

    private function paidExpenseExists(int $distributionId): bool
    {
        $statement = $this->db()->prepare(
            "SELECT COUNT(*)
             FROM trx_expenses
             WHERE created_by = 'profit_distribution'
               AND notes = :notes"
        );
        $statement->execute(['notes' => 'profit_distribution_id:' . $distributionId]);

        return (int) $statement->fetchColumn() > 0;
    }

    private function profitDistributionCategoryId(): int
    {
        $category = new ExpenseCategory();
        $categoryId = $category->findIdByCode('PROFIT_DISTRIBUTION')
            ?? $category->findIdByCode('OTHER');

        if ($categoryId === null) {
            throw new RuntimeException('Kategori expense untuk profit distribution belum tersedia.');
        }

        return $categoryId;
    }

    private function markClosingPaidIfComplete(int $closingId): void
    {
        $statement = $this->db()->prepare(
            "SELECT COUNT(*) AS total_rows,
                    SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) AS paid_rows
             FROM trx_profit_distributions
             WHERE closing_id = :closing_id"
        );
        $statement->execute(['closing_id' => $closingId]);
        $summary = $statement->fetch();

        if ((int) $summary['total_rows'] > 0 && (int) $summary['total_rows'] === (int) $summary['paid_rows']) {
            $update = $this->db()->prepare("UPDATE trx_closings SET status = 'paid' WHERE id = :id");
            $update->execute(['id' => $closingId]);
        }
    }
}
