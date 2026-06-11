<?php

namespace App\Models;

use App\Core\Model;
use App\Models\Expense;

class CashAdvance extends Model
{
    public function outstanding(): array
    {
        $statement = $this->db()->query(
            'SELECT ca.id, ca.group_id, ca.member_id, ca.advance_date, ca.description,
                    ca.amount, ca.remaining_amount, ca.status, ca.transfer_method_id,
                    ca.transfer_fee_amount, ca.transfer_fee_expense_id,
                    tm.name AS transfer_method_name,
                    g.name AS group_name, m.name AS member_name, m.nickname
             FROM trx_cash_advances ca
             LEFT JOIN mst_groups g ON g.id = ca.group_id
             JOIN mst_members m ON m.id = ca.member_id
             LEFT JOIN mst_transfer_methods tm ON tm.id = ca.transfer_method_id
             WHERE ca.status = 0 AND ca.remaining_amount > 0
             ORDER BY ca.advance_date DESC, ca.id DESC'
        );

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT ca.id, ca.group_id, ca.member_id, ca.advance_date, ca.description,
                    ca.amount, ca.remaining_amount, ca.status, ca.transfer_method_id,
                    ca.transfer_fee_amount, ca.transfer_fee_expense_id,
                    ca.created_at, ca.updated_at,
                    tm.name AS transfer_method_name,
                    g.name AS group_name, m.name AS member_name, m.nickname
             FROM trx_cash_advances ca
             LEFT JOIN mst_groups g ON g.id = ca.group_id
             JOIN mst_members m ON m.id = ca.member_id
             LEFT JOIN mst_transfer_methods tm ON tm.id = ca.transfer_method_id
             WHERE ca.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        $cashAdvance = $statement->fetch();

        return $cashAdvance ?: null;
    }

    public function create(array $data): int
    {
        $statement = $this->db()->prepare(
            'INSERT INTO trx_cash_advances
                (group_id, member_id, advance_date, amount, remaining_amount, description, status,
                 transfer_method_id, transfer_fee_amount)
             VALUES
                (:group_id, :member_id, :advance_date, :amount, :remaining_amount, :description, :status,
                 :transfer_method_id, :transfer_fee_amount)'
        );
        $statement->execute([
            'group_id' => $data['group_id'] ?: null,
            'member_id' => $data['member_id'],
            'advance_date' => $data['advance_date'],
            'amount' => $data['amount'],
            'remaining_amount' => $data['amount'],
            'description' => $data['description'],
            'status' => $data['status'],
            'transfer_method_id' => $data['transfer_method_id'] ?: null,
            'transfer_fee_amount' => $data['transfer_fee_amount'] ?? 0,
        ]);

        $id = (int) $this->db()->lastInsertId();

        if ((float) ($data['transfer_fee_amount'] ?? 0) > 0) {
            $feeExpenseId = (new Expense())->createTransferFee([
                'group_id' => $data['group_id'],
                'expense_date' => $data['advance_date'],
                'amount' => $data['transfer_fee_amount'],
                'description' => 'Biaya transfer untuk kasbon #' . $id,
                'created_by' => 'system',
                'transfer_method_id' => $data['transfer_method_id'],
            ]);
            $this->db()->prepare('UPDATE trx_cash_advances SET transfer_fee_expense_id = :fee_id WHERE id = :id')->execute([
                'fee_id' => $feeExpenseId,
                'id' => $id,
            ]);
        }

        return $id;
    }

    public function payments(int $cashAdvanceId): array
    {
        $statement = $this->db()->prepare(
            'SELECT id, payment_date, reference_no, amount, payment_method, notes, created_at
             FROM trx_cash_advance_payments
             WHERE cash_advance_id = :cash_advance_id
             ORDER BY payment_date ASC, id ASC'
        );
        $statement->execute(['cash_advance_id' => $cashAdvanceId]);

        return $statement->fetchAll();
    }

    public function totalPayments(int $cashAdvanceId): float
    {
        $statement = $this->db()->prepare(
            'SELECT COALESCE(SUM(amount), 0)
             FROM trx_cash_advance_payments
             WHERE cash_advance_id = :cash_advance_id'
        );
        $statement->execute(['cash_advance_id' => $cashAdvanceId]);

        return (float) $statement->fetchColumn();
    }
}
