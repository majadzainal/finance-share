<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    public function all(): array
    {
        $statement = $this->db()->query(
            'SELECT u.id, u.name, u.username, u.role, u.status, u.created_at, u.updated_at,
                    r.name AS role_name
             FROM users u
             LEFT JOIN mst_roles r ON r.code = u.role
             ORDER BY u.name ASC'
        );

        return $statement->fetchAll();
    }

    public function findActiveByUsername(string $username): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT id, name, username, password, role, status
             FROM users
             WHERE username = :username
               AND status = 1
             LIMIT 1'
        );
        $statement->execute(['username' => $username]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function find(int $id): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT id, name, username, role, status, created_at, updated_at
             FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function usernameExists(string $username, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE username = :username';
        $params = ['username' => $username];

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
            'INSERT INTO users (name, username, password, role, status)
             VALUES (:name, :username, :password, :role, :status)'
        );
        $statement->execute([
            'name' => $data['name'],
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'],
            'status' => $data['status'],
        ]);

        return (int) $this->db()->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $params = [
            'id' => $id,
            'name' => $data['name'],
            'username' => $data['username'],
            'role' => $data['role'],
            'status' => $data['status'],
        ];
        $passwordSql = '';

        if (($data['password'] ?? '') !== '') {
            $passwordSql = ', password = :password';
            $params['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $statement = $this->db()->prepare(
            "UPDATE users
             SET name = :name,
                 username = :username,
                 role = :role,
                 status = :status
                 {$passwordSql}
             WHERE id = :id"
        );

        return $statement->execute($params);
    }

    public function setStatus(int $id, int $status): bool
    {
        $statement = $this->db()->prepare('UPDATE users SET status = :status WHERE id = :id');

        return $statement->execute([
            'id' => $id,
            'status' => $status,
        ]);
    }
}
