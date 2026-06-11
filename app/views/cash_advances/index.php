<div class="d-flex flex-column gap-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <h1 class="h3 mb-1">Cash Advances / Kasbon</h1>
            <p class="text-secondary mb-0">Daftar kasbon outstanding yang akan dipotong otomatis saat closing.</p>
        </div>
        <div>
            <a href="<?= e(url('/cash-advances/create')) ?>" class="btn btn-primary">Input Kasbon</a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-success mb-0">Kasbon berhasil <?= e($flash) ?>.</div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Member</th>
                        <th>Group / Store</th>
                        <th>Description</th>
                        <th>Transfer</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Remaining</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($cashAdvances === []): ?>
                        <tr>
                            <td colspan="9" class="text-center text-secondary py-4">Tidak ada kasbon outstanding.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($cashAdvances as $cashAdvance): ?>
                        <tr>
                            <td><?= e($cashAdvance['advance_date']) ?></td>
                            <td>
                                <div class="fw-semibold"><?= e($cashAdvance['member_name']) ?></div>
                                <div class="small text-secondary"><?= e($cashAdvance['nickname'] ?: '-') ?></div>
                            </td>
                            <td><?= e($cashAdvance['group_name'] ?: '-') ?></td>
                            <td><?= e($cashAdvance['description'] ?: '-') ?></td>
                            <td>
                                <div><?= e($cashAdvance['transfer_method_name'] ?: '-') ?></div>
                                <?php if ((float) ($cashAdvance['transfer_fee_amount'] ?? 0) > 0): ?>
                                    <div class="small text-secondary">Fee Rp <?= e(number_format((float) $cashAdvance['transfer_fee_amount'], 0, ',', '.')) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">Rp <?= e(number_format((float) $cashAdvance['amount'], 0, ',', '.')) ?></td>
                            <td class="text-end fw-semibold">Rp <?= e(number_format((float) $cashAdvance['remaining_amount'], 0, ',', '.')) ?></td>
                            <td><span class="badge text-bg-warning">Outstanding</span></td>
                            <td class="text-end">
                                <a href="<?= e(url('/cash-advances/' . $cashAdvance['id'])) ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
