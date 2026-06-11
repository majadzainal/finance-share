<div class="d-flex flex-column gap-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <h1 class="h3 mb-1">Distribution Detail</h1>
            <p class="text-secondary mb-0"><?= e($closing['group_name']) ?>, <?= e($closing['period_start']) ?> - <?= e($closing['period_end']) ?></p>
        </div>
        <div>
            <a href="<?= e(url('/profit-distribution')) ?>" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-success mb-0">Distribution berhasil <?= e($flash) ?>.</div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-secondary small mb-1">Total Income</div>
                    <div class="fs-5 fw-bold text-success">Rp <?= e(number_format((float) $closing['total_income'], 0, ',', '.')) ?></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-secondary small mb-1">Total Expense</div>
                    <div class="fs-5 fw-bold text-danger">Rp <?= e(number_format((float) $closing['total_expense'], 0, ',', '.')) ?></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-secondary small mb-1">Net Profit</div>
                    <div class="fs-5 fw-bold">Rp <?= e(number_format((float) $closing['net_profit'], 0, ',', '.')) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Member</th>
                        <th class="text-end">Share %</th>
                        <th class="text-end">Gross Share</th>
                        <th class="text-end">Cash Advance Cut</th>
                        <th class="text-end">Final Take Home Out</th>
                        <th>Payment Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($distributions === []): ?>
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-4">Belum ada distribution.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($distributions as $distribution): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= e($distribution['member_name']) ?></div>
                                <div class="small text-secondary"><?= e($distribution['nickname'] ?: '-') ?></div>
                            </td>
                            <td class="text-end"><?= e(number_format((float) $distribution['share_percent'], 3)) ?>%</td>
                            <td class="text-end">Rp <?= e(number_format((float) $distribution['gross_share_amount'], 0, ',', '.')) ?></td>
                            <td class="text-end">Rp <?= e(number_format((float) $distribution['cash_advance_cut'], 0, ',', '.')) ?></td>
                            <td class="text-end fw-semibold">Rp <?= e(number_format((float) $distribution['final_take_home_out'], 0, ',', '.')) ?></td>
                            <td>
                                <?php if ($distribution['payment_status'] === 'paid'): ?>
                                    <span class="badge text-bg-success">paid</span>
                                    <div class="small text-secondary"><?= e($distribution['paid_at']) ?></div>
                                <?php else: ?>
                                    <span class="badge text-bg-warning"><?= e($distribution['payment_status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($distribution['payment_status'] !== 'paid'): ?>
                                    <form method="post" action="<?= e(url('/profit-distribution/' . $distribution['id'] . '/paid')) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-success">Mark as Paid</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-secondary small">No action</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
