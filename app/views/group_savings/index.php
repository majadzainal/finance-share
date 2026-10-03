<div class="d-flex flex-column gap-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h1 class="h3 mb-1">Tabungan Group / Store</h1>
            <p class="text-secondary mb-0">Kelola dana cadangan & saldo tabungan masing-masing toko/group.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= e(url('/group-savings/deposit')) ?>" class="btn btn-outline-success">
                <i class="bi bi-plus-circle me-1"></i> + Setor Tabungan
            </a>
            <a href="<?= e(url('/group-savings/withdraw')) ?>" class="btn btn-primary">
                <i class="bi bi-dash-circle me-1"></i> - Tarik / Keluar Tabungan
            </a>
        </div>
    </div>

    <?php if ($flash === 'withdrawn'): ?>
        <div class="alert alert-success mb-0">Pengeluaran/penarikan tabungan toko berhasil dicatat.</div>
    <?php elseif ($flash === 'deposited'): ?>
        <div class="alert alert-success mb-0">Setoran tabungan toko berhasil dicatat.</div>
    <?php endif; ?>

    <?php if ($flashError): ?>
        <div class="alert alert-danger mb-0"><?= e($flashError) ?></div>
    <?php endif; ?>

    <!-- Total Summary Card -->
    <div class="row g-3">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body">
                    <div class="small text-white-50 mb-1">Total Saldo Semua Tabungan</div>
                    <div class="fs-4 fw-bold">Rp <?= e(number_format($totalAllSavings, 0, ',', '.')) ?></div>
                    <div class="small text-white-50 mt-1"><?= count($groupBalances) ?> Store / Group terdaftar</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Group Balances Cards -->
    <div class="row g-3">
        <?php foreach ($groupBalances as $gb): ?>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge text-bg-light border text-secondary mb-1"><?= e($gb['code']) ?></span>
                                    <h5 class="card-title mb-0"><?= e($gb['name']) ?></h5>
                                </div>
                                <?php if ((int)$gb['is_active'] === 1): ?>
                                    <span class="badge text-bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">Non-active</span>
                                <?php endif; ?>
                            </div>

                            <div class="p-3 bg-light rounded-3 my-3">
                                <div class="text-secondary small">Saldo Tabungan Saat Ini</div>
                                <div class="fs-4 fw-bold text-primary">
                                    Rp <?= e(number_format((float) $gb['current_balance'], 0, ',', '.')) ?>
                                </div>
                            </div>

                            <div class="small text-secondary mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Total Masuk / Disisihkan:</span>
                                    <span class="text-success fw-semibold">Rp <?= e(number_format((float) $gb['total_deposited'], 0, ',', '.')) ?></span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Total Dikeluarkan / Ditarik:</span>
                                    <span class="text-danger fw-semibold">Rp <?= e(number_format((float) $gb['total_withdrawn'], 0, ',', '.')) ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 pt-2 border-top">
                            <a href="<?= e(url('/group-savings/' . $gb['id'])) ?>" class="btn btn-sm btn-outline-secondary flex-grow-1">
                                Riwayat Mutasi
                            </a>
                            <a href="<?= e(url('/group-savings/withdraw?group_id=' . $gb['id'])) ?>" class="btn btn-sm btn-outline-danger" title="Tarik Uang Tabungan">
                                Tarik
                            </a>
                            <a href="<?= e(url('/group-savings/deposit?group_id=' . $gb['id'])) ?>" class="btn btn-sm btn-outline-success" title="Setor Tabungan">
                                Setor
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Filter & All Mutations Table -->
    <div class="card border-0 shadow-sm mt-2">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0">Riwayat Mutasi Tabungan</h5>
        </div>
        <div class="card-body border-bottom bg-light">
            <form method="get" action="<?= e(url('/group-savings')) ?>" class="row g-3">
                <div class="col-12 col-md-3">
                    <label class="form-label small">Group / Store</label>
                    <select name="group_id" class="form-select form-select-sm">
                        <option value="">Semua Store / Group</option>
                        <?php foreach ($groups as $g): ?>
                            <option value="<?= e($g['id']) ?>" <?= ($filters['group_id'] ?? '') == $g['id'] ? 'selected' : '' ?>>
                                <?= e($g['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label small">Tipe Mutasi</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">Semua Tipe</option>
                        <option value="deposit" <?= ($filters['type'] ?? '') === 'deposit' ? 'selected' : '' ?>>Deposit / Masuk</option>
                        <option value="withdrawal" <?= ($filters['type'] ?? '') === 'withdrawal' ? 'selected' : '' ?>>Withdrawal / Keluar</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label small">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control form-select-sm" value="<?= e($filters['start_date'] ?? '') ?>">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label small">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control form-select-sm" value="<?= e($filters['end_date'] ?? '') ?>">
                </div>
                <div class="col-12 col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Filter</button>
                    <a href="<?= e(url('/group-savings')) ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Store / Group</th>
                        <th>Tipe</th>
                        <th>Sumber</th>
                        <th>Keterangan</th>
                        <th class="text-end">Nominal</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($mutations)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-secondary">Belum ada data mutasi tabungan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($mutations as $m): ?>
                            <tr>
                                <td><?= e(date('d/m/Y', strtotime($m['transaction_date']))) ?></td>
                                <td>
                                    <div class="fw-semibold"><?= e($m['group_name']) ?></div>
                                    <small class="text-secondary"><?= e($m['group_code']) ?></small>
                                </td>
                                <td>
                                    <?php if ($m['type'] === 'deposit'): ?>
                                        <span class="badge text-bg-success">Masuk / Disisihkan</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-danger">Keluar / Ditarik</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($m['source'] === 'closing'): ?>
                                        <span class="badge text-bg-info">Closing #<?= e($m['closing_id']) ?></span>
                                    <?php elseif ($m['source'] === 'manual_deposit'): ?>
                                        <span class="badge text-bg-secondary">Manual Setor</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-warning">Manual Tarik</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><?= e($m['description'] ?: '-') ?></div>
                                    <?php if (!empty($m['reference_no'])): ?>
                                        <small class="text-secondary">Ref: <?= e($m['reference_no']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-semibold <?= $m['type'] === 'deposit' ? 'text-success' : 'text-danger' ?>">
                                    <?= $m['type'] === 'deposit' ? '+' : '-' ?> Rp <?= e(number_format((float) $m['amount'], 0, ',', '.')) ?>
                                </td>
                                <td class="small text-secondary"><?= e($m['created_by'] ?: '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (($pagination['totalPages'] ?? 0) > 1): ?>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
                <div class="small text-secondary">
                    Halaman <?= e($pagination['page']) ?> dari <?= e($pagination['totalPages']) ?> (Total <?= e($pagination['total']) ?> mutasi)
                </div>
                <div class="btn-group btn-group-sm">
                    <?php if ($pagination['page'] > 1): ?>
                        <a href="<?= e(url('/group-savings?' . http_build_query(array_merge($filters, ['page' => $pagination['page'] - 1])))) ?>" class="btn btn-outline-secondary">&laquo; Prev</a>
                    <?php endif; ?>
                    <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                        <a href="<?= e(url('/group-savings?' . http_build_query(array_merge($filters, ['page' => $pagination['page'] + 1])))) ?>" class="btn btn-outline-secondary">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
