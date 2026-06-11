<div class="d-flex flex-column gap-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <h1 class="h3 mb-1">User Management</h1>
            <p class="text-secondary mb-0">Kelola akun login dan role aplikasi.</p>
        </div>
        <a href="<?= e(url('/users/create')) ?>" class="btn btn-primary">Create User</a>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-success mb-0">User berhasil <?= e($flash) ?>.</div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="fw-semibold"><?= e($user['name']) ?></td>
                            <td><?= e($user['username']) ?></td>
                            <td><?= e($user['role_name'] ?: $user['role']) ?></td>
                            <td>
                                <?php if ((int) $user['status'] === 1): ?>
                                    <span class="badge text-bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a href="<?= e(url('/users/' . $user['id'] . '/edit')) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <?php if ((int) $user['status'] === 1): ?>
                                        <form method="post" action="<?= e(url('/users/' . $user['id'] . '/deactivate')) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Deactivate</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="post" action="<?= e(url('/users/' . $user['id'] . '/activate')) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-success">Activate</button>
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
