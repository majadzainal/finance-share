<div class="d-flex flex-column gap-4">
    <div>
        <h1 class="h3 mb-1">Import Income</h1>
        <p class="text-secondary mb-0">Upload CSV income, review preview, lalu konfirmasi sebelum transaksi disimpan.</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger mb-0"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($result): ?>
        <div class="alert alert-success mb-0">
            Import selesai. Total <?= e($result['total_rows']) ?> row,
            success <?= e($result['success_rows']) ?>,
            duplicate <?= e($result['duplicate_rows']) ?>,
            error <?= e($result['error_rows'] ?? 0) ?>.
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="post" action="<?= e(url('/import-income')) ?>" enctype="multipart/form-data" class="row g-3">
                <div class="col-12 col-md-5">
                    <label for="group_id" class="form-label">Group / Store</label>
                    <select class="form-select" id="group_id" name="group_id" required>
                        <option value="">Select group/store</option>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?= e($group['id']) ?>" <?= (int) $selectedGroupId === (int) $group['id'] ? 'selected' : '' ?>>
                                <?= e($group['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-5">
                    <label for="income_file" class="form-label">CSV File</label>
                    <input class="form-control" type="file" id="income_file" name="income_file" accept=".csv,text/csv" required>
                </div>

                <div class="col-12 col-md-2 d-flex align-items-end">
                    <input type="hidden" name="action" value="preview">
                    <button type="submit" class="btn btn-primary w-100">Preview</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($preview): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex flex-column flex-md-row justify-content-between gap-2">
                <div>
                    <div class="fw-semibold">Import Preview</div>
                    <div class="small text-secondary"><?= e($preview['original_name']) ?> · <?= e(substr($preview['file_hash'], 0, 16)) ?></div>
                </div>
                <div class="d-flex gap-2">
                    <?php if ($preview['error_rows'] > 0): ?>
                        <a href="<?= e(url('/import-income/errors?token=' . $preview['token'])) ?>" class="btn btn-sm btn-outline-danger">Download Error CSV</a>
                    <?php endif; ?>
                    <form method="post" action="<?= e(url('/import-income')) ?>" class="mb-0">
                        <input type="hidden" name="action" value="confirm">
                        <input type="hidden" name="preview_token" value="<?= e($preview['token']) ?>">
                        <button type="submit" class="btn btn-sm btn-success" <?= $preview['can_import'] ? '' : 'disabled' ?>>Confirm Import</button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-3">
                        <div class="border rounded-2 p-3 h-100">
                            <div class="text-secondary small mb-1">Total Rows</div>
                            <div class="fs-4 fw-bold"><?= e($preview['total_rows']) ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="border rounded-2 p-3 h-100">
                            <div class="text-secondary small mb-1">Valid Rows</div>
                            <div class="fs-4 fw-bold text-success"><?= e($preview['valid_rows']) ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="border rounded-2 p-3 h-100">
                            <div class="text-secondary small mb-1">Duplicate Rows</div>
                            <div class="fs-4 fw-bold text-warning"><?= e($preview['duplicate_rows']) ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="border rounded-2 p-3 h-100">
                            <div class="text-secondary small mb-1">Error Rows</div>
                            <div class="fs-4 fw-bold text-danger"><?= e($preview['error_rows']) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Client</th>
                            <th>Username</th>
                            <th>Payment</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($preview['sample_rows'] === []): ?>
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">Tidak ada row valid untuk direview.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($preview['sample_rows'] as $row): ?>
                            <tr>
                                <td><?= e($row['payment_date']) ?></td>
                                <td class="fw-semibold"><?= e($row['reference_id'] ?: '-') ?></td>
                                <td><?= e($row['client_name'] ?: '-') ?></td>
                                <td><?= e($row['username'] ?: '-') ?></td>
                                <td>
                                    <div><?= e($row['payment_type'] ?: '-') ?></div>
                                    <div class="small text-secondary"><?= e($row['bank_target'] ?: '-') ?></div>
                                </td>
                                <td class="text-end fw-semibold">Rp <?= e(number_format((float) $row['amount'], 0, ',', '.')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card-body border-top small text-secondary">
                Menampilkan maksimal 20 row valid pertama. Duplicate dan error tidak akan disimpan saat confirm.
            </div>
        </div>
    <?php endif; ?>

    <?php if ($result): ?>
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-secondary small mb-1">Total Rows</div>
                        <div class="fs-4 fw-bold"><?= e($result['total_rows']) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-secondary small mb-1">Success Rows</div>
                        <div class="fs-4 fw-bold text-success"><?= e($result['success_rows']) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-secondary small mb-1">Duplicate Rows</div>
                        <div class="fs-4 fw-bold text-warning"><?= e($result['duplicate_rows']) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-secondary small mb-1">Error Rows</div>
                        <div class="fs-4 fw-bold text-danger"><?= e($result['error_rows'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Recent Imports</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Import Date</th>
                        <th>Group / Store</th>
                        <th>Filename</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Success</th>
                        <th class="text-end">Duplicate</th>
                        <th class="text-end">Error</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($imports === []): ?>
                        <tr>
                            <td colspan="8" class="text-center text-secondary py-4">Belum ada import income.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($imports as $import): ?>
                        <tr>
                            <td><?= e($import['import_date']) ?></td>
                            <td><?= e($import['group_name'] ?? '-') ?></td>
                            <td>
                                <div class="fw-semibold"><?= e($import['file_name']) ?></div>
                                <div class="small text-secondary"><?= e(substr($import['file_hash'], 0, 16)) ?></div>
                            </td>
                            <td class="text-end"><?= e($import['total_rows']) ?></td>
                            <td class="text-end"><?= e($import['success_rows']) ?></td>
                            <td class="text-end"><?= e($import['duplicate_rows']) ?></td>
                            <td class="text-end"><?= e($import['failed_rows'] ?? 0) ?></td>
                            <td><span class="badge text-bg-secondary"><?= e($import['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
