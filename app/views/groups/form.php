<form method="post" action="<?= e($action) ?>" class="row g-3">
    <div class="col-12 col-md-4">
        <label for="code" class="form-label">Code</label>
        <input
            type="text"
            class="form-control <?= isset($errors['code']) ? 'is-invalid' : '' ?>"
            id="code"
            name="code"
            value="<?= e($group['code'] ?? '') ?>"
            maxlength="50"
            required
        >
        <?php if (isset($errors['code'])): ?>
            <div class="invalid-feedback"><?= e($errors['code']) ?></div>
        <?php endif; ?>
    </div>

    <div class="col-12 col-md-5">
        <label for="name" class="form-label">Name</label>
        <input
            type="text"
            class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
            id="name"
            name="name"
            value="<?= e($group['name'] ?? '') ?>"
            maxlength="150"
            required
        >
        <?php if (isset($errors['name'])): ?>
            <div class="invalid-feedback"><?= e($errors['name']) ?></div>
        <?php endif; ?>
    </div>

    <div class="col-12 col-md-3">
        <label for="status" class="form-label">Status</label>
        <select class="form-select" id="status" name="status">
            <option value="1" <?= (int) ($group['is_active'] ?? 1) === 1 ? 'selected' : '' ?>>Active</option>
            <option value="0" <?= (int) ($group['is_active'] ?? 1) === 0 ? 'selected' : '' ?>>Non-active</option>
        </select>
    </div>

    <div class="col-12 d-flex justify-content-end gap-2">
        <a href="<?= e(url('/groups')) ?>" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><?= e($submitLabel) ?></button>
    </div>
</form>
