<div class="d-flex flex-column gap-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <h1 class="h3 mb-1">Group Detail</h1>
            <p class="text-secondary mb-0">Informasi master group/store.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= e(url('/groups')) ?>" class="btn btn-outline-secondary">Back</a>
            <a href="<?= e(url('/groups/' . $group['id'] . '/edit')) ?>" class="btn btn-primary">Edit</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Code</dt>
                <dd class="col-sm-9 fw-semibold"><?= e($group['code']) ?></dd>

                <dt class="col-sm-3">Name</dt>
                <dd class="col-sm-9"><?= e($group['name']) ?></dd>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">
                    <?php if ((int) $group['is_active'] === 1): ?>
                        <span class="badge text-bg-success">Active</span>
                    <?php else: ?>
                        <span class="badge text-bg-secondary">Non-active</span>
                    <?php endif; ?>
                </dd>

                <dt class="col-sm-3">Created</dt>
                <dd class="col-sm-9"><?= e($group['created_at']) ?></dd>

                <dt class="col-sm-3">Updated</dt>
                <dd class="col-sm-9"><?= e($group['updated_at']) ?></dd>
            </dl>
        </div>
    </div>
</div>
