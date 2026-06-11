<?php
$hasPrevious = $page > 1;
$hasNext = $page < $totalPages;
?>

<div class="d-flex flex-column gap-4">
    <div>
        <h1 class="h3 mb-1">Audit Trail</h1>
        <p class="text-secondary mb-0">Log aktivitas POST untuk semua modul aplikasi.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="get" action="<?= e(url('/audit-logs')) ?>" class="row g-3">
                <div class="col-12 col-md-3">
                    <label for="module" class="form-label">Module</label>
                    <input type="text" class="form-control" id="module" name="module" value="<?= e($filters['module']) ?>">
                </div>
                <div class="col-12 col-md-3">
                    <label for="event" class="form-label">Event</label>
                    <input type="text" class="form-control" id="event" name="event" value="<?= e($filters['event']) ?>">
                </div>
                <div class="col-12 col-md-2">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?= e($filters['date_from']) ?>">
                </div>
                <div class="col-12 col-md-2">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?= e($filters['date_to']) ?>">
                </div>
                <div class="col-12 col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-outline-primary flex-fill">Filter</button>
                    <a href="<?= e(url('/audit-logs')) ?>" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Event</th>
                        <th>Module</th>
                        <th>Path</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($logs === []): ?>
                        <tr><td colspan="6" class="text-center text-secondary py-4">Audit log belum ada.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= e($log['created_at']) ?></td>
                            <td>
                                <div class="fw-semibold"><?= e($log['user_name'] ?: 'Guest') ?></div>
                                <div class="small text-secondary"><?= e($log['user_role'] ?: '-') ?></div>
                            </td>
                            <td><?= e($log['event']) ?></td>
                            <td><?= e($log['module']) ?></td>
                            <td><span class="small"><?= e($log['method']) ?> <?= e($log['path']) ?></span></td>
                            <td><code class="small"><?= e($log['request_data'] ?: '{}') ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-body border-top d-flex flex-column flex-md-row justify-content-between gap-3">
            <div class="text-secondary small">Page <?= e($page) ?> of <?= e($totalPages) ?>, <?= e($totalRows) ?> rows</div>
            <div class="d-flex gap-2">
                <?php $previousQuery = http_build_query(array_merge($filters, ['page' => max(1, $page - 1)])); ?>
                <?php $nextQuery = http_build_query(array_merge($filters, ['page' => min($totalPages, $page + 1)])); ?>
                <a class="btn btn-sm btn-outline-secondary <?= $hasPrevious ? '' : 'disabled' ?>" href="<?= e(url('/audit-logs?' . $previousQuery)) ?>">Previous</a>
                <a class="btn btn-sm btn-outline-secondary <?= $hasNext ? '' : 'disabled' ?>" href="<?= e(url('/audit-logs?' . $nextQuery)) ?>">Next</a>
            </div>
        </div>
    </div>
</div>
