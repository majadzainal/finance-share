<div class="d-flex flex-column gap-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <h1 class="h3 mb-1">Groups / Store</h1>
            <p class="text-secondary mb-0">Kelola master group atau store yang dipakai di transaksi.</p>
        </div>
        <div>
            <a href="<?= e(url('/groups/create')) ?>" class="btn btn-primary">Create Group</a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-success mb-0">
            Data group berhasil <?= e($flash) ?>.
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($groups === []): ?>
                        <tr>
                            <td colspan="4" class="text-center text-secondary py-4">Belum ada data group.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($groups as $group): ?>
                        <tr>
                            <td class="fw-semibold"><?= e($group['code']) ?></td>
                            <td><?= e($group['name']) ?></td>
                            <td>
                                <?php if ((int) $group['is_active'] === 1): ?>
                                    <span class="badge text-bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">Non-active</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a href="<?= e(url('/groups/' . $group['id'])) ?>" class="btn btn-sm btn-outline-secondary">Show</a>
                                    <a href="<?= e(url('/groups/' . $group['id'] . '/edit')) ?>" class="btn btn-sm btn-outline-primary">Edit</a>

                                    <?php if ((int) $group['is_active'] === 1): ?>
                                        <form method="post" action="<?= e(url('/groups/' . $group['id'] . '/deactivate')) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-warning">Non-active</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="post" action="<?= e(url('/groups/' . $group['id'] . '/activate')) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-success">Active</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
