<div class="d-flex flex-column gap-4">
    <div>
        <h1 class="h3 mb-1">Edit Income</h1>
        <p class="text-secondary mb-0">Data income hanya bisa diedit selama belum locked oleh closing.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="post" action="<?= e(url('/income/' . $income['id'])) ?>" class="row g-3">
                <input type="hidden" name="return_url" value="<?= e($returnUrl ?? '/income') ?>">
                <div class="col-12 col-md-3">
                    <label class="form-label">Group / Store</label>
                    <input type="text" class="form-control" value="<?= e($income['group_name']) ?>" disabled>
                </div>

                <div class="col-12 col-md-3">
                    <label for="transaction_date" class="form-label">Date</label>
                    <input type="date" class="form-control <?= isset($errors['transaction_date']) ? 'is-invalid' : '' ?>" id="transaction_date" name="transaction_date" value="<?= e($income['transaction_date']) ?>" required>
                    <?php if (isset($errors['transaction_date'])): ?>
                        <div class="invalid-feedback"><?= e($errors['transaction_date']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-12 col-md-3">
                    <label for="reference_no" class="form-label">Reference</label>
                    <input type="text" class="form-control <?= isset($errors['reference_no']) ? 'is-invalid' : '' ?>" id="reference_no" name="reference_no" value="<?= e($income['reference_no']) ?>" required>
                    <?php if (isset($errors['reference_no'])): ?>
                        <div class="invalid-feedback"><?= e($errors['reference_no']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-12 col-md-3">
                    <label for="amount" class="form-label">Amount</label>
                    <input type="number" step="0.01" min="0.01" class="form-control <?= isset($errors['amount']) ? 'is-invalid' : '' ?>" id="amount" name="amount" value="<?= e($income['amount']) ?>" required>
                    <?php if (isset($errors['amount'])): ?>
                        <div class="invalid-feedback"><?= e($errors['amount']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-12 col-md-4">
                    <label for="client_name" class="form-label">Client Name</label>
                    <input type="text" class="form-control <?= isset($errors['client_name']) ? 'is-invalid' : '' ?>" id="client_name" name="client_name" value="<?= e($income['client_name']) ?>" required>
                    <?php if (isset($errors['client_name'])): ?>
                        <div class="invalid-feedback"><?= e($errors['client_name']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-12 col-md-4">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?= e($income['username']) ?>">
                </div>

                <div class="col-12 col-md-4">
                    <label for="profile_package" class="form-label">Profile Package</label>
                    <input type="text" class="form-control" id="profile_package" name="profile_package" value="<?= e($income['profile_package']) ?>">
                </div>

                <div class="col-12 col-md-3">
                    <label for="payment_method" class="form-label">Payment Type</label>
                    <input type="text" class="form-control" id="payment_method" name="payment_method" value="<?= e($income['payment_method']) ?>">
                </div>

                <div class="col-12 col-md-3">
                    <label for="bank_target" class="form-label">Bank Target</label>
                    <input type="text" class="form-control" id="bank_target" name="bank_target" value="<?= e($income['bank_target']) ?>">
                </div>

                <div class="col-12 col-md-3">
                    <label for="income_type" class="form-label">Income Type</label>
                    <input type="text" class="form-control" id="income_type" name="income_type" value="<?= e($income['income_type']) ?>">
                </div>

                <div class="col-12 col-md-3">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" class="form-control" id="address" name="address" value="<?= e($income['address']) ?>">
                </div>

                <div class="col-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= e($income['description']) ?></textarea>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="<?= e(url($returnUrl ?? '/income')) ?>" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Income</button>
                </div>
            </form>
        </div>
    </div>
</div>
