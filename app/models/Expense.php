<?php

namespace App\Models;

use App\Core\Model;

class Expense extends Model
{
    public function paginate(array $filters, int $page, int|string $perPage): array
    {
        $params = [];
        $where = $this->buildWhere($filters, $params);
        $limit = '';

        if ($perPage !== 'all') {
            $offset = ($page - 1) * $perPage;
            $limit = "LIMIT {$perPage} OFFSET {$offset}";
        }
        $orderBy = $this->orderBy($filters);

        $statement = $this->db()->prepare(
            "SELECT e.id, e.group_id, e.category_id, e.closing_id, e.expense_date,
                    e.description, e.amount, e.created_by, e.transfer_method_id,
                    e.transfer_fee_amount, e.transfer_fee_expense_id,
                    tm.name AS transfer_method_name,
                    g.name AS group_name, c.name AS category_name
             FROM trx_expenses e
             JOIN mst_groups g ON g.id = e.group_id
             JOIN mst_expense_categories c ON c.id = e.category_id
             LEFT JOIN mst_transfer_methods tm ON tm.id = e.transfer_method_id
             {$where}
             ORDER BY {$orderBy}, e.id DESC
             {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function count(array $filters): int
    {
        $params = [];
        $where = $this->buildWhere($filters, $params);
        $statement = $this->db()->prepare("SELECT COUNT(*) FROM trx_expenses e {$where}");
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    public function totalAmount(array $filters): float
    {
        $params = [];
        $where = $this->buildWhere($filters, $params);
        $statement = $this->db()->prepare("SELECT COALESCE(SUM(e.amount), 0) FROM trx_expenses e {$where}");
        $statement->execute($params);

        return (float) $statement->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT e.id, e.group_id, e.category_id, e.closing_id, e.expense_date,
                    e.description, e.amount, e.created_by, e.transfer_method_id,
                    e.transfer_fee_amount, e.transfer_fee_expense_id,
                    tm.name AS transfer_method_name,
                    g.name AS group_name, c.name AS category_name
             FROM trx_expenses e
             JOIN mst_groups g ON g.id = e.group_id
             JOIN mst_expense_categories c ON c.id = e.category_id
             LEFT JOIN mst_transfer_methods tm ON tm.id = e.transfer_method_id
             WHERE e.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        $expense = $statement->fetch();

        return $expense ?: null;
    }

    public function create(array $data): int
    {
        $statement = $this->db()->prepare(
            'INSERT INTO trx_expenses
                (group_id, category_id, expense_date, amount, description, created_by,
                 transfer_method_id, transfer_fee_amount)
             VALUES
                (:group_id, :category_id, :expense_date, :amount, :description, :created_by,
                 :transfer_method_id, :transfer_fee_amount)'
        );
        $statement->execute([
            'group_id' => $data['group_id'],
            'category_id' => $data['category_id'],
            'expense_date' => $data['expense_date'],
            'amount' => $data['amount'],
            'description' => $data['description'],
            'created_by' => $data['created_by'],
            'transfer_method_id' => $data['transfer_method_id'] ?: null,
            'transfer_fee_amount' => $data['transfer_fee_amount'] ?? 0,
        ]);

        $id = (int) $this->db()->lastInsertId();
        $this->syncTransferFeeExpense($id, $data);

        return $id;
    }

    public function update(int $id, array $data): bool
    {
        $statement = $this->db()->prepare(
            'UPDATE trx_expenses
             SET group_id = :group_id,
                 category_id = :category_id,
                 expense_date = :expense_date,
                 amount = :amount,
                 description = :description,
                 created_by = :created_by,
                 transfer_method_id = :transfer_method_id,
                 transfer_fee_amount = :transfer_fee_amount
             WHERE id = :id AND closing_id IS NULL'
        );

        $updated = $statement->execute([
            'id' => $id,
            'group_id' => $data['group_id'],
            'category_id' => $data['category_id'],
            'expense_date' => $data['expense_date'],
            'amount' => $data['amount'],
            'description' => $data['description'],
            'created_by' => $data['created_by'],
            'transfer_method_id' => $data['transfer_method_id'] ?: null,
            'transfer_fee_amount' => $data['transfer_fee_amount'] ?? 0,
        ]);

        if ($updated) {
            $this->syncTransferFeeExpense($id, $data);
        }

        return $updated;
    }

    public function delete(int $id): bool
    {
        $expense = $this->find($id);

        if ($expense && $expense['transfer_fee_expense_id'] !== null) {
            $this->db()->prepare('DELETE FROM trx_expenses WHERE id = :id AND closing_id IS NULL')->execute([
                'id' => (int) $expense['transfer_fee_expense_id'],
            ]);
        }

        $statement = $this->db()->prepare('DELETE FROM trx_expenses WHERE id = :id AND closing_id IS NULL');

        return $statement->execute(['id' => $id]);
    }

    private function buildWhere(array $filters, array &$params): string
    {
        $where = [];

        if (($filters['group_id'] ?? 0) > 0) {
            $where[] = 'e.group_id = :group_id';
            $params['group_id'] = (int) $filters['group_id'];
        }

        if (($filters['date_from'] ?? '') !== '') {
            $where[] = 'e.expense_date >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }

        if (($filters['date_to'] ?? '') !== '') {
            $where[] = 'e.expense_date <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        return $where === [] ? '' : 'WHERE ' . implode(' AND ', $where);
    }

    private function orderBy(array $filters): string
    {
        $columns = [
            'expense_date' => 'e.expense_date',
            'group_name' => 'g.name',
            'category_name' => 'c.name',
            'description' => 'e.description',
            'created_by' => 'e.created_by',
            'transfer_method' => 'tm.name',
            'amount' => 'e.amount',
            'status' => 'e.closing_id',
        ];
        $sortBy = $filters['sort_by'] ?? 'expense_date';
        $sortDir = strtolower($filters['sort_dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
        $column = $columns[$sortBy] ?? $columns['expense_date'];

        return $column . ' ' . $sortDir;
    }

    public function createTransferFee(array $data): int
    {
        $categoryId = (new ExpenseCategory())->findIdByCode('TRANSFER_FEE');

        if ($categoryId === null) {
            throw new \RuntimeException('Kategori Biaya Transfer belum tersedia.');
        }

        $statement = $this->db()->prepare(
            'INSERT INTO trx_expenses
                (group_id, category_id, expense_date, amount, description, created_by,
                 transfer_method_id, transfer_fee_amount)
             VALUES
                (:group_id, :category_id, :expense_date, :amount, :description, :created_by,
                 :transfer_method_id, 0)'
        );
        $statement->execute([
            'group_id' => $data['group_id'],
            'category_id' => $categoryId,
            'expense_date' => $data['expense_date'],
            'amount' => $data['amount'],
            'description' => $data['description'],
            'created_by' => $data['created_by'] ?: 'system',
            'transfer_method_id' => $data['transfer_method_id'] ?: null,
        ]);

        return (int) $this->db()->lastInsertId();
    }

    private function syncTransferFeeExpense(int $expenseId, array $data): void
    {
        $feeAmount = (float) ($data['transfer_fee_amount'] ?? 0);
        $expense = $this->find($expenseId);

        if (! $expense) {
            return;
        }

        $feeExpenseId = $expense['transfer_fee_expense_id'] ? (int) $expense['transfer_fee_expense_id'] : null;

        if ($feeAmount <= 0) {
            if ($feeExpenseId !== null) {
                $this->db()->prepare('DELETE FROM trx_expenses WHERE id = :id AND closing_id IS NULL')->execute(['id' => $feeExpenseId]);
                $this->db()->prepare('UPDATE trx_expenses SET transfer_fee_expense_id = NULL WHERE id = :id')->execute(['id' => $expenseId]);
            }

            return;
        }

        $feeData = [
            'group_id' => $data['group_id'],
            'expense_date' => $data['expense_date'],
            'amount' => $feeAmount,
            'description' => 'Biaya transfer untuk expense #' . $expenseId,
            'created_by' => $data['created_by'] ?? 'system',
            'transfer_method_id' => $data['transfer_method_id'] ?? 0,
        ];

        if ($feeExpenseId === null) {
            $feeExpenseId = $this->createTransferFee($feeData);
            $this->db()->prepare('UPDATE trx_expenses SET transfer_fee_expense_id = :fee_id WHERE id = :id')->execute([
                'fee_id' => $feeExpenseId,
                'id' => $expenseId,
            ]);
            return;
        }

        $categoryId = (new ExpenseCategory())->findIdByCode('TRANSFER_FEE');
        $statement = $this->db()->prepare(
            'UPDATE trx_expenses
             SET group_id = :group_id,
                 category_id = :category_id,
                 expense_date = :expense_date,
                 amount = :amount,
                 description = :description,
                 created_by = :created_by,
                 transfer_method_id = :transfer_method_id
             WHERE id = :id AND closing_id IS NULL'
        );
        $statement->execute([
            'id' => $feeExpenseId,
            'group_id' => $feeData['group_id'],
            'category_id' => $categoryId,
            'expense_date' => $feeData['expense_date'],
            'amount' => $feeData['amount'],
            'description' => $feeData['description'],
            'created_by' => $feeData['created_by'],
            'transfer_method_id' => $feeData['transfer_method_id'] ?: null,
        ]);
    }
}
