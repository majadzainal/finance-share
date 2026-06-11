<header class="app-header bg-white border-bottom d-flex align-items-center justify-content-between px-3 px-md-4">
    <div class="d-flex align-items-center gap-2 app-title">
        <button class="btn btn-sm btn-outline-secondary d-lg-none flex-shrink-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#appSidebar" aria-controls="appSidebar" aria-label="Open menu">
            Menu
        </button>
        <div class="min-w-0">
        <div class="fw-semibold"><?= e($title ?? 'Dashboard') ?></div>
        <div class="small text-secondary">Native PHP 8 + MySQL ledger</div>
        </div>
    </div>

    <div class="text-end app-user-panel">
        <div class="small text-secondary"><?= e($_SESSION['user']['role'] ?? 'guest') ?></div>
        <div class="d-flex align-items-center gap-2">
            <span class="fw-semibold app-user-name"><?= e($_SESSION['user']['name'] ?? 'Guest') ?></span>
            <form method="post" action="<?= e(url('/logout')) ?>" class="mb-0">
                <button type="submit" class="btn btn-sm btn-outline-secondary">Logout</button>
            </form>
        </div>
    </div>
</header>
