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
            "SELECT l.id, l.transaction_date AS trx_date, l.source_table AS reference_type,
                    l.source_id AS reference_id, l.description, l.debit, l.credit,
                    l.account_code, l.account_name, g.name AS group_name, m.name AS member_name
             FROM trx_ledger l
             JOIN mst_groups g ON g.id = l.group_id
             LEFT JOIN mst_members m ON m.id = l.member_id
             {$where}
             ORDER BY l.transaction_date ASC, l.id ASC"
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
        $where = ['l.transaction_date < :date_from'];

        if (($filters['group_id'] ?? 0) > 0) {
            $where[] = 'l.group_id = :group_id';
            $params['group_id'] = (int) $filters['group_id'];
        }

        if (($filters['member_id'] ?? 0) > 0) {
            $where[] = 'l.member_id = :member_id';
            $params['member_id'] = (int) $filters['member_id'];
        }

        $statement = $this->db()->prepare('SELECT COALESCE(SUM(l.debit - l.credit), 0) FROM trx_ledger l WHERE ' . implode(' AND ', $where));
        $statement->execute($params);

        return (float) $statement->fetchColumn();
    }

    private function buildWhere(array $filters, array &$params): string
    {
        $where = [];

        if (($filters['group_id'] ?? 0) > 0) {
            $where[] = 'l.group_id = :group_id';
            $params['group_id'] = (int) $filters['group_id'];
        }

        if (($filters['member_id'] ?? 0) > 0) {
            $where[] = 'l.member_id = :member_id';
            $params['member_id'] = (int) $filters['member_id'];
        }

        if (($filters['date_from'] ?? '') !== '') {
            $where[] = 'l.transaction_date >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }

        if (($filters['date_to'] ?? '') !== '') {
            $where[] = 'l.transaction_date <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        return $where === [] ? '' : 'WHERE ' . implode(' AND ', $where);
    }
}
