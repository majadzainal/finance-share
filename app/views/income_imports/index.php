<div class="d-flex flex-column gap-4">
    <div>
        <h1 class="h3 mb-1">Import Income</h1>
        <p class="text-secondary mb-0">Upload CSV income dan simpan transaksi ke ledger income.</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger mb-0"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($result): ?>
        <div class="alert alert-success mb-0">
            Import selesai. Total <?= e($result['total_rows']) ?> row,
            success <?= e($result['success_rows']) ?>,
            duplicate <?= e($result['duplicate_rows']) ?>.
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
                    <button type="submit" class="btn btn-primary w-100">Import</button>
                </div>
            </form>
        </div>
    </div>

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
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($imports === []): ?>
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-4">Belum ada import income.</td>
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
                            <td><span class="badge text-bg-secondary"><?= e($import['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
