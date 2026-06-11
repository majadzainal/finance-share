<?php if (isset($errors['general'])): ?>
    <div class="alert alert-danger"><?= e($errors['general']) ?></div>
<?php endif; ?>

<form method="post" action="<?= e($action) ?>" class="row g-3">
    <div class="col-12 col-md-6">
        <label for="group_id" class="form-label">Group / Store</label>
        <?php if ($isEdit): ?>
            <input type="text" class="form-control" value="<?= e($groupMember['group_name']) ?>" disabled>
            <input type="hidden" name="group_id" value="<?= e($groupMember['group_id']) ?>">
        <?php else: ?>
            <select class="form-select <?= isset($errors['group_id']) ? 'is-invalid' : '' ?>" id="group_id" name="group_id" required>
                <option value="">Select group/store</option>
                <?php foreach ($groups as $group): ?>
                    <option value="<?= e($group['id']) ?>" <?= (int) ($groupMember['group_id'] ?? 0) === (int) $group['id'] ? 'selected' : '' ?>>
                        <?= e($group['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['group_id'])): ?>
                <div class="invalid-feedback"><?= e($errors['group_id']) ?></div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="col-12 col-md-6">
        <label for="member_id" class="form-label">Member</label>
        <?php if ($isEdit): ?>
            <input type="text" class="form-control" value="<?= e($groupMember['member_name']) ?>" disabled>
            <input type="hidden" name="member_id" value="<?= e($groupMember['member_id']) ?>">
        <?php else: ?>
            <select class="form-select <?= isset($errors['member_id']) ? 'is-invalid' : '' ?>" id="member_id" name="member_id" required>
                <option value="">Select member</option>
                <?php foreach ($members as $member): ?>
                    <option value="<?= e($member['id']) ?>" <?= (int) ($groupMember['member_id'] ?? 0) === (int) $member['id'] ? 'selected' : '' ?>>
                        <?= e($member['full_name']) ?><?= $member['nickname'] ? ' (' . e($member['nickname']) . ')' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['member_id'])): ?>
                <div class="invalid-feedback"><?= e($errors['member_id']) ?></div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="col-12 col-md-4">
        <label for="share_percent" class="form-label">Share Percent</label>
        <div class="input-group">
            <input
                type="number"
                step="0.001"
                min="0.001"
                max="100"
                class="form-control <?= isset($errors['share_percent']) ? 'is-invalid' : '' ?>"
                id="share_percent"
                name="share_percent"
                value="<?= e($groupMember['share_percent'] ?? '') ?>"
                required
            >
            <span class="input-group-text">%</span>
            <?php if (isset($errors['share_percent'])): ?>
                <div class="invalid-feedback"><?= e($errors['share_percent']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-12">
        <div class="border rounded p-3 bg-light">
            <div class="fw-semibold mb-2">Total share per group</div>
            <div class="row g-2">
                <?php foreach ($totals as $total): ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="d-flex justify-content-between gap-2 small">
                            <span class="text-secondary"><?= e($total['group_name']) ?></span>
                            <span class="fw-semibold"><?= e(number_format((float) $total['total_share_percent'], 3)) ?>%</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="col-12 d-flex justify-content-end gap-2">
        <a href="<?= e(url('/group-members')) ?>" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><?= e($submitLabel) ?></button>
    </div>
</form>
