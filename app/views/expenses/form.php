<form method="post" action="<?= e($action) ?>" class="row g-3">
    <div class="col-12 col-md-4">
        <label for="group_id" class="form-label">Group / Store</label>
        <select class="form-select <?= isset($errors['group_id']) ? 'is-invalid' : '' ?>" id="group_id" name="group_id" required>
            <option value="">Select group/store</option>
            <?php foreach ($groups as $group): ?>
                <option value="<?= e($group['id']) ?>" <?= (int) ($expense['group_id'] ?? 0) === (int) $group['id'] ? 'selected' : '' ?>>
                    <?= e($group['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['group_id'])): ?>
            <div class="invalid-feedback"><?= e($errors['group_id']) ?></div>
        <?php endif; ?>
    </div>

    <div class="col-12 col-md-4">
        <label for="category_id" class="form-label">Category</label>
        <select class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>" id="category_id" name="category_id" required>
            <option value="">Select category</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= e($category['id']) ?>" <?= (int) ($expense['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : '' ?>>
                    <?= e($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['category_id'])): ?>
            <div class="invalid-feedback"><?= e($errors['category_id']) ?></div>
        <?php endif; ?>
    </div>

    <div class="col-12 col-md-4">
        <label for="expense_date" class="form-label">Expense Date</label>
        <input type="date" class="form-control <?= isset($errors['expense_date']) ? 'is-invalid' : '' ?>" id="expense_date" name="expense_date" value="<?= e($expense['expense_date'] ?? '') ?>" required>
        <?php if (isset($errors['expense_date'])): ?>
            <div class="invalid-feedback"><?= e($errors['expense_date']) ?></div>
        <?php endif; ?>
    </div>

    <div class="col-12 col-md-6">
        <label for="amount" class="form-label">Amount</label>
        <input type="number" step="0.01" min="0.01" class="form-control <?= isset($errors['amount']) ? 'is-invalid' : '' ?>" id="amount" name="amount" value="<?= e($expense['amount'] ?? '') ?>" required>
        <?php if (isset($errors['amount'])): ?>
            <div class="invalid-feedback"><?= e($errors['amount']) ?></div>
        <?php endif; ?>
    </div>

    <div class="col-12 col-md-6">
        <label for="transfer_method_id" class="form-label">Metode Transfer</label>
        <select class="form-select" id="transfer_method_id" name="transfer_method_id" data-transfer-method>
            <option value="0" data-fee="0">Tanpa metode transfer</option>
            <?php foreach ($transferMethods as $method): ?>
                <option
                    value="<?= e($method['id']) ?>"
                    data-fee="<?= e($method['default_fee']) ?>"
                    <?= (int) ($expense['transfer_method_id'] ?? 0) === (int) $method['id'] ? 'selected' : '' ?>
                >
                    <?= e($method['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-12 col-md-6">
        <label for="transfer_fee_amount" class="form-label">Biaya Transfer</label>
        <input type="number" step="0.01" min="0" class="form-control <?= isset($errors['transfer_fee_amount']) ? 'is-invalid' : '' ?>" id="transfer_fee_amount" name="transfer_fee_amount" value="<?= e($expense['transfer_fee_amount'] ?? 0) ?>" data-transfer-fee>
        <?php if (isset($errors['transfer_fee_amount'])): ?>
            <div class="invalid-feedback"><?= e($errors['transfer_fee_amount']) ?></div>
        <?php endif; ?>
    </div>

    <div class="col-12 col-md-6">
        <label for="created_by" class="form-label">Created By</label>
        <input type="text" class="form-control <?= isset($errors['created_by']) ? 'is-invalid' : '' ?>" id="created_by" name="created_by" value="<?= e($expense['created_by'] ?? '') ?>" maxlength="100" required>
        <?php if (isset($errors['created_by'])): ?>
            <div class="invalid-feedback"><?= e($errors['created_by']) ?></div>
        <?php endif; ?>
    </div>

    <div class="col-12">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" id="description" name="description" rows="3" required><?= e($expense['description'] ?? '') ?></textarea>
        <?php if (isset($errors['description'])): ?>
            <div class="invalid-feedback"><?= e($errors['description']) ?></div>
        <?php endif; ?>
    </div>

    <div class="col-12 d-flex justify-content-end gap-2">
        <a href="<?= e(url('/expenses')) ?>" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><?= e($submitLabel) ?></button>
    </div>
</form>

<script>
    document.querySelectorAll('[data-transfer-method]').forEach((select) => {
        select.addEventListener('change', () => {
            const form = select.closest('form');
            const feeInput = form ? form.querySelector('[data-transfer-fee]') : null;
            const option = select.options[select.selectedIndex];

            if (feeInput && option) {
                feeInput.value = option.dataset.fee || '0';
            }
        });
    });
</script>
