<form method="post" action="<?= e($action) ?>" class="row g-3">
    <div class="col-12 col-md-6">
        <label for="full_name" class="form-label">Full Name</label>
        <input
            type="text"
            class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>"
            id="full_name"
            name="full_name"
            value="<?= e($member['full_name'] ?? '') ?>"
            maxlength="150"
            required
        >
        <?php if (isset($errors['full_name'])): ?>
            <div class="invalid-feedback"><?= e($errors['full_name']) ?></div>
        <?php endif; ?>
    </div>

    <div class="col-12 col-md-6">
        <label for="nickname" class="form-label">Nickname</label>
        <input
            type="text"
            class="form-control <?= isset($errors['nickname']) ? 'is-invalid' : '' ?>"
            id="nickname"
            name="nickname"
            value="<?= e($member['nickname'] ?? '') ?>"
            maxlength="100"
            required
        >
        <?php if (isset($errors['nickname'])): ?>
            <div class="invalid-feedback"><?= e($errors['nickname']) ?></div>
        <?php endif; ?>
    </div>

    <div class="col-12 col-md-8">
        <label for="phone" class="form-label">Phone</label>
        <input
            type="text"
            class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
            id="phone"
            name="phone"
            value="<?= e($member['phone'] ?? '') ?>"
            maxlength="50"
        >
        <?php if (isset($errors['phone'])): ?>
            <div class="invalid-feedback"><?= e($errors['phone']) ?></div>
        <?php endif; ?>
    </div>

    <div class="col-12 col-md-4">
        <label for="status" class="form-label">Status</label>
        <select class="form-select" id="status" name="status">
            <option value="1" <?= (int) ($member['is_active'] ?? 1) === 1 ? 'selected' : '' ?>>Active</option>
            <option value="0" <?= (int) ($member['is_active'] ?? 1) === 0 ? 'selected' : '' ?>>Non-active</option>
        </select>
    </div>

    <div class="col-12 d-flex justify-content-end gap-2">
        <a href="<?= e(url('/members')) ?>" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><?= e($submitLabel) ?></button>
    </div>
</form>
