<div class="d-flex flex-column gap-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <h1 class="h3 mb-1">Saldo Rekening</h1>
            <p class="text-secondary mb-0">Input manual saldo 3 rekening penampungan dan cek selisih dengan saldo ledger.</p>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-success mb-0">Saldo rekening berhasil diperbarui.</div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-secondary small mb-1">Total Saldo Rekening</div>
                    <div class="h4 mb-0">Rp <?= e(number_format($manualTotal, 0, ',', '.')) ?></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-secondary small mb-1">Saldo Seharusnya</div>
                    <div class="h4 mb-0">Rp <?= e(number_format($expectedBalance, 0, ',', '.')) ?></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <div class="text-secondary small mb-1">Selisih</div>
                            <div class="h4 mb-0">Rp <?= e(number_format(abs($difference), 0, ',', '.')) ?></div>
                        </div>
                        <?php if ($isBalanced): ?>
                            <span class="badge text-bg-success">Balance</span>
                        <?php else: ?>
                            <span class="badge text-bg-danger">Tidak Balance</span>
                        <?php endif; ?>
                    </div>
                    <?php if (! $isBalanced): ?>
                        <div class="small text-secondary mt-2">
                            <?= $difference > 0 ? 'Saldo rekening lebih besar dari ledger.' : 'Saldo rekening lebih kecil dari ledger.' ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="post" action="<?= e(url('/account-balances')) ?>" class="d-flex flex-column gap-3">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th>Rekening</th>
                                <th class="text-end">Saldo Manual</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($accounts as $account): ?>
                                <tr>
                                    <td class="fw-semibold"><?= e($account['code']) ?></td>
                                    <td><?= e($account['name']) ?></td>
                                    <td style="min-width: 220px;">
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="form-control text-end <?= isset($errors[$account['id']]) ? 'is-invalid' : '' ?>"
                                            name="balances[<?= e($account['id']) ?>]"
                                            value="<?= e($account['current_balance']) ?>"
                                            required
                                        >
                                        <?php if (isset($errors[$account['id']])): ?>
                                            <div class="invalid-feedback"><?= e($errors[$account['id']]) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="min-width: 260px;">
                                        <input
                                            type="text"
                                            class="form-control"
                                            name="notes[<?= e($account['id']) ?>]"
                                            value="<?= e($account['notes'] ?? '') ?>"
                                            maxlength="255"
                                        >
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Simpan Saldo</button>
                </div>
            </form>
        </div>
    </div>
</div>
