<?php
$queryWithoutPage = $filters;
$hasPrevious = $page > 1;
$hasNext = $page < $totalPages;
$sortLink = static function (string $column) use ($filters, $sort): string {
    $nextDir = ($sort['by'] === $column && $sort['dir'] === 'asc') ? 'desc' : 'asc';
    return url('/expenses?' . http_build_query(array_merge($filters, [
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
            <h1 class="h3 mb-1">Expenses</h1>
            <p class="text-secondary mb-0">Kelola pengeluaran operasional per group/store.</p>
        </div>
        <div>
            <a href="<?= e(url('/expenses/create')) ?>" class="btn btn-primary">Create Expense</a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-success mb-0">Expense berhasil <?= e($flash) ?>.</div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger mb-0"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="get" action="<?= e(url('/expenses')) ?>" class="row g-3">
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

                <div class="col-12 col-md-2">
                    <label for="date_from" class="form-label">Tanggal Awal</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?= e($filters['date_from']) ?>">
                </div>

                <div class="col-12 col-md-2">
                    <label for="date_to" class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?= e($filters['date_to']) ?>">
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
                    <a href="<?= e(url('/expenses')) ?>" class="btn btn-outline-secondary">Reset</a>
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
                        <th><a class="text-decoration-none text-dark" href="<?= e($sortLink('expense_date')) ?>">Date<?= e($sortLabel('expense_date')) ?></a></th>
                        <th><a class="text-decoration-none text-dark" href="<?= e($sortLink('group_name')) ?>">Group<?= e($sortLabel('group_name')) ?></a></th>
                        <th><a class="text-decoration-none text-dark" href="<?= e($sortLink('category_name')) ?>">Category<?= e($sortLabel('category_name')) ?></a></th>
                        <th><a class="text-decoration-none text-dark" href="<?= e($sortLink('description')) ?>">Description<?= e($sortLabel('description')) ?></a></th>
                        <th><a class="text-decoration-none text-dark" href="<?= e($sortLink('created_by')) ?>">Created By<?= e($sortLabel('created_by')) ?></a></th>
                        <th><a class="text-decoration-none text-dark" href="<?= e($sortLink('transfer_method')) ?>">Transfer<?= e($sortLabel('transfer_method')) ?></a></th>
                        <th class="text-end"><a class="text-decoration-none text-dark" href="<?= e($sortLink('amount')) ?>">Amount<?= e($sortLabel('amount')) ?></a></th>
                        <th><a class="text-decoration-none text-dark" href="<?= e($sortLink('status')) ?>">Status<?= e($sortLabel('status')) ?></a></th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($expenses === []): ?>
                        <tr>
                            <td colspan="10" class="text-center text-secondary py-4">Data expense tidak ditemukan.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($expenses as $index => $expense): ?>
                        <tr>
                            <td><?= e($rowStart + $index) ?></td>
                            <td><?= e($expense['expense_date']) ?></td>
                            <td><?= e($expense['group_name']) ?></td>
                            <td><?= e($expense['category_name']) ?></td>
                            <td><?= e($expense['description']) ?></td>
                            <td><?= e($expense['created_by'] ?: '-') ?></td>
                            <td>
                                <div><?= e($expense['transfer_method_name'] ?: '-') ?></div>
                                <?php if ((float) ($expense['transfer_fee_amount'] ?? 0) > 0): ?>
                                    <div class="small text-secondary">Fee Rp <?= e(number_format((float) $expense['transfer_fee_amount'], 0, ',', '.')) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end fw-semibold">Rp <?= e(number_format((float) $expense['amount'], 0, ',', '.')) ?></td>
                            <td>
                                <?php if ($expense['closing_id'] !== null): ?>
                                    <span class="badge text-bg-secondary">Locked</span>
                                <?php else: ?>
                                    <span class="badge text-bg-success">Open</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($expense['closing_id'] === null): ?>
                                    <div class="d-inline-flex gap-2">
                                        <a href="<?= e(url('/expenses/' . $expense['id'] . '/edit')) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form method="post" action="<?= e(url('/expenses/' . $expense['id'] . '/delete')) ?>">
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
                <tfoot class="table-light">
                    <tr>
                        <th colspan="7">Total Expense</th>
                        <th class="text-end">Rp <?= e(number_format($totalExpense, 0, ',', '.')) ?></th>
                        <th colspan="2"></th>
                    </tr>
                </tfoot>
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
                <a class="btn btn-sm btn-outline-secondary <?= $hasPrevious && $perPage !== 'all' ? '' : 'disabled' ?>" href="<?= e(url('/expenses?' . $previousQuery)) ?>">Previous</a>
                <a class="btn btn-sm btn-outline-secondary <?= $hasNext && $perPage !== 'all' ? '' : 'disabled' ?>" href="<?= e(url('/expenses?' . $nextQuery)) ?>">Next</a>
            </div>
        </div>
    </div>
</div>
