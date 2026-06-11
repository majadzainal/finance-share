<div class="d-flex flex-column gap-4">
    <div>
        <h1 class="h3 mb-1">Dashboard</h1>
        <p class="text-secondary mb-0">Ringkasan awal finance ledger dan accounting-lite.</p>
    </div>

    <div class="row g-3">
        <?php foreach ($summaryCards as $card): ?>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div>
                                <div class="text-secondary small mb-2"><?= e($card['label']) ?></div>
                                <div class="fs-4 fw-bold"><?= e($card['value']) ?></div>
                            </div>
                            <span class="badge text-bg-<?= e($card['tone']) ?> rounded-pill">&nbsp;</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h2 class="h5 mb-2">Finance Share</h2>
            <p class="text-secondary mb-0">Layout admin sudah siap. Menu transaksi, closing, distribusi profit, dan ledger bisa dibangun bertahap dari struktur ini.</p>
        </div>
    </div>
</div>
