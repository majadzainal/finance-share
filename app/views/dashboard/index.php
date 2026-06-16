<?php
$formatMoney = static fn (float $amount): string => 'Rp ' . number_format($amount, 0, ',', '.');
$expenseRatio = $summary['total_income'] > 0 ? min(100, ($summary['total_expense'] / $summary['total_income']) * 100) : 0;
$cashAdvanceRatio = $summary['total_income'] > 0 ? min(100, ($summary['outstanding_cash_advance'] / $summary['total_income']) * 100) : 0;
$balanceDifference = abs($accountHealth['balance_difference']);
$periodLabels = [
    'today' => 'Hari ini',
    'month' => 'Bulan ini',
    'year' => 'Tahun ini',
    'all' => 'Semua data',
    'custom' => 'Custom',
];
?>

<style>
    .dashboard-hero {
        background: linear-gradient(135deg, #ffffff 0%, #eef7f3 48%, #f7f2ea 100%);
        border: 1px solid rgba(24, 33, 47, .08);
    }

    .dashboard-card {
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .dashboard-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 .75rem 1.75rem rgba(24, 33, 47, .1) !important;
    }

    .shortcut-card {
        border-left: 4px solid var(--shortcut-color);
    }

    .activity-dot {
        width: .65rem;
        height: .65rem;
    }
</style>

<div class="d-flex flex-column gap-4">
    <div class="dashboard-hero rounded-3 p-3 p-md-4">
        <div class="d-flex flex-column flex-xl-row justify-content-between gap-4">
            <div class="min-w-0">
                <div class="text-uppercase small fw-semibold text-secondary mb-2">Finance Control Center</div>
                <h1 class="h3 mb-2">Dashboard</h1>
                <p class="text-secondary mb-0">Pantau cash position, kasbon, closing, dan jalur kerja utama dari satu halaman. KPI aktif: <?= e($periodLabels[$filters['period']] ?? 'Bulan ini') ?>.</p>
            </div>
            <div class="d-flex flex-column flex-sm-row gap-2 align-items-stretch align-items-sm-center">
                <a href="<?= e(url('/import-income')) ?>" class="btn btn-success">Import Income</a>
                <a href="<?= e(url('/expenses/create')) ?>" class="btn btn-outline-danger">Input Expense</a>
                <a href="<?= e(url('/closing')) ?>" class="btn btn-outline-dark">Preview Closing</a>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="get" action="<?= e(url('/dashboard')) ?>" class="row g-3 align-items-end">
                <div class="col-12 col-lg-4">
                    <label class="form-label">Quick Date Filter</label>
                    <div class="btn-group w-100" role="group" aria-label="Dashboard period filter">
                        <?php foreach (['today' => 'Hari Ini', 'month' => 'Bulan Ini', 'year' => 'Tahun Ini', 'all' => 'All'] as $period => $label): ?>
                            <a
                                href="<?= e(url('/dashboard?period=' . $period)) ?>"
                                class="btn btn-sm <?= $filters['period'] === $period ? 'btn-primary' : 'btn-outline-primary' ?>"
                            >
                                <?= e($label) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-12 col-md-3 col-lg-2">
                    <label for="date_from" class="form-label">Dari</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?= e($filters['date_from']) ?>">
                </div>
                <div class="col-12 col-md-3 col-lg-2">
                    <label for="date_to" class="form-label">Sampai</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?= e($filters['date_to']) ?>">
                </div>
                <div class="col-12 col-md-3 col-lg-2">
                    <input type="hidden" name="period" value="custom">
                    <button type="submit" class="btn btn-outline-secondary w-100">Apply Custom</button>
                </div>
                <div class="col-12 col-lg-2">
                    <div class="small text-secondary">Filter ini memengaruhi KPI dan aktivitas terbaru.</div>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3">
        <?php foreach ($summaryCards as $card): ?>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 dashboard-card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div class="min-w-0">
                                <div class="text-secondary small mb-1"><?= e($card['label']) ?></div>
                                <div class="fs-4 fw-bold text-<?= e($card['tone']) ?>"><?= e($formatMoney((float) $card['value'])) ?></div>
                                <div class="small text-secondary mt-2"><?= e($card['hint']) ?></div>
                            </div>
                            <span class="badge text-bg-<?= e($card['tone']) ?> rounded-pill">&nbsp;</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                <div>
                    <h2 class="h5 mb-1">Saldo Per Group</h2>
                    <div class="text-secondary small">Saldo cash dihitung dari income, expense approved, dan kasbon yang sudah disalurkan.</div>
                </div>
                <a href="<?= e(url('/ledger-report')) ?>" class="btn btn-sm btn-outline-secondary align-self-start">Ledger</a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Group</th>
                            <th class="text-end">Income</th>
                            <th class="text-end">Expense</th>
                            <th class="text-end">Kasbon Disalurkan</th>
                            <th class="text-end">Outstanding Kasbon</th>
                            <th class="text-end">Distribusi Pending</th>
                            <th class="text-end">Saldo Cash</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($groupBalances === []): ?>
                            <tr>
                                <td colspan="7" class="text-center text-secondary py-4">Belum ada group aktif.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($groupBalances as $groupBalance): ?>
                            <?php $cashBalance = (float) $groupBalance['cash_balance']; ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= e($groupBalance['name']) ?></div>
                                    <div class="small text-secondary"><?= e($groupBalance['code']) ?></div>
                                </td>
                                <td class="text-end"><?= e($formatMoney((float) $groupBalance['total_income'])) ?></td>
                                <td class="text-end"><?= e($formatMoney((float) $groupBalance['total_expense'])) ?></td>
                                <td class="text-end"><?= e($formatMoney((float) $groupBalance['cash_advance_disbursed'])) ?></td>
                                <td class="text-end"><?= e($formatMoney((float) $groupBalance['outstanding_cash_advance'])) ?></td>
                                <td class="text-end"><?= e($formatMoney((float) $groupBalance['pending_distribution'])) ?></td>
                                <td class="text-end fw-bold <?= $cashBalance < 0 ? 'text-danger' : 'text-success' ?>"><?= e($formatMoney($cashBalance)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <h2 class="h5 mb-1">Action Needed</h2>
                            <div class="text-secondary small">Hal yang perlu dicek sebelum lanjut operasional.</div>
                        </div>
                        <span class="badge text-bg-<?= $actionNeeded === [] ? 'success' : 'warning' ?>"><?= e(count($actionNeeded)) ?> item</span>
                    </div>

                    <?php if ($actionNeeded === []): ?>
                        <div class="alert alert-success mb-0">Tidak ada perhatian khusus saat ini.</div>
                    <?php endif; ?>

                    <?php if ($actionNeeded !== []): ?>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($actionNeeded as $action): ?>
                                <a href="<?= e($action['url']) ?>" class="border rounded-2 p-3 text-decoration-none text-dark dashboard-card">
                                    <div class="d-flex justify-content-between gap-3">
                                        <div>
                                            <div class="fw-semibold"><?= e($action['label']) ?></div>
                                            <div class="small text-secondary"><?= e($action['description']) ?></div>
                                        </div>
                                        <span class="badge text-bg-<?= e($action['tone']) ?> align-self-start">Cek</span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <h2 class="h5 mb-1">Kasbon Aging</h2>
                            <div class="text-secondary small">Kasbon outstanding terlama.</div>
                        </div>
                        <a href="<?= e(url('/cash-advances')) ?>" class="btn btn-sm btn-outline-warning">Kasbon</a>
                    </div>

                    <?php if ($cashAdvanceAging === []): ?>
                        <div class="alert alert-success mb-0">Tidak ada kasbon outstanding.</div>
                    <?php endif; ?>

                    <?php if ($cashAdvanceAging !== []): ?>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($cashAdvanceAging as $cashAdvance): ?>
                                <a href="<?= e(url('/cash-advances/' . $cashAdvance['id'])) ?>" class="border rounded-2 p-3 text-decoration-none text-dark dashboard-card">
                                    <div class="d-flex justify-content-between gap-3">
                                        <div class="min-w-0">
                                            <div class="fw-semibold text-truncate"><?= e($cashAdvance['member_name']) ?></div>
                                            <div class="small text-secondary text-truncate"><?= e($cashAdvance['description'] ?: ($cashAdvance['nickname'] ?: '-')) ?></div>
                                        </div>
                                        <div class="text-end flex-shrink-0">
                                            <div class="fw-semibold"><?= e($formatMoney((float) $cashAdvance['remaining_amount'])) ?></div>
                                            <div class="small text-secondary"><?= e((int) $cashAdvance['age_days']) ?> hari</div>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
                        <div>
                            <h2 class="h5 mb-1">Cash Health</h2>
                            <div class="text-secondary small">Validasi saldo rekening terhadap income, expense, dan kasbon.</div>
                        </div>
                        <?php if ($accountHealth['is_balanced']): ?>
                            <span class="badge text-bg-success align-self-start">Balance</span>
                        <?php else: ?>
                            <span class="badge text-bg-danger align-self-start">Tidak Balance</span>
                        <?php endif; ?>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <div class="border rounded-2 p-3 h-100">
                                <div class="small text-secondary mb-1">Saldo Rekening Manual</div>
                                <div class="fs-5 fw-bold"><?= e($formatMoney($accountHealth['manual_balance'])) ?></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="border rounded-2 p-3 h-100">
                                <div class="small text-secondary mb-1">Selisih</div>
                                <div class="fs-5 fw-bold <?= $accountHealth['is_balanced'] ? 'text-success' : 'text-danger' ?>"><?= e($formatMoney($balanceDifference)) ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Expense terhadap income</span>
                                <span><?= e(number_format($expenseRatio, 1, ',', '.')) ?>%</span>
                            </div>
                            <div class="progress" role="progressbar" aria-label="Expense ratio" aria-valuenow="<?= e((int) $expenseRatio) ?>" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar bg-danger" style="width: <?= e($expenseRatio) ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Outstanding kasbon terhadap income</span>
                                <span><?= e(number_format($cashAdvanceRatio, 1, ',', '.')) ?>%</span>
                            </div>
                            <div class="progress" role="progressbar" aria-label="Cash advance ratio" aria-valuenow="<?= e((int) $cashAdvanceRatio) ?>" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar bg-warning" style="width: <?= e($cashAdvanceRatio) ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="<?= e(url('/account-balances')) ?>" class="btn btn-sm btn-outline-primary">Review Saldo Rekening</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                        <div>
                            <h2 class="h5 mb-1">Shortcut Menu</h2>
                            <div class="text-secondary small">Cari dan buka aksi yang paling sering dipakai.</div>
                        </div>
                        <input type="search" class="form-control form-control-sm" style="max-width: 220px;" placeholder="Cari shortcut" data-shortcut-search>
                    </div>

                    <div class="row g-2" data-shortcut-list>
                        <?php foreach ($shortcuts as $shortcut): ?>
                            <div class="col-12 col-md-6" data-shortcut-item="<?= e(strtolower($shortcut['label'] . ' ' . $shortcut['description'])) ?>">
                                <a
                                    href="<?= e($shortcut['url']) ?>"
                                    class="card shortcut-card dashboard-card border-0 shadow-sm h-100 text-decoration-none text-dark"
                                    style="--shortcut-color: var(--bs-<?= e($shortcut['tone']) ?>);"
                                >
                                    <div class="card-body py-3">
                                        <div class="fw-semibold"><?= e($shortcut['label']) ?></div>
                                        <div class="small text-secondary"><?= e($shortcut['description']) ?></div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">Operasional</h2>
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex justify-content-between gap-3">
                            <span class="text-secondary">Group aktif</span>
                            <span class="fw-semibold"><?= e($summary['active_groups']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between gap-3">
                            <span class="text-secondary">Member aktif</span>
                            <span class="fw-semibold"><?= e($summary['active_members']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between gap-3">
                            <span class="text-secondary">Closing draft/closed</span>
                            <span class="fw-semibold"><?= e($summary['open_closings']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between gap-3">
                            <span class="text-secondary">Distribusi pending</span>
                            <span class="fw-semibold"><?= e($summary['pending_distributions']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between gap-3">
                            <span class="text-secondary">Expense draft</span>
                            <span class="fw-semibold"><?= e($summary['draft_expenses'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <h2 class="h5 mb-1">Aktivitas Terbaru</h2>
                            <div class="text-secondary small">Income, expense, dan kasbon terakhir.</div>
                        </div>
                        <a href="<?= e(url('/ledger-report')) ?>" class="btn btn-sm btn-outline-secondary">Ledger</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Tipe</th>
                                    <th>Deskripsi</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recentActivity === []): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-secondary py-4">Belum ada aktivitas transaksi.</td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach ($recentActivity as $activity): ?>
                                    <?php
                                    $typeTone = match ($activity['type']) {
                                        'Income' => 'success',
                                        'Expense' => 'danger',
                                        default => 'warning',
                                    };
                                    ?>
                                    <tr>
                                        <td><?= e($activity['activity_date']) ?></td>
                                        <td>
                                            <span class="d-inline-flex align-items-center gap-2">
                                                <span class="activity-dot rounded-circle bg-<?= e($typeTone) ?>"></span>
                                                <?= e($activity['type']) ?>
                                            </span>
                                        </td>
                                        <td class="text-truncate" style="max-width: 320px;"><?= e($activity['title']) ?></td>
                                        <td class="text-end fw-semibold"><?= e($formatMoney((float) $activity['amount'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('[data-shortcut-search]').forEach((input) => {
        input.addEventListener('input', () => {
            const keyword = input.value.trim().toLowerCase();
            document.querySelectorAll('[data-shortcut-item]').forEach((item) => {
                item.classList.toggle('d-none', keyword !== '' && !item.dataset.shortcutItem.includes(keyword));
            });
        });
    });
</script>
