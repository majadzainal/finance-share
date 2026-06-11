<?php
$queryWithoutPage = $filters;
$hasPrevious = $page > 1;
$hasNext = $page < $totalPages;
$sortLink = static function (string $column) use ($filters, $sort): string {
    $nextDir = ($sort['by'] === $column && $sort['dir'] === 'asc') ? 'desc' : 'asc';
    return url('/income?' . http_build_query(array_merge($filters, [
        'sort_by' => $column,
        'sort_dir' => $nextDir,
        'page' => 1,
    ])));
};
$sortLabel = static function (string $column) use ($sort): string {
    if ($sort['by'] !== $column) {
        return '';
    }

    return $sort['dir'] === 'asc' ? ' ▲' : ' ▼';
};
?>

<div class="d-flex flex-column gap-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <h1 class="h3 mb-1">Income</h1>
            <p class="text-secondary mb-0">Daftar transaksi income hasil import atau input manual.</p>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body py-2">
                <div class="small text-secondary">Total Amount</div>
                <div class="fs-5 fw-bold">Rp <?= e(number_format($totalAmount, 0, ',', '.')) ?></div>
            </div>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-success mb-0">Income berhasil <?= e($flash) ?>.</div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger mb-0"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="get" action="<?= e(url('/income')) ?>" class="row g-3">
                <div class="col-12 col-md-3">
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

                <div class="col-12 col-md-2">
                    <label for="date_from" class="form-label">Tanggal Awal</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?= e($filters['date_from']) ?>">
                </div>

                <div class="col-12 col-md-2">
                    <label for="date_to" class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?= e($filters['date_to']) ?>">
                </div>

                <div class="col-12 col-md-3">
                    <label for="search" class="form-label">Username / Client</label>
                    <input type="search" class="form-control" id="search" name="search" value="<?= e($filters['search']) ?>" placeholder="Username atau client name">
                </div>

                <div class="col-12 col-md-2">
                    <label for="per_page" class="form-label">Show</label>
                    <select class="form-select" id="per_page" name="per_page">
                        <?php foreach ([10, 20, 50] as $option): ?>
                            <option value="<?= e($option) ?>" <?= (string) $perPage === (string) $option ? 'selected' : '' ?>><?= e($option) ?></option>
                        <?php endforeach; ?>
                        <option value="all" <?= $perPage === 'all' ? 'selected' : '' ?>>All</option>
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-outline-primary flex-fill">Filter</button>
                    <a href="<?= e(url('/income')) ?>" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No.</th>
                        <th><a class="text-decoration-none text-dark" href="<?= e($sortLink('transaction_date')) ?>">Date<?= e($sortLabel('transaction_date')) ?></a></th>
                        <th><a class="text-decoration-none text-dark" href="<?= e($sortLink('group_name')) ?>">Group<?= e($sortLabel('group_name')) ?></a></th>
                        <th><a class="text-decoration-none text-dark" href="<?= e($sortLink('reference_no')) ?>">Reference<?= e($sortLabel('reference_no')) ?></a></th>
                        <th><a class="text-decoration-none text-dark" href="<?= e($sortLink('client_name')) ?>">Client<?= e($sortLabel('client_name')) ?></a></th>
                        <th><a class="text-decoration-none text-dark" href="<?= e($sortLink('username')) ?>">Username<?= e($sortLabel('username')) ?></a></th>
                        <th><a class="text-decoration-none text-dark" href="<?= e($sortLink('payment_method')) ?>">Payment<?= e($sortLabel('payment_method')) ?></a></th>
                        <th class="text-end"><a class="text-decoration-none text-dark" href="<?= e($sortLink('amount')) ?>">Amount<?= e($sortLabel('amount')) ?></a></th>
                        <th><a class="text-decoration-none text-dark" href="<?= e($sortLink('status')) ?>">Status<?= e($sortLabel('status')) ?></a></th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($incomes === []): ?>
                        <tr>
                            <td colspan="10" class="text-center text-secondary py-4">Data income tidak ditemukan.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($incomes as $index => $income): ?>
                        <tr>
                            <td><?= e($rowStart + $index) ?></td>
                            <td><?= e($income['transaction_date']) ?></td>
                            <td><?= e($income['group_name']) ?></td>
                            <td class="fw-semibold"><?= e($income['reference_no'] ?: '-') ?></td>
                            <td><?= e($income['client_name'] ?: '-') ?></td>
                            <td><?= e($income['username'] ?: '-') ?></td>
                            <td>
                                <div><?= e($income['payment_method'] ?: '-') ?></div>
                                <div class="small text-secondary"><?= e($income['bank_target'] ?: '-') ?></div>
                            </td>
                            <td class="text-end fw-semibold">Rp <?= e(number_format((float) $income['amount'], 0, ',', '.')) ?></td>
                            <td>
                                <?php if ($income['closing_id'] !== null): ?>
                                    <span class="badge text-bg-secondary">Locked</span>
                                <?php else: ?>
                                    <span class="badge text-bg-success">Open</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($income['closing_id'] === null): ?>
                                    <div class="d-inline-flex gap-2">
                                        <a href="<?= e(url('/income/' . $income['id'] . '/edit')) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form method="post" action="<?= e(url('/income/' . $income['id'] . '/delete')) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="text-secondary small">No action</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card-body border-top d-flex flex-column flex-md-row justify-content-between gap-3">
            <div class="text-secondary small">
                <?php if ($perPage === 'all'): ?>
                    Showing all <?= e($totalRows) ?> rows
                <?php else: ?>
                    Page <?= e($page) ?> of <?= e($totalPages) ?>, showing <?= e($perPage) ?> per page from <?= e($totalRows) ?> rows
                <?php endif; ?>
            </div>
            <div class="d-flex gap-2">
                <?php $previousQuery = http_build_query(array_merge($queryWithoutPage, ['page' => max(1, $page - 1)])); ?>
                <?php $nextQuery = http_build_query(array_merge($queryWithoutPage, ['page' => min($totalPages, $page + 1)])); ?>
                <a class="btn btn-sm btn-outline-secondary <?= $hasPrevious && $perPage !== 'all' ? '' : 'disabled' ?>" href="<?= e(url('/income?' . $previousQuery)) ?>">Previous</a>
                <a class="btn btn-sm btn-outline-secondary <?= $hasNext && $perPage !== 'all' ? '' : 'disabled' ?>" href="<?= e(url('/income?' . $nextQuery)) ?>">Next</a>
            </div>
        </div>
    </div>
</div>
