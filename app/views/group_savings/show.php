<div class="d-flex flex-column gap-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="<?= e(url('/group-savings')) ?>" class="text-decoration-none text-secondary">
                    &larr; Kembali
                </a>
                <span class="text-secondary">/</span>
                <span class="badge text-bg-light border"><?= e($group['code']) ?></span>
            </div>
            <h1 class="h3 mb-0"><?= e($group['name']) ?> - Riwayat Tabungan</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= e(url('/group-savings/deposit?group_id=' . $group['id'])) ?>" class="btn btn-outline-success">
                + Setor Tabungan
            </a>
            <a href="<?= e(url('/group-savings/withdraw?group_id=' . $group['id'])) ?>" class="btn btn-danger">
                - Tarik / Keluar Tabungan
            </a>
        </div>
    </div>

    <?php if ($flash === 'withdrawn'): ?>
        <div class="alert alert-success mb-0">Pengeluaran/penarikan tabungan toko berhasil dicatat.</div>
    <?php elseif ($flash === 'deposited'): ?>
        <div class="alert alert-success mb-0">Setoran tabungan toko berhasil dicatat.</div>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div class="row g-3">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body">
                    <div class="small text-white-50 mb-1">Saldo Tabungan Saat Ini</div>
                    <div class="fs-3 fw-bold">Rp <?= e(number_format($currentBalance, 0, ',', '.')) ?></div>
                    <div class="small text-white-50 mt-1"><?= count($mutations) ?> transaksi tercatat</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-secondary mb-1">Total Tabungan Masuk (Disisihkan)</div>
                    <div class="fs-4 fw-bold text-success">Rp <?= e(number_format($totalDeposited, 0, ',', '.')) ?></div>
                    <div class="small text-secondary mt-1">Akumulasi penyisihan closing & setoran</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-secondary mb-1">Total Tabungan Dikeluarkan (Ditarik)</div>
                    <div class="fs-4 fw-bold text-danger">Rp <?= e(number_format($totalWithdrawn, 0, ',', '.')) ?></div>
                    <div class="small text-secondary mt-1">Pengeluaran keperluan cadangan toko</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="get" action="<?= e(url('/group-savings/' . $group['id'])) ?>" class="row g-3">
                <div class="col-12 col-md-3">
                    <label class="form-label small">Tipe Mutasi</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">Semua Tipe</option>
                        <option value="deposit" <?= ($filters['type'] ?? '') === 'deposit' ? 'selected' : '' ?>>Deposit / Masuk</option>
                        <option value="withdrawal" <?= ($filters['type'] ?? '') === 'withdrawal' ? 'selected' : '' ?>>Withdrawal / Keluar</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control form-select-sm" value="<?= e($filters['start_date'] ?? '') ?>">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control form-select-sm" value="<?= e($filters['end_date'] ?? '') ?>">
                </div>
                <div class="col-12 col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Terapkan Filter</button>
                    <a href="<?= e(url('/group-savings/' . $group['id'])) ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Sumber</th>
                        <th>Keterangan</th>
                        <th>No. Ref</th>
                        <th class="text-end">Nominal</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($mutations)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-secondary">Belum ada mutasi tabungan untuk filter ini.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($mutations as $m): ?>
                            <tr>
                                <td><?= e(date('d/m/Y', strtotime($m['transaction_date']))) ?></td>
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
                                <td><?= e($m['description'] ?: '-') ?></td>
                                <td><small class="text-secondary"><?= e($m['reference_no'] ?: '-') ?></small></td>
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
    </div>
</div>
