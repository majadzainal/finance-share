<div class="d-flex flex-column gap-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <h1 class="h3 mb-1">Roles</h1>
            <p class="text-secondary mb-0">Kelola daftar role yang dapat dipilih pada user.</p>
        </div>
        <a href="<?= e(url('/roles/create')) ?>" class="btn btn-primary">Create Role</a>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-success mb-0">Role berhasil <?= e($flash) ?>.</div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $role): ?>
                        <tr>
                            <td class="fw-semibold"><?= e($role['code']) ?></td>
                            <td><?= e($role['name']) ?></td>
                            <td><?= e($role['description'] ?: '-') ?></td>
                            <td><?= (int) $role['is_active'] === 1 ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">Inactive</span>' ?></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a href="<?= e(url('/roles/' . $role['id'] . '/edit')) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form method="post" action="<?= e(url('/roles/' . $role['id'] . ((int) $role['is_active'] === 1 ? '/deactivate' : '/activate'))) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary"><?= (int) $role['is_active'] === 1 ? 'Deactivate' : 'Activate' ?></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
