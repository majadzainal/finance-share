<div class="d-flex flex-column gap-4">
    <div class="d-flex align-items-center gap-2">
        <a href="<?= e(url('/group-savings')) ?>" class="text-decoration-none text-secondary">&larr; Kembali ke Tabungan</a>
    </div>

    <div>
        <h1 class="h3 mb-1">Setor Tabungan Toko</h1>
        <p class="text-secondary mb-0">Catat penambahan atau setoran manual ke dana cadangan/tabungan toko.</p>
    </div>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger mb-0"><?= e($errors['general']) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm col-12 col-lg-8">
        <div class="card-body">
            <form method="post" action="<?= e(url('/group-savings/deposit')) ?>" class="d-flex flex-column gap-3">
                <div>
                    <label for="group_id" class="form-label">Pilih Group / Store <span class="text-danger">*</span></label>
                    <select class="form-select <?= isset($errors['group_id']) ? 'is-invalid' : '' ?>" id="group_id" name="group_id" required>
                        <option value="">-- Pilih Toko / Group --</option>
                        <?php foreach ($groups as $group): ?>
                            <?php 
                                $bal = 0.0;
                                foreach ($groupBalances as $gb) {
                                    if ((int)$gb['id'] === (int)$group['id']) {
                                        $bal = (float)$gb['current_balance'];
                                        break;
                                    }
                                }
                            ?>
                            <option value="<?= e($group['id']) ?>" 
                                    data-balance="<?= $bal ?>"
                                    <?= (int) ($data['group_id'] ?? $selectedGroupId) === (int) $group['id'] ? 'selected' : '' ?>>
                                <?= e($group['name']) ?> (Saldo saat ini: Rp <?= e(number_format($bal, 0, ',', '.')) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['group_id'])): ?>
                        <div class="invalid-feedback"><?= e($errors['group_id']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="transaction_date" class="form-label">Tanggal Setoran <span class="text-danger">*</span></label>
                        <input type="date" class="form-control <?= isset($errors['transaction_date']) ? 'is-invalid' : '' ?>" 
                               id="transaction_date" name="transaction_date" 
                               value="<?= e($data['transaction_date'] ?? date('Y-m-d')) ?>" required>
                        <?php if (isset($errors['transaction_date'])): ?>
                            <div class="invalid-feedback"><?= e($errors['transaction_date']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="amount" class="form-label">Nominal Setoran (Rp) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="1" 
                               class="form-control <?= isset($errors['amount']) ? 'is-invalid' : '' ?>" 
                               id="amount" name="amount" 
                               placeholder="Contoh: 1000000"
                               value="<?= e($data['amount'] ?? '') ?>" required>
                        <?php if (isset($errors['amount'])): ?>
                            <div class="invalid-feedback"><?= e($errors['amount']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <label for="reference_no" class="form-label">No. Referensi / No. Bukti Setor (Opsional)</label>
                    <input type="text" class="form-control" id="reference_no" name="reference_no" 
                           placeholder="Contoh: DEP-SAV-2026-001"
                           value="<?= e($data['reference_no'] ?? '') ?>">
                </div>

                <div>
                    <label for="description" class="form-label">Keterangan Setoran</label>
                    <textarea class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" 
                              id="description" name="description" rows="3" 
                              placeholder="Keterangan setoran dana tabungan toko"><?= e($data['description'] ?? '') ?></textarea>
                    <?php if (isset($errors['description'])): ?>
                        <div class="invalid-feedback"><?= e($errors['description']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3 pt-3 border-top">
                    <a href="<?= e(url('/group-savings')) ?>" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">Simpan Setoran Tabungan</button>
                </div>
            </form>
        </div>
    </div>
</div>
