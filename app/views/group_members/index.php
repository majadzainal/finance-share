<div class="d-flex flex-column gap-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <h1 class="h3 mb-1">Group Members</h1>
            <p class="text-secondary mb-0">Kelola member dan persentase share per group/store.</p>
        </div>
        <div>
            <a href="<?= e(url('/group-members/create')) ?>" class="btn btn-primary">Add Member</a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-success mb-0">
            Data group member berhasil <?= e($flash) ?>.
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger mb-0">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <?php foreach ($totals as $total): ?>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-secondary small mb-1"><?= e($total['group_name']) ?></div>
                        <div class="d-flex align-items-end justify-content-between gap-3">
                            <div class="fs-4 fw-bold"><?= e(number_format((float) $total['total_share_percent'], 3)) ?>%</div>
                            <span class="badge <?= (float) $total['total_share_percent'] > 100 ? 'text-bg-danger' : 'text-bg-primary' ?>">Total Share</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body border-bottom">
            <form method="get" action="<?= e(url('/group-members')) ?>" class="row g-2">
                <div class="col-12 col-md-9">
                    <select name="group_id" class="form-select">
                        <option value="0">All Groups / Store</option>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?= e($group['id']) ?>" <?= (int) $selectedGroupId === (int) $group['id'] ? 'selected' : '' ?>>
                                <?= e($group['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary flex-fill">Filter</button>
                    <a href="<?= e(url('/group-members')) ?>" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Group / Store</th>
                        <th>Member</th>
                        <th>Nickname</th>
                        <th class="text-end">Share Percent</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($groupMembers === []): ?>
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">Belum ada member pada group/store ini.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($groupMembers as $item): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= e($item['group_name']) ?></div>
                                <div class="small text-secondary"><?= e($item['group_code']) ?></div>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= e($item['member_name']) ?></div>
                                <div class="small text-secondary"><?= e($item['member_code']) ?></div>
                            </td>
                            <td><?= e($item['nickname'] ?: '-') ?></td>
                            <td class="text-end fw-semibold"><?= e(number_format((float) $item['share_percent'], 3)) ?>%</td>
                            <td>
                                <?php if ((int) $item['is_active'] === 1): ?>
                                    <span class="badge text-bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">Non-active</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a href="<?= e(url('/group-members/' . $item['id'] . '/edit')) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form method="post" action="<?= e(url('/group-members/' . $item['id'] . '/delete')) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
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
