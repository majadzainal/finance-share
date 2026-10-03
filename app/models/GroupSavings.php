<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use RuntimeException;

class GroupSavings extends Model
{
    public function getBalance(int $groupId): float
    {
        $statement = $this->db()->prepare(
            "SELECT 
                COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE -amount END), 0)
             FROM trx_group_savings
             WHERE group_id = :group_id"
        );
        $statement->execute(['group_id' => $groupId]);

        return (float) $statement->fetchColumn();
    }

    public function allGroupBalances(): array
    {
        $statement = $this->db()->query(
            "SELECT 
                g.id, g.code, g.name, g.is_active,
                COALESCE(SUM(CASE WHEN s.type = 'deposit' THEN s.amount ELSE 0 END), 0) AS total_deposited,
                COALESCE(SUM(CASE WHEN s.type = 'withdrawal' THEN s.amount ELSE 0 END), 0) AS total_withdrawn,
                COALESCE(SUM(CASE WHEN s.type = 'deposit' THEN s.amount ELSE -s.amount END), 0) AS current_balance,
                MAX(s.transaction_date) AS last_activity_date,
                COUNT(s.id) AS total_transactions
             FROM mst_groups g
             LEFT JOIN trx_group_savings s ON s.group_id = g.id
             GROUP BY g.id, g.code, g.name, g.is_active
             ORDER BY g.name ASC"
        );

        return $statement->fetchAll();
    }

    public function totalAllBalances(): float
    {
        $statement = $this->db()->query(
            "SELECT 
                COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE -amount END), 0)
             FROM trx_group_savings"
        );

        return (float) $statement->fetchColumn();
    }

    public function getMutations(int $groupId, ?string $startDate = null, ?string $endDate = null, ?string $type = null): array
    {
        $sql = "SELECT s.*, g.name AS group_name, g.code AS group_code, c.period_start, c.period_end
                FROM trx_group_savings s
                JOIN mst_groups g ON g.id = s.group_id
                LEFT JOIN trx_closings c ON c.id = s.closing_id
                WHERE s.group_id = :group_id";
        $params = ['group_id' => $groupId];

        if (!empty($startDate)) {
            $sql .= " AND s.transaction_date >= :start_date";
            $params['start_date'] = $startDate;
        }

        if (!empty($endDate)) {
            $sql .= " AND s.transaction_date <= :end_date";
            $params['end_date'] = $endDate;
        }

        if (!empty($type)) {
            $sql .= " AND s.type = :type";
            $params['type'] = $type;
        }

        $sql .= " ORDER BY s.transaction_date DESC, s.id DESC";

        $statement = $this->db()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function paginateMutations(array $filters, int $page = 1, int|string $perPage = 20): array
    {
        $params = [];
        $where = $this->buildWhere($filters, $params);
        $limit = '';

        if ($perPage !== 'all') {
            $offset = ($page - 1) * (int) $perPage;
            $limit = "LIMIT {$perPage} OFFSET {$offset}";
        }

        $statement = $this->db()->prepare(
            "SELECT s.*, g.name AS group_name, g.code AS group_code, c.period_start, c.period_end
             FROM trx_group_savings s
             JOIN mst_groups g ON g.id = s.group_id
             LEFT JOIN trx_closings c ON c.id = s.closing_id
             {$where}
             ORDER BY s.transaction_date DESC, s.id DESC
             {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function countMutations(array $filters): int
    {
        $params = [];
        $where = $this->buildWhere($filters, $params);
        $statement = $this->db()->prepare(
            "SELECT COUNT(*)
             FROM trx_group_savings s
             JOIN mst_groups g ON g.id = s.group_id
             {$where}"
        );
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db()->prepare(
            "SELECT s.*, g.name AS group_name, g.code AS group_code
             FROM trx_group_savings s
             JOIN mst_groups g ON g.id = s.group_id
             WHERE s.id = :id
             LIMIT 1"
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row ?: null;
    }

    public function recordDeposit(
        int $groupId,
        float $amount,
        string $transactionDate,
        string $source = 'closing',
        ?int $closingId = null,
        ?string $description = null,
        ?string $referenceNo = null,
        ?string $createdBy = null
    ): int {
        if ($amount <= 0) {
            throw new RuntimeException('Nominal deposit tabungan harus lebih besar dari 0.');
        }

        $statement = $this->db()->prepare(
            "INSERT INTO trx_group_savings
                (group_id, closing_id, transaction_date, type, amount, source, reference_no, description, created_by)
             VALUES
                (:group_id, :closing_id, :transaction_date, 'deposit', :amount, :source, :reference_no, :description, :created_by)"
        );
        $statement->execute([
            'group_id' => $groupId,
            'closing_id' => $closingId,
            'transaction_date' => $transactionDate,
            'amount' => $amount,
            'source' => $source,
            'reference_no' => $referenceNo,
            'description' => $description ?: 'Setor Tabungan Toko',
            'created_by' => $createdBy,
        ]);

        return (int) $this->db()->lastInsertId();
    }

    public function recordWithdrawal(
        int $groupId,
        float $amount,
        string $transactionDate,
        ?string $description = null,
        ?string $receiptFile = null,
        ?string $referenceNo = null,
        ?string $createdBy = null
    ): int {
        if ($amount <= 0) {
            throw new RuntimeException('Nominal penarikan tabungan harus lebih besar dari 0.');
        }

        $currentBalance = $this->getBalance($groupId);
        if ($amount > $currentBalance) {
            throw new RuntimeException(
                sprintf('Saldo tabungan tidak mencukupi. Saldo saat ini: Rp %s, Permintaan: Rp %s',
                    number_format($currentBalance, 0, ',', '.'),
                    number_format($amount, 0, ',', '.')
                )
            );
        }

        $statement = $this->db()->prepare(
            "INSERT INTO trx_group_savings
                (group_id, transaction_date, type, amount, source, reference_no, description, receipt_file, created_by)
             VALUES
                (:group_id, :transaction_date, 'withdrawal', :amount, 'manual_withdrawal', :reference_no, :description, :receipt_file, :created_by)"
        );
        $statement->execute([
            'group_id' => $groupId,
            'transaction_date' => $transactionDate,
            'amount' => $amount,
            'reference_no' => $referenceNo,
            'description' => $description ?: 'Pengeluaran/Penarikan Tabungan Toko',
            'receipt_file' => $receiptFile,
            'created_by' => $createdBy,
        ]);

        $savingsId = (int) $this->db()->lastInsertId();

        // Also record to ledger
        $ledgerStatement = $this->db()->prepare(
            "INSERT INTO trx_ledger
                (group_id, member_id, transaction_date, source_table, source_id, account_code, account_name, debit, credit, description)
             VALUES
                (:group_id, NULL, :transaction_date, 'trx_group_savings', :source_id, '3100', 'Pengeluaran Tabungan Toko', 0, :credit, :description)"
        );
        $ledgerStatement->execute([
            'group_id' => $groupId,
            'transaction_date' => $transactionDate,
            'source_id' => $savingsId,
            'credit' => $amount,
            'description' => 'Penarikan dana tabungan toko: ' . ($description ?: '-'),
        ]);

        return $savingsId;
    }

    private function buildWhere(array $filters, array &$params): string
    {
        $clauses = [];

        if (!empty($filters['group_id'])) {
            $clauses[] = 's.group_id = :group_id';
            $params['group_id'] = (int) $filters['group_id'];
        }

        if (!empty($filters['type'])) {
            $clauses[] = 's.type = :type';
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['source'])) {
            $clauses[] = 's.source = :source';
            $params['source'] = $filters['source'];
        }

        if (!empty($filters['start_date'])) {
            $clauses[] = 's.transaction_date >= :start_date';
            $params['start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $clauses[] = 's.transaction_date <= :end_date';
            $params['end_date'] = $filters['end_date'];
        }

        if (!empty($filters['search'])) {
            $clauses[] = '(s.description LIKE :search OR s.reference_no LIKE :search OR g.name LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        return $clauses === [] ? '' : 'WHERE ' . implode(' AND ', $clauses);
    }
}
