<?php

namespace App\Models;

use App\Core\Model;

class Income extends Model
{
    public function paginate(array $filters, int $page, int|string $perPage): array
    {
        $params = [];
        $amountThreshold = $this->amountReviewThreshold();
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
                    CASE WHEN i.amount > :amount_threshold THEN 1 ELSE 0 END AS amount_needs_review,
                    CASE WHEN TRIM(COALESCE(i.username, '')) = '' OR TRIM(COALESCE(i.client_name, '')) = '' THEN 1 ELSE 0 END AS identity_needs_review,
                    (
                        SELECT COUNT(*)
                        FROM trx_incomes d
                        WHERE d.group_id = i.group_id
                          AND d.deleted_at IS NULL
                          AND d.transaction_date = i.transaction_date
                          AND COALESCE(d.reference_no, '') = COALESCE(i.reference_no, '')
                          AND COALESCE(d.client_name, '') = COALESCE(i.client_name, '')
                    ) AS suspicious_duplicate_count,
                    g.name AS group_name
             FROM trx_incomes i
             JOIN mst_groups g ON g.id = i.group_id
             {$where}
             ORDER BY {$orderBy}, i.id DESC
             {$limit}"
        );
        $statement->bindValue('amount_threshold', $amountThreshold);

        foreach ($params as $key => $value) {
            $statement->bindValue($key, $value);
        }

        $statement->execute();
        $rows = $statement->fetchAll();

        return array_map(
            fn (array $row): array => $this->withReviewFlags($row),
            $rows
        );
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

    public function reviewSummary(array $filters): array
    {
        $params = [];
        $where = $this->buildWhere($filters, $params);
        $statement = $this->db()->prepare("SELECT COUNT(*) FROM trx_incomes i {$where}");
        $statement->execute($params);

        return [
            'needs_review' => (int) $statement->fetchColumn(),
            'amount_threshold' => $this->amountReviewThreshold(),
        ];
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
               AND i.deleted_at IS NULL
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        $income = $statement->fetch();

        return $income ?: null;
    }

    public function findDeleted(int $id): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT i.id, i.group_id, i.closing_id, i.transaction_date, i.reference_no,
                    i.payment_method, i.bank_target, i.income_type, i.client_name,
                    i.address, i.username, i.profile_package, i.amount, i.description,
                    i.deleted_at, i.deleted_by,
                    g.name AS group_name
             FROM trx_incomes i
             JOIN mst_groups g ON g.id = i.group_id
             WHERE i.id = :id
               AND i.deleted_at IS NOT NULL
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
             WHERE id = :id AND closing_id IS NULL AND deleted_at IS NULL'
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
        $statement = $this->db()->prepare(
            'UPDATE trx_incomes
             SET deleted_at = NOW(),
                 deleted_by = :deleted_by
             WHERE id = :id
               AND closing_id IS NULL
               AND deleted_at IS NULL'
        );

        $statement->execute([
            'id' => $id,
            'deleted_by' => $_SESSION['user']['username'] ?? $_SESSION['user']['name'] ?? 'system',
        ]);

        return $statement->rowCount() > 0;
    }

    public function restore(int $id): bool
    {
        $statement = $this->db()->prepare(
            'UPDATE trx_incomes
             SET deleted_at = NULL,
                 deleted_by = NULL
             WHERE id = :id
               AND closing_id IS NULL
               AND deleted_at IS NOT NULL'
        );
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }

    private function buildWhere(array $filters, array &$params): string
    {
        $where = [];

        if (($filters['deleted'] ?? '') === '1') {
            $where[] = 'i.deleted_at IS NOT NULL';
        } else {
            $where[] = 'i.deleted_at IS NULL';
        }

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

        if (($filters['needs_review'] ?? '') === '1') {
            $amountThreshold = $this->amountReviewThreshold();
            $where[] = "(
                i.amount > :review_amount_threshold
                OR TRIM(COALESCE(i.username, '')) = ''
                OR TRIM(COALESCE(i.client_name, '')) = ''
                OR EXISTS (
                    SELECT 1
                    FROM trx_incomes d
                    WHERE d.id <> i.id
                      AND d.group_id = i.group_id
                      AND d.deleted_at IS NULL
                      AND d.transaction_date = i.transaction_date
                      AND COALESCE(d.reference_no, '') = COALESCE(i.reference_no, '')
                      AND COALESCE(d.client_name, '') = COALESCE(i.client_name, '')
                )
            )";
            $params['review_amount_threshold'] = $amountThreshold;
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

    private function amountReviewThreshold(): float
    {
        $statement = $this->db()->query('SELECT COALESCE(AVG(amount), 0) FROM trx_incomes WHERE deleted_at IS NULL');
        $average = (float) $statement->fetchColumn();

        return max(250000.0, $average * 2.5);
    }

    private function withReviewFlags(array $row): array
    {
        $reasons = [];

        if ((int) $row['amount_needs_review'] === 1) {
            $reasons[] = 'Amount tidak wajar';
        }

        if ((int) $row['identity_needs_review'] === 1) {
            $reasons[] = 'Username/client kosong';
        }

        if ((int) $row['suspicious_duplicate_count'] > 1) {
            $reasons[] = 'Duplicate mencurigakan';
        }

        $row['review_reasons'] = $reasons;
        $row['needs_review'] = $reasons !== [];

        return $row;
    }
}
