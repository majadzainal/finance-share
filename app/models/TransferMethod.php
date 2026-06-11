<?php

namespace App\Models;

use App\Core\Model;

class TransferMethod extends Model
{
    public function active(): array
    {
        $statement = $this->db()->query(
            'SELECT id, code, name, default_fee
             FROM mst_transfer_methods
             WHERE is_active = 1
             ORDER BY default_fee ASC, name ASC'
        );

        return $statement->fetchAll();
    }
}
