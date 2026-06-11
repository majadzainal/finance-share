<?php

namespace App\Models;

use App\Core\Model;

class Income extends Model
{
    public function paginate(array $filters, int $page, int|string $perPage): array
    {
        $params = [];
        $where = $this->buildWhere($filters, $params);
        $limit = '';

        if ($perPage !== 'all') {
            $offset = ($page - 1) * $perPage;
            $limit = "LIMIT {$perPage} OFFSET {$offset}";
        }
        $orderBy = $this->orderBy($filters);

        $statement = $this->db()->prepare(
            "SELECT i.id, i.group_id, i.closing_id, i.transaction_date, i.reference_no,
                    i.payment_method, i.bank_target, i.income_type, i.client_name,
                    i.address, i.username, i.profile_package, i.amount, i.description,
                    g.name AS group_name
             FROM trx_incomes i
             JOIN mst_groups g ON g.id = i.group_id
             {$where}
             ORDER BY {$orderBy}, i.id DESC
             {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function count(array $filters): int
    {
        $params = [];
        $where = $this->buildWhere($filters, $params);
        $statement = $this->db()->prepare("SELECT COUNT(*) FROM trx_incomes i {$where}");
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    public function totalAmount(array $filters): float
    {
        $params = [];
        $where = $this->buildWhere($filters, $params);
        $statement = $this->db()->prepare("SELECT COALESCE(SUM(i.amount), 0) FROM trx_incomes i {$where}");
        $statement->execute($params);

        return (float) $statement->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT i.id, i.group_id, i.closing_id, i.transaction_date, i.reference_no,
                    i.payment_method, i.bank_target, i.income_type, i.client_name,
                    i.address, i.username, i.profile_package, i.amount, i.description,
                    g.name AS group_name
             FROM trx_incomes i
             JOIN mst_groups g ON g.id = i.group_id
             WHERE i.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        $income = $statement->fetch();

        return $income ?: null;
    }

    public function update(int $id, array $data): bool
    {
        $statement = $this->db()->prepare(
            'UPDATE trx_incomes
             SET transaction_date = :transaction_date,
                 reference_no = :reference_no,
                 payment_method = :payment_method,
                 bank_target = :bank_target,
                 income_type = :income_type,
                 client_name = :client_name,
                 address = :address,
                 username = :username,
                 profile_package = :profile_package,
                 amount = :amount,
                 description = :description
             WHERE id = :id AND closing_id IS NULL'
        );

        return $statement->execute([
            'id' => $id,
            'transaction_date' => $data['transaction_date'],
            'reference_no' => $data['reference_no'],
            'payment_method' => $data['payment_method'],
            'bank_target' => $data['bank_target'],
            'income_type' => $data['income_type'],
            'client_name' => $data['client_name'],
            'address' => $data['address'],
            'username' => $data['username'],
            'profile_package' => $data['profile_package'],
            'amount' => $data['amount'],
            'description' => $data['description'],
        ]);
    }

    public function delete(int $id): bool
    {
        $statement = $this->db()->prepare('DELETE FROM trx_incomes WHERE id = :id AND closing_id IS NULL');

        return $statement->execute(['id' => $id]);
    }

    private function buildWhere(array $filters, array &$params): string
    {
        $where = [];

        if (($filters['group_id'] ?? 0) > 0) {
            $where[] = 'i.group_id = :group_id';
            $params['group_id'] = (int) $filters['group_id'];
        }

        if (($filters['date_from'] ?? '') !== '') {
            $where[] = 'i.transaction_date >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }

        if (($filters['date_to'] ?? '') !== '') {
            $where[] = 'i.transaction_date <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        if (($filters['search'] ?? '') !== '') {
            $where[] = '(i.username LIKE :search_username OR i.client_name LIKE :search_client)';
            $params['search_username'] = '%' . $filters['search'] . '%';
            $params['search_client'] = '%' . $filters['search'] . '%';
        }

        return $where === [] ? '' : 'WHERE ' . implode(' AND ', $where);
    }

    private function orderBy(array $filters): string
    {
        $columns = [
            'transaction_date' => 'i.transaction_date',
            'group_name' => 'g.name',
            'reference_no' => 'i.reference_no',
            'client_name' => 'i.client_name',
            'username' => 'i.username',
            'payment_method' => 'i.payment_method',
            'amount' => 'i.amount',
            'status' => 'i.closing_id',
        ];
        $sortBy = $filters['sort_by'] ?? 'transaction_date';
        $sortDir = strtolower($filters['sort_dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
        $column = $columns[$sortBy] ?? $columns['transaction_date'];

        return $column . ' ' . $sortDir;
    }
}
