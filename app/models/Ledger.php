<?php

namespace App\Models;

use App\Core\Model;

class Ledger extends Model
{
    public function report(array $filters): array
    {
        $params = [];
        $where = $this->buildWhere($filters, $params);
        $statement = $this->db()->prepare(
            "SELECT ledger_rows.id, ledger_rows.trx_date, ledger_rows.reference_type,
                    ledger_rows.reference_id, ledger_rows.description, ledger_rows.debit,
                    ledger_rows.credit, ledger_rows.account_code, ledger_rows.account_name,
                    g.name AS group_name, m.name AS member_name
             FROM (
                SELECT i.id, i.group_id, NULL AS member_id, i.transaction_date AS trx_date,
                       'trx_incomes' AS reference_type, i.id AS reference_id,
                       COALESCE(i.client_name, i.username, i.reference_no, i.description, 'Income') AS description,
                       i.amount AS debit, 0 AS credit, '4000' AS account_code, 'Income' AS account_name
                FROM trx_incomes i
                WHERE i.deleted_at IS NULL
                UNION ALL
                SELECT e.id, e.group_id, e.paid_by_member_id AS member_id, e.expense_date AS trx_date,
                       'trx_expenses' AS reference_type, e.id AS reference_id,
                       e.description, 0 AS debit, e.amount AS credit,
                       CASE WHEN e.created_by = 'profit_distribution' THEN '3000' ELSE '5000' END AS account_code,
                       CASE WHEN e.created_by = 'profit_distribution' THEN 'Profit Distribution' ELSE 'Expense' END AS account_name
                FROM trx_expenses e
                WHERE e.approval_status = 'approved'
                UNION ALL
                SELECT ca.id, ca.group_id, ca.member_id, ca.advance_date AS trx_date,
                       'trx_cash_advances' AS reference_type, ca.id AS reference_id,
                       COALESCE(ca.description, 'Kasbon') AS description,
                       0 AS debit, ca.amount AS credit, '1200' AS account_code, 'Kasbon Disalurkan' AS account_name
                FROM trx_cash_advances ca
                WHERE ca.group_id IS NOT NULL
             ) ledger_rows
             JOIN mst_groups g ON g.id = ledger_rows.group_id
             LEFT JOIN mst_members m ON m.id = ledger_rows.member_id
             {$where}
             ORDER BY ledger_rows.trx_date ASC, ledger_rows.id ASC"
        );
        $statement->execute($params);
        $rows = $statement->fetchAll();
        $runningBalance = $this->openingBalance($filters);

        foreach ($rows as &$row) {
            $runningBalance += (float) $row['debit'] - (float) $row['credit'];
            $row['running_balance'] = $runningBalance;
        }

        return $rows;
    }

    private function openingBalance(array $filters): float
    {
        if (($filters['date_from'] ?? '') === '') {
            return 0.0;
        }

        $params = ['date_from' => $filters['date_from']];
        $where = ['ledger_rows.trx_date < :date_from'];

        if (($filters['group_id'] ?? 0) > 0) {
            $where[] = 'ledger_rows.group_id = :group_id';
            $params['group_id'] = (int) $filters['group_id'];
        }

        if (($filters['member_id'] ?? 0) > 0) {
            $where[] = 'ledger_rows.member_id = :member_id';
            $params['member_id'] = (int) $filters['member_id'];
        }

        $statement = $this->db()->prepare(
            "SELECT COALESCE(SUM(ledger_rows.debit - ledger_rows.credit), 0)
             FROM (
                SELECT group_id, NULL AS member_id, transaction_date AS trx_date, amount AS debit, 0 AS credit
                FROM trx_incomes
                WHERE deleted_at IS NULL
                UNION ALL
                SELECT group_id, paid_by_member_id AS member_id, expense_date AS trx_date, 0 AS debit, amount AS credit
                FROM trx_expenses
                WHERE approval_status = 'approved'
                UNION ALL
                SELECT group_id, member_id, advance_date AS trx_date, 0 AS debit, amount AS credit
                FROM trx_cash_advances
                WHERE group_id IS NOT NULL
             ) ledger_rows
             WHERE " . implode(' AND ', $where)
        );
        $statement->execute($params);

        return (float) $statement->fetchColumn();
    }

    private function buildWhere(array $filters, array &$params): string
    {
        $where = [];

        if (($filters['group_id'] ?? 0) > 0) {
            $where[] = 'ledger_rows.group_id = :group_id';
            $params['group_id'] = (int) $filters['group_id'];
        }

        if (($filters['member_id'] ?? 0) > 0) {
            $where[] = 'ledger_rows.member_id = :member_id';
            $params['member_id'] = (int) $filters['member_id'];
        }

        if (($filters['date_from'] ?? '') !== '') {
            $where[] = 'ledger_rows.trx_date >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }

        if (($filters['date_to'] ?? '') !== '') {
            $where[] = 'ledger_rows.trx_date <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        return $where === [] ? '' : 'WHERE ' . implode(' AND ', $where);
    }
}
