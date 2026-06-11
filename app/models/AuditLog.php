<?php

namespace App\Models;

use App\Core\Model;

class AuditLog extends Model
{
    public function create(array $data): int
    {
        $statement = $this->db()->prepare(
            'INSERT INTO trx_audit_logs
                (user_id, user_name, user_role, event, module, method, path, ip_address, user_agent, request_data)
             VALUES
                (:user_id, :user_name, :user_role, :event, :module, :method, :path, :ip_address, :user_agent, :request_data)'
        );
        $statement->execute([
            'user_id' => $data['user_id'] ?? null,
            'user_name' => $data['user_name'] ?? null,
            'user_role' => $data['user_role'] ?? null,
            'event' => $data['event'],
            'module' => $data['module'],
            'method' => $data['method'],
            'path' => $data['path'],
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'request_data' => $data['request_data'] ?? null,
        ]);

        return (int) $this->db()->lastInsertId();
    }

    public function paginate(array $filters, int $page, int $perPage): array
    {
        $params = [];
        $where = $this->buildWhere($filters, $params);
        $offset = ($page - 1) * $perPage;
        $statement = $this->db()->prepare(
            "SELECT id, user_name, user_role, event, module, method, path, ip_address, request_data, created_at
             FROM trx_audit_logs
             {$where}
             ORDER BY created_at DESC, id DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function count(array $filters): int
    {
        $params = [];
        $where = $this->buildWhere($filters, $params);
        $statement = $this->db()->prepare("SELECT COUNT(*) FROM trx_audit_logs {$where}");
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    private function buildWhere(array $filters, array &$params): string
    {
        $where = [];

        if (($filters['module'] ?? '') !== '') {
            $where[] = 'module LIKE :module';
            $params['module'] = '%' . $filters['module'] . '%';
        }

        if (($filters['event'] ?? '') !== '') {
            $where[] = 'event LIKE :event';
            $params['event'] = '%' . $filters['event'] . '%';
        }

        if (($filters['date_from'] ?? '') !== '') {
            $where[] = 'DATE(created_at) >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }

        if (($filters['date_to'] ?? '') !== '') {
            $where[] = 'DATE(created_at) <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        return $where === [] ? '' : 'WHERE ' . implode(' AND ', $where);
    }
}
