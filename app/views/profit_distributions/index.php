<div class="d-flex flex-column gap-4">
    <div>
        <h1 class="h3 mb-1">Profit Distribution</h1>
        <p class="text-secondary mb-0">Daftar closing dan status pembayaran profit distribution.</p>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-success mb-0">Profit distribution berhasil <?= e($flash) ?>.</div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="get" action="<?= e(url('/profit-distribution')) ?>" class="row g-3">
                <div class="col-12 col-md-4">
                    <label for="group_id" class="form-label">Group / Store</label>
                    <select class="form-select" id="group_id" name="group_id">
                        <option value="0">All Groups / Store</option>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?= e($group['id']) ?>" <?= (int) $filters['group_id'] === (int) $group['id'] ? 'selected' : '' ?>>
                                <?= e($group['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label for="period_start" class="form-label">Period Start</label>
                    <input type="date" class="form-control" id="period_start" name="period_start" value="<?= e($filters['period_start']) ?>">
                </div>

                <div class="col-12 col-md-3">
                    <label for="period_end" class="form-label">Period End</label>
                    <input type="date" class="form-control" id="period_end" name="period_end" value="<?= e($filters['period_end']) ?>">
                </div>

                <div class="col-12 col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-outline-primary flex-fill">Filter</button>
                    <a href="<?= e(url('/profit-distribution')) ?>" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Group / Store</th>
                        <th>Period</th>
                        <th class="text-end">Income</th>
                        <th class="text-end">Expense</th>
                        <th class="text-end">Net Profit</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($closings === []): ?>
                        <tr>
                            <td colspan="8" class="text-center text-secondary py-4">Belum ada closing.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($closings as $closing): ?>
                        <tr>
                            <td><?= e($closing['group_name']) ?></td>
                            <td><?= e($closing['period_start']) ?> - <?= e($closing['period_end']) ?></td>
                            <td class="text-end">Rp <?= e(number_format((float) $closing['total_income'], 0, ',', '.')) ?></td>
                            <td class="text-end">Rp <?= e(number_format((float) $closing['total_expense'], 0, ',', '.')) ?></td>
                            <td class="text-end fw-semibold">Rp <?= e(number_format((float) $closing['net_profit'], 0, ',', '.')) ?></td>
                            <td><?= e((int) $closing['paid_count']) ?> / <?= e((int) $closing['distribution_count']) ?> paid</td>
                            <td><span class="badge text-bg-secondary"><?= e($closing['status']) ?></span></td>
                            <td class="text-end">
                                <a href="<?= e(url('/profit-distribution/' . $closing['id'])) ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
