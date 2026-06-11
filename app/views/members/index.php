<div class="d-flex flex-column gap-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <h1 class="h3 mb-1">Members</h1>
            <p class="text-secondary mb-0">Kelola member yang terhubung ke group/store.</p>
        </div>
        <div>
            <a href="<?= e(url('/members/create')) ?>" class="btn btn-primary">Create Member</a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-success mb-0">
            Data member berhasil <?= e($flash) ?>.
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body border-bottom">
            <form method="get" action="<?= e(url('/members')) ?>" class="row g-2">
                <div class="col-12 col-md-9">
                    <input
                        type="search"
                        name="search"
                        class="form-control"
                        value="<?= e($search) ?>"
                        placeholder="Search by full name or nickname"
                    >
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary flex-fill">Search</button>
                    <a href="<?= e(url('/members')) ?>" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Full Name</th>
                        <th>Nickname</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($members === []): ?>
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">Data member tidak ditemukan.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td class="fw-semibold"><?= e($member['full_name']) ?></td>
                            <td><?= e($member['nickname'] ?? '-') ?></td>
                            <td><?= e($member['phone'] ?: '-') ?></td>
                            <td>
                                <?php if ((int) $member['is_active'] === 1): ?>
                                    <span class="badge text-bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">Non-active</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a href="<?= e(url('/members/' . $member['id'] . '/edit')) ?>" class="btn btn-sm btn-outline-primary">Edit</a>

                                    <?php if ((int) $member['is_active'] === 1): ?>
                                        <form method="post" action="<?= e(url('/members/' . $member['id'] . '/deactivate')) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-warning">Non-active</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="post" action="<?= e(url('/members/' . $member['id'] . '/activate')) ?>">
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
