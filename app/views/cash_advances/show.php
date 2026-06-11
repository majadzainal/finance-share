<div class="d-flex flex-column gap-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <h1 class="h3 mb-1">Kasbon Detail</h1>
            <p class="text-secondary mb-0">Histori pembayaran kasbon ditampilkan dari proses closing otomatis.</p>
        </div>
        <div>
            <a href="<?= e(url('/cash-advances')) ?>" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-success mb-0">Kasbon berhasil <?= e($flash) ?>.</div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Member</dt>
                        <dd class="col-sm-7"><?= e($cashAdvance['member_name']) ?></dd>

                        <dt class="col-sm-5">Group / Store</dt>
                        <dd class="col-sm-7"><?= e($cashAdvance['group_name'] ?: '-') ?></dd>

                        <dt class="col-sm-5">Advance Date</dt>
                        <dd class="col-sm-7"><?= e($cashAdvance['advance_date']) ?></dd>

                        <dt class="col-sm-5">Amount</dt>
                        <dd class="col-sm-7">Rp <?= e(number_format((float) $cashAdvance['amount'], 0, ',', '.')) ?></dd>

                        <dt class="col-sm-5">Metode Transfer</dt>
                        <dd class="col-sm-7"><?= e($cashAdvance['transfer_method_name'] ?: '-') ?></dd>

                        <dt class="col-sm-5">Biaya Transfer</dt>
                        <dd class="col-sm-7">Rp <?= e(number_format((float) ($cashAdvance['transfer_fee_amount'] ?? 0), 0, ',', '.')) ?></dd>

                        <dt class="col-sm-5">Total Paid</dt>
                        <dd class="col-sm-7">Rp <?= e(number_format($totalPayments, 0, ',', '.')) ?></dd>

                        <dt class="col-sm-5">Remaining</dt>
                        <dd class="col-sm-7 fw-semibold">Rp <?= e(number_format((float) $cashAdvance['remaining_amount'], 0, ',', '.')) ?></dd>

                        <dt class="col-sm-5">Status</dt>
                        <dd class="col-sm-7">
                            <?php if ((int) $cashAdvance['status'] === 1): ?>
                                <span class="badge text-bg-success">Paid</span>
                            <?php else: ?>
                                <span class="badge text-bg-warning">Outstanding</span>
                            <?php endif; ?>
                        </dd>

                        <dt class="col-sm-5">Description</dt>
                        <dd class="col-sm-7"><?= e($cashAdvance['description'] ?: '-') ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Payment History</div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Method</th>
                                <th class="text-end">Amount</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($payments === []): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-4">Belum ada histori pembayaran.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td><?= e($payment['payment_date']) ?></td>
                                    <td><?= e($payment['reference_no'] ?: '-') ?></td>
                                    <td><?= e($payment['payment_method'] ?: '-') ?></td>
                                    <td class="text-end">Rp <?= e(number_format((float) $payment['amount'], 0, ',', '.')) ?></td>
                                    <td><?= e($payment['notes'] ?: '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
