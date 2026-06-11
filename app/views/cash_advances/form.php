<form method="post" action="<?= e($action) ?>" class="row g-3">
    <div class="col-12 col-md-6">
        <label for="member_id" class="form-label">Member</label>
        <select class="form-select <?= isset($errors['member_id']) ? 'is-invalid' : '' ?>" id="member_id" name="member_id" required>
            <option value="">Select member</option>
            <?php foreach ($members as $member): ?>
                <option value="<?= e($member['id']) ?>" <?= (int) ($cashAdvance['member_id'] ?? 0) === (int) $member['id'] ? 'selected' : '' ?>>
                    <?= e($member['full_name']) ?><?= $member['nickname'] ? ' (' . e($member['nickname']) . ')' : '' ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['member_id'])): ?>
            <div class="invalid-feedback"><?= e($errors['member_id']) ?></div>
        <?php endif; ?>
    </div>

    <div class="col-12 col-md-6">
        <label for="group_id" class="form-label">Group / Store</label>
        <select class="form-select" id="group_id" name="group_id">
            <option value="0">No group/store</option>
            <?php foreach ($groups as $group): ?>
                <option value="<?= e($group['id']) ?>" <?= (int) ($cashAdvance['group_id'] ?? 0) === (int) $group['id'] ? 'selected' : '' ?>>
                    <?= e($group['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-12 col-md-4">
        <label for="advance_date" class="form-label">Advance Date</label>
        <input type="date" class="form-control <?= isset($errors['advance_date']) ? 'is-invalid' : '' ?>" id="advance_date" name="advance_date" value="<?= e($cashAdvance['advance_date'] ?? '') ?>" required>
        <?php if (isset($errors['advance_date'])): ?>
            <div class="invalid-feedback"><?= e($errors['advance_date']) ?></div>
        <?php endif; ?>
    </div>

    <div class="col-12 col-md-4">
        <label for="amount" class="form-label">Amount</label>
        <input type="number" step="0.01" min="0.01" class="form-control <?= isset($errors['amount']) ? 'is-invalid' : '' ?>" id="amount" name="amount" value="<?= e($cashAdvance['amount'] ?? '') ?>" required>
        <?php if (isset($errors['amount'])): ?>
            <div class="invalid-feedback"><?= e($errors['amount']) ?></div>
        <?php endif; ?>
    </div>

    <div class="col-12 col-md-4">
        <label for="status" class="form-label">Status</label>
        <select class="form-select" id="status" name="status">
            <option value="0" <?= (int) ($cashAdvance['status'] ?? 0) === 0 ? 'selected' : '' ?>>Outstanding</option>
            <option value="1" <?= (int) ($cashAdvance['status'] ?? 0) === 1 ? 'selected' : '' ?>>Paid</option>
        </select>
    </div>

    <div class="col-12 col-md-6">
        <label for="transfer_method_id" class="form-label">Metode Transfer</label>
        <select class="form-select" id="transfer_method_id" name="transfer_method_id" data-transfer-method>
            <option value="0" data-fee="0">Tanpa metode transfer</option>
            <?php foreach ($transferMethods as $method): ?>
                <option
                    value="<?= e($method['id']) ?>"
                    data-fee="<?= e($method['default_fee']) ?>"
                    <?= (int) ($cashAdvance['transfer_method_id'] ?? 0) === (int) $method['id'] ? 'selected' : '' ?>
                >
                    <?= e($method['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-12 col-md-6">
        <label for="transfer_fee_amount" class="form-label">Biaya Transfer</label>
        <input type="number" step="0.01" min="0" class="form-control <?= isset($errors['transfer_fee_amount']) ? 'is-invalid' : '' ?>" id="transfer_fee_amount" name="transfer_fee_amount" value="<?= e($cashAdvance['transfer_fee_amount'] ?? 0) ?>" data-transfer-fee>
        <?php if (isset($errors['transfer_fee_amount'])): ?>
            <div class="invalid-feedback"><?= e($errors['transfer_fee_amount']) ?></div>
        <?php endif; ?>
    </div>

    <div class="col-12">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" id="description" name="description" rows="3" required><?= e($cashAdvance['description'] ?? '') ?></textarea>
        <?php if (isset($errors['description'])): ?>
            <div class="invalid-feedback"><?= e($errors['description']) ?></div>
        <?php endif; ?>
    </div>

    <div class="col-12 d-flex justify-content-end gap-2">
        <a href="<?= e(url('/cash-advances')) ?>" class="btn btn-outline-secondary">Cancel</a>
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
