<?php

namespace App\Services;

use App\Core\Model;
use RuntimeException;
use Throwable;

class IncomeImportService extends Model
{
    private const REQUIRED_COLUMNS = [
        'reference_id',
        'payment_date',
        'payment_type',
        'bank_target',
        'income_type',
        'client_name',
        'address',
        'username',
        'profile_package',
        'amount',
        'description',
    ];

    private const HEADER_MAP = [
        'reference id' => 'reference_id',
        'reference_id' => 'reference_id',
        'tanggal bayar' => 'payment_date',
        'payment date' => 'payment_date',
        'payment_date' => 'payment_date',
        'tipe payment' => 'payment_type',
        'payment type' => 'payment_type',
        'payment_type' => 'payment_type',
        'tujuan/bank' => 'bank_target',
        'tujuan bank' => 'bank_target',
        'bank target' => 'bank_target',
        'bank_target' => 'bank_target',
        'tipe income' => 'income_type',
        'income type' => 'income_type',
        'income_type' => 'income_type',
        'client' => 'client_name',
        'client name' => 'client_name',
        'client_name' => 'client_name',
        'alamat' => 'address',
        'address' => 'address',
        'username' => 'username',
        'profile' => 'profile_package',
        'profile package' => 'profile_package',
        'profile_package' => 'profile_package',
        'jumlah' => 'amount',
        'amount' => 'amount',
        'keterangan' => 'description',
        'description' => 'description',
    ];

    public function preview(int $groupId, array $file): array
    {
        $this->validateUpload($groupId, $file);

        $tmpPath = $file['tmp_name'];
        $fileHash = hash_file('sha256', $tmpPath);

        if ($this->fileHashExists($fileHash)) {
            throw new RuntimeException('File CSV ini sudah pernah di-import.');
        }

        $analysis = $this->analyzeCsvRows($tmpPath, $groupId);
        $token = bin2hex(random_bytes(16));
        $previewFile = $this->storePreviewFile($file, $token);

        $_SESSION['income_import_preview'][$token] = [
            'group_id' => $groupId,
            'preview_file' => $previewFile,
            'original_name' => basename($file['name'] ?? 'income-import.csv'),
            'file_hash' => $fileHash,
            'created_at' => time(),
        ];

        return array_merge($analysis, [
            'token' => $token,
            'file_hash' => $fileHash,
            'original_name' => basename($file['name'] ?? 'income-import.csv'),
            'can_import' => $analysis['valid_rows'] > 0,
        ]);
    }

    public function importPreview(string $token): array
    {
        $preview = $_SESSION['income_import_preview'][$token] ?? null;

        if (! is_array($preview)) {
            throw new RuntimeException('Preview import sudah tidak tersedia. Upload ulang file CSV.');
        }

        if (($preview['created_at'] ?? 0) < time() - 3600) {
            unset($_SESSION['income_import_preview'][$token]);
            throw new RuntimeException('Preview import sudah kedaluwarsa. Upload ulang file CSV.');
        }

        $path = base_path('storage/tmp/income_import_previews/' . $preview['preview_file']);

        if (! is_file($path)) {
            unset($_SESSION['income_import_preview'][$token]);
            throw new RuntimeException('File preview tidak ditemukan. Upload ulang file CSV.');
        }

        $groupId = (int) $preview['group_id'];
        $fileHash = (string) $preview['file_hash'];

        if ($this->fileHashExists($fileHash)) {
            throw new RuntimeException('File CSV ini sudah pernah di-import.');
        }

        $analysis = $this->analyzeCsvRows($path, $groupId);
        $storedFileName = $this->storePreviewAsImportFile($path, (string) $preview['original_name'], $fileHash);
        $db = $this->db();

        $db->beginTransaction();

        try {
            $importId = $this->createImportRecord(
                $groupId,
                (string) $preview['original_name'],
                $storedFileName,
                $fileHash,
                $analysis['total_rows']
            );
            $successRows = 0;

            foreach ($analysis['valid_data'] as $row) {
                if ($this->insertIncomeRow($groupId, $importId, $row)) {
                    $successRows++;
                }
            }

            $duplicateRows = $analysis['duplicate_rows'] + (count($analysis['valid_data']) - $successRows);
            $this->updateImportRecord($importId, $successRows, $duplicateRows, $analysis['error_rows'], 'completed');
            $db->commit();
            unset($_SESSION['income_import_preview'][$token]);
            @unlink($path);

            return [
                'import_id' => $importId,
                'filename' => $storedFileName,
                'file_hash' => $fileHash,
                'total_rows' => $analysis['total_rows'],
                'success_rows' => $successRows,
                'duplicate_rows' => $duplicateRows,
                'error_rows' => $analysis['error_rows'],
            ];
        } catch (Throwable $exception) {
            $db->rollBack();
            throw $exception;
        }
    }

    public function downloadPreviewErrors(string $token): void
    {
        $preview = $_SESSION['income_import_preview'][$token] ?? null;

        if (! is_array($preview)) {
            throw new RuntimeException('Preview import sudah tidak tersedia. Upload ulang file CSV.');
        }

        $path = base_path('storage/tmp/income_import_previews/' . $preview['preview_file']);

        if (! is_file($path)) {
            throw new RuntimeException('File preview tidak ditemukan. Upload ulang file CSV.');
        }

        $analysis = $this->analyzeCsvRows($path, (int) $preview['group_id']);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="income-import-errors.csv"');

        $output = fopen('php://output', 'wb');
        fputcsv($output, ['row_number', 'error', 'reference_id', 'payment_date', 'username', 'amount', 'description']);

        foreach ($analysis['error_data'] as $error) {
            fputcsv($output, [
                $error['row_number'],
                $error['error'],
                $error['row']['reference_id'] ?? '',
                $error['row']['payment_date'] ?? '',
                $error['row']['username'] ?? '',
                $error['row']['amount'] ?? '',
                $error['row']['description'] ?? '',
            ]);
        }

        fclose($output);
    }

    public function recentImports(): array
    {
        $statement = $this->db()->query(
            'SELECT i.id, i.file_name, i.file_hash, i.total_rows, i.success_rows,
                    i.duplicate_rows, i.failed_rows, i.import_date, i.status, g.name AS group_name
             FROM trx_imports i
             LEFT JOIN mst_groups g ON g.id = i.group_id
             ORDER BY i.import_date DESC, i.id DESC
             LIMIT 10'
        );

        return $statement->fetchAll();
    }

    private function validateUpload(int $groupId, array $file): void
    {
        if ($groupId <= 0) {
            throw new RuntimeException('Group/store wajib dipilih.');
        }

        if (! $this->groupExists($groupId)) {
            throw new RuntimeException('Group/store tidak ditemukan.');
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('File CSV wajib di-upload.');
        }

        $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));

        if ($extension !== 'csv') {
            throw new RuntimeException('File harus berformat CSV.');
        }
    }

    private function groupExists(int $groupId): bool
    {
        $statement = $this->db()->prepare('SELECT COUNT(*) FROM mst_groups WHERE id = :id');
        $statement->execute(['id' => $groupId]);

        return (int) $statement->fetchColumn() > 0;
    }

    private function fileHashExists(string $fileHash): bool
    {
        $statement = $this->db()->prepare('SELECT COUNT(*) FROM trx_imports WHERE file_hash = :file_hash');
        $statement->execute(['file_hash' => $fileHash]);

        return (int) $statement->fetchColumn() > 0;
    }

    private function storePreviewFile(array $file, string $token): string
    {
        $directory = base_path('storage/tmp/income_import_previews');

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $previewFile = $token . '.csv';
        $targetPath = $directory . DIRECTORY_SEPARATOR . $previewFile;

        if (! move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new RuntimeException('Gagal menyimpan file preview.');
        }

        return $previewFile;
    }

    private function storePreviewAsImportFile(string $path, string $originalName, string $fileHash): string
    {
        $directory = base_path('storage/uploads/income_imports');

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName) ?: 'income-import.csv';
        $storedFileName = date('YmdHis') . '_' . substr($fileHash, 0, 12) . '_' . $safeName;
        $targetPath = $directory . DIRECTORY_SEPARATOR . $storedFileName;

        if (! copy($path, $targetPath)) {
            throw new RuntimeException('Gagal menyimpan file import.');
        }

        return $storedFileName;
    }

    private function analyzeCsvRows(string $path, int $groupId): array
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('File CSV tidak bisa dibaca.');
        }

        $header = fgetcsv($handle, 0, ';');

        if ($header === false) {
            fclose($handle);
            throw new RuntimeException('File CSV kosong.');
        }

        $columnMap = $this->buildColumnMap($header);
        $validRows = [];
        $sampleRows = [];
        $errorRows = [];
        $duplicateRows = 0;
        $seenKeys = [];
        $rowNumber = 1;

        while (($line = fgetcsv($handle, 0, ';')) !== false) {
            $rowNumber++;

            if ($this->isBlankRow($line)) {
                continue;
            }

            $rawRow = $this->mapCsvLine($line, $columnMap);

            if ($this->isSummaryRow($rawRow)) {
                continue;
            }

            try {
                $row = $this->normalizeRow($rawRow);
                $duplicateKey = $this->duplicateKey($groupId, $row);

                if (isset($seenKeys[$duplicateKey]) || $this->incomeRowExists($groupId, $row)) {
                    $duplicateRows++;
                    continue;
                }

                $seenKeys[$duplicateKey] = true;
                $validRows[] = $row;

                if (count($sampleRows) < 20) {
                    $sampleRows[] = $row;
                }
            } catch (RuntimeException $exception) {
                $errorRows[] = [
                    'row_number' => $rowNumber,
                    'error' => $exception->getMessage(),
                    'row' => $rawRow,
                ];
            }
        }

        fclose($handle);

        return [
            'total_rows' => count($validRows) + $duplicateRows + count($errorRows),
            'valid_rows' => count($validRows),
            'duplicate_rows' => $duplicateRows,
            'error_rows' => count($errorRows),
            'sample_rows' => $sampleRows,
            'error_data' => $errorRows,
            'valid_data' => $validRows,
        ];
    }

    private function readCsvRows(string $path): array
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('File CSV tidak bisa dibaca.');
        }

        $header = fgetcsv($handle, 0, ';');

        if ($header === false) {
            fclose($handle);
            throw new RuntimeException('File CSV kosong.');
        }

        $columnMap = $this->buildColumnMap($header);
        $rows = [];

        while (($line = fgetcsv($handle, 0, ';')) !== false) {
            if ($this->isBlankRow($line)) {
                continue;
            }

            $row = $this->mapCsvLine($line, $columnMap);

            if ($this->isSummaryRow($row)) {
                continue;
            }

            $rows[] = $this->normalizeRow($row);
        }

        fclose($handle);

        return $rows;
    }

    private function mapCsvLine(array $line, array $columnMap): array
    {
        $row = [];

        foreach ($columnMap as $index => $field) {
            $row[$field] = trim((string) ($line[$index] ?? ''));
        }

        return $row;
    }

    private function normalizeRow(array $row): array
    {
        $row['payment_date'] = $this->parseDate($row['payment_date'] ?? '');
        $row['amount'] = $this->parseAmount($row['amount'] ?? '');
        $row['raw_payload'] = $this->encodeRawPayload($row);

        return $row;
    }

    private function buildColumnMap(array $header): array
    {
        $columnMap = [];

        foreach ($header as $index => $column) {
            $normalized = $this->normalizeHeader((string) $column);

            if (isset(self::HEADER_MAP[$normalized])) {
                $columnMap[$index] = self::HEADER_MAP[$normalized];
            }
        }

        $missing = array_diff(self::REQUIRED_COLUMNS, array_values($columnMap));

        if ($missing !== []) {
            throw new RuntimeException('Kolom CSV belum lengkap: ' . implode(', ', $missing));
        }

        return $columnMap;
    }

    private function normalizeHeader(string $header): string
    {
        $header = trim(str_replace("\xEF\xBB\xBF", '', $header));
        $header = strtolower(str_replace(['-', '_'], ' ', $header));

        return preg_replace('/\s+/', ' ', $header) ?: '';
    }

    private function isBlankRow(array $row): bool
    {
        return trim(implode('', $row)) === '';
    }

    private function isSummaryRow(array $row): bool
    {
        $values = array_map(static fn ($value) => strtoupper(trim((string) $value)), $row);

        return ($row['reference_id'] ?? '') === ''
            && ($row['payment_date'] ?? '') === ''
            && in_array('TOTAL:', $values, true);
    }

    private function parseDate(string $value): string
    {
        $value = trim($value);
        $months = [
            'Jan' => 'Jan',
            'Feb' => 'Feb',
            'Mar' => 'Mar',
            'Apr' => 'Apr',
            'Mei' => 'May',
            'Jun' => 'Jun',
            'Jul' => 'Jul',
            'Agu' => 'Aug',
            'Ags' => 'Aug',
            'Sep' => 'Sep',
            'Okt' => 'Oct',
            'Nov' => 'Nov',
            'Des' => 'Dec',
        ];

        $value = str_replace(array_keys($months), array_values($months), $value);
        $timestamp = strtotime($value);

        if ($timestamp === false) {
            throw new RuntimeException("Tanggal bayar tidak valid: {$value}");
        }

        return date('Y-m-d', $timestamp);
    }

    private function parseAmount(string $value): float
    {
        $value = trim($value);
        $value = str_replace(['Rp', 'rp', ' ', ',00'], '', $value);

        if (preg_match('/^\d{1,3}(\.\d{3})+$/', $value)) {
            $value = str_replace('.', '', $value);
        } else {
            $value = str_replace(',', '.', $value);
        }

        if (! is_numeric($value) || (float) $value <= 0) {
            throw new RuntimeException('Jumlah income tidak valid.');
        }

        return (float) $value;
    }

    private function encodeRawPayload(array $row): string
    {
        $payload = json_encode($row, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        if ($payload === false || $payload === '') {
            $payload = json_encode($this->sanitizeForJson($row), JSON_UNESCAPED_UNICODE);
        }

        if ($payload === false || $payload === '') {
            throw new RuntimeException('Raw payload CSV tidak bisa dikonversi ke JSON.');
        }

        return $payload;
    }

    private function sanitizeForJson(array $row): array
    {
        foreach ($row as $key => $value) {
            if (is_string($value)) {
                $row[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
            }
        }

        return $row;
    }

    private function createImportRecord(int $groupId, string $originalName, string $storedFileName, string $fileHash, int $totalRows): int
    {
        $statement = $this->db()->prepare(
            'INSERT INTO trx_imports
                (group_id, file_name, file_hash, original_file_name, source, imported_at, import_date, total_rows, status)
             VALUES
                (:group_id, :file_name, :file_hash, :original_file_name, :source, NOW(), NOW(), :total_rows, :status)'
        );
        $statement->execute([
            'group_id' => $groupId,
            'file_name' => $storedFileName,
            'file_hash' => $fileHash,
            'original_file_name' => $originalName,
            'source' => 'csv_income',
            'total_rows' => $totalRows,
            'status' => 'processing',
        ]);

        return (int) $this->db()->lastInsertId();
    }

    private function updateImportRecord(int $importId, int $successRows, int $duplicateRows, int $failedRows, string $status): void
    {
        $statement = $this->db()->prepare(
            'UPDATE trx_imports
             SET success_rows = :success_rows,
                 duplicate_rows = :duplicate_rows,
                 failed_rows = :failed_rows,
                 status = :status
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $importId,
            'success_rows' => $successRows,
            'duplicate_rows' => $duplicateRows,
            'failed_rows' => $failedRows,
            'status' => $status,
        ]);
    }

    private function incomeRowExists(int $groupId, array $row): bool
    {
        $statement = $this->db()->prepare(
            'SELECT COUNT(*)
             FROM trx_incomes
             WHERE group_id = :group_id
               AND deleted_at IS NULL
               AND transaction_date = :transaction_date
               AND COALESCE(reference_no, \'\') = :reference_no
               AND COALESCE(external_id, \'\') = :external_id
               AND COALESCE(description, \'\') = :description
               AND amount = :amount
               AND COALESCE(payment_method, \'\') = :payment_method'
        );
        $statement->execute([
            'group_id' => $groupId,
            'transaction_date' => $row['payment_date'],
            'reference_no' => $row['reference_id'],
            'external_id' => $row['username'],
            'description' => $row['description'],
            'amount' => $row['amount'],
            'payment_method' => $row['payment_type'],
        ]);

        return (int) $statement->fetchColumn() > 0;
    }

    private function duplicateKey(int $groupId, array $row): string
    {
        return implode('|', [
            $groupId,
            $row['payment_date'],
            $row['reference_id'],
            $row['username'],
            $row['description'],
            number_format((float) $row['amount'], 2, '.', ''),
            $row['payment_type'],
        ]);
    }

    private function insertIncomeRow(int $groupId, int $importId, array $row): bool
    {
        $statement = $this->db()->prepare(
            'INSERT IGNORE INTO trx_incomes
                (group_id, import_id, transaction_date, reference_no, external_id,
                 bank_target, income_type, client_name, address, username, profile_package,
                 description, amount, payment_method, raw_payload)
             VALUES
                (:group_id, :import_id, :transaction_date, :reference_no, :external_id,
                 :bank_target, :income_type, :client_name, :address, :username, :profile_package,
                 :description, :amount, :payment_method, :raw_payload)'
        );
        $statement->execute([
            'group_id' => $groupId,
            'import_id' => $importId,
            'transaction_date' => $row['payment_date'],
            'reference_no' => $row['reference_id'],
            'external_id' => $row['username'],
            'bank_target' => $row['bank_target'],
            'income_type' => $row['income_type'],
            'client_name' => $row['client_name'],
            'address' => $row['address'],
            'username' => $row['username'],
            'profile_package' => $row['profile_package'],
            'description' => $row['description'],
            'amount' => $row['amount'],
            'payment_method' => $row['payment_type'],
            'raw_payload' => $row['raw_payload'],
        ]);

        return $statement->rowCount() === 1;
    }
}
