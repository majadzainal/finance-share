<?php

namespace App\Models;

use App\Core\Model;

class ExpenseCategory extends Model
{
    public function active(): array
    {
        $statement = $this->db()->query(
            'SELECT id, code, name
             FROM mst_expense_categories
             WHERE is_active = 1
             ORDER BY name ASC'
        );

        return $statement->fetchAll();
    }

    public function findIdByCode(string $code): ?int
    {
        $statement = $this->db()->prepare(
            'SELECT id
             FROM mst_expense_categories
             WHERE code = :code AND is_active = 1
             LIMIT 1'
        );
        $statement->execute(['code' => $code]);
        $id = $statement->fetchColumn();

        return $id === false ? null : (int) $id;
    }
}
