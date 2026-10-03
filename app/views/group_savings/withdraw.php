<div class="d-flex flex-column gap-4">
    <div class="d-flex align-items-center gap-2">
        <a href="<?= e(url('/group-savings')) ?>" class="text-decoration-none text-secondary">&larr; Kembali ke Tabungan</a>
    </div>

    <div>
        <h1 class="h3 mb-1">Tarik / Pengeluaran Uang Tabungan Toko</h1>
        <p class="text-secondary mb-0">Catat penarikan atau pengeluaran dana dari cadangan/tabungan toko untuk keperluan operasional/khusus.</p>
    </div>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger mb-0"><?= e($errors['general']) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm col-12 col-lg-8">
        <div class="card-body">
            <form method="post" action="<?= e(url('/group-savings/withdraw')) ?>" class="d-flex flex-column gap-3">
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
                                <?= e($group['name']) ?> (Saldo: Rp <?= e(number_format($bal, 0, ',', '.')) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['group_id'])): ?>
                        <div class="invalid-feedback"><?= e($errors['group_id']) ?></div>
                    <?php endif; ?>
                </div>

                <div id="balance-info-box" class="alert alert-info py-2 px-3 mb-0 d-none">
                    <div class="small">Saldo Tabungan Toko Saat Ini: <strong id="balance-info-amount">Rp 0</strong></div>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="transaction_date" class="form-label">Tanggal Penarikan <span class="text-danger">*</span></label>
                        <input type="date" class="form-control <?= isset($errors['transaction_date']) ? 'is-invalid' : '' ?>" 
                               id="transaction_date" name="transaction_date" 
                               value="<?= e($data['transaction_date'] ?? date('Y-m-d')) ?>" required>
                        <?php if (isset($errors['transaction_date'])): ?>
                            <div class="invalid-feedback"><?= e($errors['transaction_date']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="amount" class="form-label">Nominal Penarikan (Rp) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="1" 
                               class="form-control <?= isset($errors['amount']) ? 'is-invalid' : '' ?>" 
                               id="amount" name="amount" 
                               placeholder="Contoh: 500000"
                               value="<?= e($data['amount'] ?? '') ?>" required>
                        <?php if (isset($errors['amount'])): ?>
                            <div class="invalid-feedback"><?= e($errors['amount']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <label for="reference_no" class="form-label">No. Referensi / No. Bukti Transaksi (Opsional)</label>
                    <input type="text" class="form-control" id="reference_no" name="reference_no" 
                           placeholder="Contoh: WD-SAV-2026-001"
                           value="<?= e($data['reference_no'] ?? '') ?>">
                </div>

                <div>
                    <label for="description" class="form-label">Keterangan / Keperluan Pengeluaran <span class="text-danger">*</span></label>
                    <textarea class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" 
                              id="description" name="description" rows="3" 
                              placeholder="Jelaskan alasan pengeluaran tabungan (misal: Pembelian inventaris toko, perbaikan fasilitas, dll)" required><?= e($data['description'] ?? '') ?></textarea>
                    <?php if (isset($errors['description'])): ?>
                        <div class="invalid-feedback"><?= e($errors['description']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3 pt-3 border-top">
                    <a href="<?= e(url('/group-savings')) ?>" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-danger">Simpan Penarikan Tabungan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const groupSelect = document.getElementById('group_id');
    const balanceBox = document.getElementById('balance-info-box');
    const balanceAmount = document.getElementById('balance-info-amount');

    function updateBalanceInfo() {
        const option = groupSelect.options[groupSelect.selectedIndex];
        if (option && option.value) {
            const bal = parseFloat(option.dataset.balance || '0');
            balanceAmount.textContent = 'Rp ' + bal.toLocaleString('id-ID');
            balanceBox.classList.remove('d-none');
        } else {
            balanceBox.classList.add('d-none');
        }
    }

    groupSelect.addEventListener('change', updateBalanceInfo);
    updateBalanceInfo();
</script>
