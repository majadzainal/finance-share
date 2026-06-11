<form method="post" action="<?= e($action) ?>" class="row g-3">
    <div class="col-12 col-md-6">
        <label for="name" class="form-label">Name</label>
        <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" id="name" name="name" value="<?= e($user['name'] ?? '') ?>" required>
        <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= e($errors['name']) ?></div><?php endif; ?>
    </div>

    <div class="col-12 col-md-6">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" id="username" name="username" value="<?= e($user['username'] ?? '') ?>" required>
        <?php if (isset($errors['username'])): ?><div class="invalid-feedback"><?= e($errors['username']) ?></div><?php endif; ?>
    </div>

    <div class="col-12 col-md-6">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" id="password" name="password" <?= ($passwordRequired ?? false) ? 'required' : '' ?>>
        <?php if (! ($passwordRequired ?? false)): ?><div class="form-text">Kosongkan jika tidak ingin mengganti password.</div><?php endif; ?>
        <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= e($errors['password']) ?></div><?php endif; ?>
    </div>

    <div class="col-12 col-md-4">
        <label for="role" class="form-label">Role</label>
        <select class="form-select <?= isset($errors['role']) ? 'is-invalid' : '' ?>" id="role" name="role" required>
            <option value="">Select role</option>
            <?php foreach ($roles as $role): ?>
                <option value="<?= e($role['code']) ?>" <?= ($user['role'] ?? '') === $role['code'] ? 'selected' : '' ?>>
                    <?= e($role['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['role'])): ?><div class="invalid-feedback"><?= e($errors['role']) ?></div><?php endif; ?>
    </div>

    <div class="col-12 col-md-2">
        <label for="status" class="form-label">Status</label>
        <select class="form-select" id="status" name="status">
            <option value="1" <?= (int) ($user['status'] ?? 1) === 1 ? 'selected' : '' ?>>Active</option>
            <option value="0" <?= (int) ($user['status'] ?? 1) === 0 ? 'selected' : '' ?>>Inactive</option>
        </select>
    </div>

    <div class="col-12 d-flex justify-content-end gap-2">
        <a href="<?= e(url('/users')) ?>" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><?= e($submitLabel) ?></button>
    </div>
</form>
