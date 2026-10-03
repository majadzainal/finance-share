<div class="d-flex flex-column gap-4">
    <div>
        <h1 class="h3 mb-1">Closing Preview</h1>
        <p class="text-secondary mb-0">Preview perhitungan closing tanpa menyimpan data ke database.</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger mb-0"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success mb-0"><?= e($success) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="post" action="<?= e(url('/closing/preview')) ?>" class="row g-3">
                <div class="col-12 col-md-4">
                    <label for="group_id" class="form-label">Group / Store</label>
                    <select class="form-select" id="group_id" name="group_id" required>
                        <option value="">Select group/store</option>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?= e($group['id']) ?>" <?= (int) $input['group_id'] === (int) $group['id'] ? 'selected' : '' ?>>
                                <?= e($group['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label for="period_start" class="form-label">Period Start</label>
                    <input type="date" class="form-control" id="period_start" name="period_start" value="<?= e($input['period_start']) ?>" required>
                </div>

                <div class="col-12 col-md-3">
                    <label for="period_end" class="form-label">Period End</label>
                    <input type="date" class="form-control" id="period_end" name="period_end" value="<?= e($input['period_end']) ?>" required>
                </div>

                <div class="col-12 col-md-3">
                    <label for="transfer_method_id" class="form-label">Metode Transfer</label>
                    <select class="form-select" id="transfer_method_id" name="transfer_method_id" data-transfer-method>
                        <option value="0" data-fee="0">Tanpa metode transfer</option>
                        <?php foreach ($transferMethods as $method): ?>
                            <option
                                value="<?= e($method['id']) ?>"
                                data-fee="<?= e($method['default_fee']) ?>"
                                <?= (int) ($input['transfer_method_id'] ?? 0) === (int) $method['id'] ? 'selected' : '' ?>
                            >
                                <?= e($method['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label for="transfer_fee_amount" class="form-label">Biaya Transfer</label>
                    <input type="number" step="0.01" min="0" class="form-control" id="transfer_fee_amount" name="transfer_fee_amount" value="<?= e($input['transfer_fee_amount'] ?? 0) ?>" data-transfer-fee>
                </div>

                <div class="col-12 col-md-4">
                    <label for="savings_amount" class="form-label">Penyisihan Tabungan Toko (Rp)</label>
                    <input type="number" step="0.01" min="0" class="form-control" id="savings_amount" name="savings_amount" value="<?= e($input['savings_amount'] ?? 0) ?>" placeholder="Disisihkan ke kas cadangan toko">
                    <div class="form-text small">Nominal yang disisihkan sebelum dibagi ke member.</div>
                </div>

                <div class="col-12 col-md-2 d-flex align-items-center mt-md-4">
                    <button type="submit" class="btn btn-primary w-100">Preview</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($preview): ?>
        <?php foreach ($preview['warnings'] as $warning): ?>
            <div class="alert alert-warning mb-0"><?= e($warning) ?></div>
        <?php endforeach; ?>

        <div class="row g-3">
            <div class="col-12 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-secondary small mb-1">Opening Balance</div>
                        <div class="fs-6 fw-bold">Rp <?= e(number_format($preview['opening_balance'], 0, ',', '.')) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-secondary small mb-1">Total Income</div>
                        <div class="fs-6 fw-bold text-success">Rp <?= e(number_format($preview['total_income'], 0, ',', '.')) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-secondary small mb-1">Total Expense</div>
                        <div class="fs-6 fw-bold text-danger">Rp <?= e(number_format($preview['total_expense'], 0, ',', '.')) ?></div>
                        <?php if (($preview['transfer_fee_amount'] ?? 0) > 0): ?>
                            <div class="small text-secondary">Fee: Rp <?= e(number_format($preview['transfer_fee_amount'], 0, ',', '.')) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-secondary small mb-1">Net Profit</div>
                        <div class="fs-6 fw-bold">Rp <?= e(number_format($preview['net_profit'], 0, ',', '.')) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100 bg-light">
                    <div class="card-body">
                        <div class="text-secondary small mb-1">Tabungan Toko</div>
                        <div class="fs-6 fw-bold text-info">Rp <?= e(number_format($preview['savings_amount'] ?? 0, 0, ',', '.')) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100 border-primary">
                    <div class="card-body">
                        <div class="text-primary small mb-1 fw-semibold">Laba Dibagikan</div>
                        <div class="fs-6 fw-bold text-primary">Rp <?= e(number_format($preview['distributable_profit'] ?? $preview['net_profit'], 0, ',', '.')) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex flex-column flex-md-row justify-content-between gap-2">
                <div>
                    <div class="fw-semibold"><?= e($preview['group']['name']) ?></div>
                    <div class="small text-secondary"><?= e($preview['period_start']) ?> sampai <?= e($preview['period_end']) ?> (Dasar Pembagian: Rp <?= e(number_format($preview['distributable_profit'] ?? $preview['net_profit'], 0, ',', '.')) ?>)</div>
                </div>
                <div>
                    <?php if ($preview['share_is_valid']): ?>
                        <span class="badge text-bg-success">Share <?= e(number_format($preview['share_total'], 3)) ?>%</span>
                    <?php else: ?>
                        <span class="badge text-bg-warning">Share <?= e(number_format($preview['share_total'], 3)) ?>%</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Member</th>
                            <th class="text-end">Share %</th>
                            <th class="text-end">Gross Share</th>
                            <th class="text-end">Outstanding Kasbon</th>
                            <th class="text-end">Cash Advance Cut</th>
                            <th class="text-end">Final Take Home Out</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($preview['members'] === []): ?>
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">Belum ada member aktif pada group ini.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($preview['members'] as $member): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= e($member['member_name']) ?></div>
                                    <div class="small text-secondary"><?= e($member['nickname'] ?: '-') ?></div>
                                </td>
                                <td class="text-end"><?= e(number_format($member['share_percent'], 3)) ?>%</td>
                                <td class="text-end">Rp <?= e(number_format($member['gross_share_amount'], 0, ',', '.')) ?></td>
                                <td class="text-end">Rp <?= e(number_format($member['outstanding_kasbon'], 0, ',', '.')) ?></td>
                                <td class="text-end">Rp <?= e(number_format($member['cash_advance_cut'], 0, ',', '.')) ?></td>
                                <td class="text-end fw-semibold">Rp <?= e(number_format($member['final_take_home_out'], 0, ',', '.')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($preview['share_is_valid']): ?>
            <form method="post" action="<?= e(url('/closing/finalize')) ?>" class="d-flex justify-content-end">
                <input type="hidden" name="group_id" value="<?= e($input['group_id']) ?>">
                <input type="hidden" name="period_start" value="<?= e($input['period_start']) ?>">
                <input type="hidden" name="period_end" value="<?= e($input['period_end']) ?>">
                <input type="hidden" name="transfer_method_id" value="<?= e($input['transfer_method_id'] ?? 0) ?>">
                <input type="hidden" name="transfer_fee_amount" value="<?= e($input['transfer_fee_amount'] ?? 0) ?>">
                <input type="hidden" name="savings_amount" value="<?= e($input['savings_amount'] ?? 0) ?>">
                <button type="submit" class="btn btn-danger">Finalize Closing</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
    document.querySelectorAll('[data-transfer-method]').forEach((select) => {
        select.addEventListener('change', () => {
            const form = select.closest('form');
            const feeInput = form ? form.querySelector('[data-transfer-fee]') : null;
            const option = select.options[select.selectedIndex];

            if (feeInput && option) {
                feeInput.value = option.dataset.fee || '0';
            }
        });
    });
</script>
