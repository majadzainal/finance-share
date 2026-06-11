<?php

namespace App\Models;

use App\Core\Model;

class Member extends Model
{
    public function all(string $search = ''): array
    {
        $sql = 'SELECT id, code, name AS full_name, nickname, phone, is_active, created_at, updated_at
                FROM mst_members';
        $params = [];

        if ($search !== '') {
            $sql .= ' WHERE name LIKE :search_name OR nickname LIKE :search_nickname';
            $params['search_name'] = '%' . $search . '%';
            $params['search_nickname'] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY name ASC';

        $statement = $this->db()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT id, code, name AS full_name, nickname, phone, is_active, created_at, updated_at
             FROM mst_members
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        $member = $statement->fetch();

        return $member ?: null;
    }

    public function create(array $data): int
    {
        $statement = $this->db()->prepare(
            'INSERT INTO mst_members (code, name, nickname, phone, is_active)
             VALUES (:code, :full_name, :nickname, :phone, :is_active)'
        );
        $statement->execute([
            'code' => $this->nextCode(),
            'full_name' => $data['full_name'],
            'nickname' => $data['nickname'],
            'phone' => $data['phone'],
            'is_active' => $data['status'],
        ]);

        return (int) $this->db()->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $statement = $this->db()->prepare(
            'UPDATE mst_members
             SET name = :full_name,
                 nickname = :nickname,
                 phone = :phone,
                 is_active = :is_active
             WHERE id = :id'
        );

        return $statement->execute([
            'id' => $id,
            'full_name' => $data['full_name'],
            'nickname' => $data['nickname'],
            'phone' => $data['phone'],
            'is_active' => $data['status'],
        ]);
    }

    public function setStatus(int $id, int $status): bool
    {
        $statement = $this->db()->prepare(
            'UPDATE mst_members
             SET is_active = :is_active
             WHERE id = :id'
        );

        return $statement->execute([
            'id' => $id,
            'is_active' => $status,
        ]);
    }

    private function nextCode(): string
    {
        $statement = $this->db()->query("SELECT MAX(CAST(SUBSTRING(code, 5) AS UNSIGNED)) FROM mst_members WHERE code LIKE 'MBR-%'");
        $nextNumber = ((int) $statement->fetchColumn()) + 1;

        return 'MBR-' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
