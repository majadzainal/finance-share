<?php $exportQuery = http_build_query($filters); ?>

<div class="d-flex flex-column gap-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <h1 class="h3 mb-1">Ledger Report</h1>
            <p class="text-secondary mb-0">Laporan mutasi ledger dengan running balance.</p>
        </div>
        <div>
            <a href="<?= e(url('/ledger-report/export?' . $exportQuery)) ?>" class="btn btn-outline-primary">Export CSV</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="get" action="<?= e(url('/ledger-report')) ?>" class="row g-3">
                <div class="col-12 col-md-3">
                    <label for="group_id" class="form-label">Group / Store</label>
                    <select class="form-select" id="group_id" name="group_id">
                        <option value="0">All Groups / Store</option>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?= e($group['id']) ?>" <?= (int) $filters['group_id'] === (int) $group['id'] ? 'selected' : '' ?>>
                                <?= e($group['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label for="member_id" class="form-label">Member</label>
                    <select class="form-select" id="member_id" name="member_id">
                        <option value="0">All Members</option>
                        <?php foreach ($members as $member): ?>
                            <option value="<?= e($member['id']) ?>" <?= (int) $filters['member_id'] === (int) $member['id'] ? 'selected' : '' ?>>
                                <?= e($member['full_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <label for="date_from" class="form-label">Tanggal Awal</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?= e($filters['date_from']) ?>">
                </div>

                <div class="col-12 col-md-2">
                    <label for="date_to" class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?= e($filters['date_to']) ?>">
                </div>

                <div class="col-12 col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-outline-primary flex-fill">Filter</button>
                    <a href="<?= e(url('/ledger-report')) ?>" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Trx Date</th>
                        <th>Reference Type</th>
                        <th>Reference ID</th>
                        <th>Description</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                        <th class="text-end">Running Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows === []): ?>
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-4">Data ledger tidak ditemukan.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?= e($row['trx_date']) ?></td>
                            <td><?= e($row['reference_type']) ?></td>
                            <td><?= e($row['reference_id']) ?></td>
                            <td>
                                <div><?= e($row['description'] ?: '-') ?></div>
                                <div class="small text-secondary">
                                    <?= e($row['group_name']) ?><?= $row['member_name'] ? ' / ' . e($row['member_name']) : '' ?>
                                </div>
                            </td>
                            <td class="text-end">Rp <?= e(number_format((float) $row['debit'], 0, ',', '.')) ?></td>
                            <td class="text-end">Rp <?= e(number_format((float) $row['credit'], 0, ',', '.')) ?></td>
                            <td class="text-end fw-semibold">Rp <?= e(number_format((float) $row['running_balance'], 0, ',', '.')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
