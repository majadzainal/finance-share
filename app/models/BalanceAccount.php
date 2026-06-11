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
        $statement = $this->db()->query('SELECT COALESCE(SUM(debit - credit), 0) FROM trx_ledger');

        return (float) $statement->fetchColumn();
    }
}
