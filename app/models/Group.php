<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Group extends Model
{
    public function all(): array
    {
        $statement = $this->db()->query(
            'SELECT id, code, name, is_active, created_at, updated_at
             FROM mst_groups
             ORDER BY name ASC'
        );

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT id, code, name, is_active, created_at, updated_at
             FROM mst_groups
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        $group = $statement->fetch();

        return $group ?: null;
    }

    public function codeExists(string $code, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM mst_groups WHERE code = :code';
        $params = ['code' => $code];

        if ($ignoreId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $ignoreId;
        }

        $statement = $this->db()->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn() > 0;
    }

    public function create(array $data): int
    {
        $statement = $this->db()->prepare(
            'INSERT INTO mst_groups (code, name, is_active)
             VALUES (:code, :name, :is_active)'
        );
        $statement->execute([
            'code' => $data['code'],
            'name' => $data['name'],
            'is_active' => $data['status'],
        ]);

        return (int) $this->db()->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $statement = $this->db()->prepare(
            'UPDATE mst_groups
             SET code = :code,
                 name = :name,
                 is_active = :is_active
             WHERE id = :id'
        );

        return $statement->execute([
            'id' => $id,
            'code' => $data['code'],
            'name' => $data['name'],
            'is_active' => $data['status'],
        ]);
    }

    public function setStatus(int $id, int $status): bool
    {
        $statement = $this->db()->prepare(
            'UPDATE mst_groups
             SET is_active = :is_active
             WHERE id = :id'
        );

        return $statement->execute([
            'id' => $id,
            'is_active' => $status,
        ]);
    }
}
