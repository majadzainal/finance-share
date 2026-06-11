<?php

namespace App\Models;

use App\Core\Model;

class Role extends Model
{
    public function all(): array
    {
        $statement = $this->db()->query(
            'SELECT id, code, name, description, is_active, created_at, updated_at
             FROM mst_roles
             ORDER BY name ASC'
        );

        return $statement->fetchAll();
    }

    public function active(): array
    {
        $statement = $this->db()->query(
            'SELECT id, code, name
             FROM mst_roles
             WHERE is_active = 1
             ORDER BY name ASC'
        );

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT id, code, name, description, is_active
             FROM mst_roles
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $role = $statement->fetch();

        return $role ?: null;
    }

    public function codeExists(string $code, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM mst_roles WHERE code = :code';
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
            'INSERT INTO mst_roles (code, name, description, is_active)
             VALUES (:code, :name, :description, :is_active)'
        );
        $statement->execute([
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'],
            'is_active' => $data['status'],
        ]);

        return (int) $this->db()->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $statement = $this->db()->prepare(
            'UPDATE mst_roles
             SET code = :code,
                 name = :name,
                 description = :description,
                 is_active = :is_active
             WHERE id = :id'
        );

        return $statement->execute([
            'id' => $id,
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'],
            'is_active' => $data['status'],
        ]);
    }

    public function setStatus(int $id, int $status): bool
    {
        $statement = $this->db()->prepare('UPDATE mst_roles SET is_active = :status WHERE id = :id');

        return $statement->execute(['id' => $id, 'status' => $status]);
    }
}
