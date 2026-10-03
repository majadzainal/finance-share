<?php
$menus = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'url' => url('/dashboard')],
    ['key' => 'groups', 'label' => 'Groups / Store', 'url' => url('/groups')],
    ['key' => 'members', 'label' => 'Members', 'url' => url('/members')],
    ['key' => 'group_members', 'label' => 'Group Members', 'url' => url('/group-members')],
    ['key' => 'import_income', 'label' => 'Import Income', 'url' => url('/import-income')],
    ['key' => 'income', 'label' => 'Income', 'url' => url('/income')],
    ['key' => 'expenses', 'label' => 'Expenses', 'url' => url('/expenses')],
    ['key' => 'cash_advances', 'label' => 'Cash Advances / Kasbon', 'url' => url('/cash-advances')],
    ['key' => 'group_savings', 'label' => 'Tabungan Group', 'url' => url('/group-savings')],
    ['key' => 'account_balances', 'label' => 'Saldo Rekening', 'url' => url('/account-balances')],
    ['key' => 'closing', 'label' => 'Closing', 'url' => url('/closing')],
    ['key' => 'profit_distribution', 'label' => 'Profit Distribution', 'url' => url('/profit-distribution')],
    ['key' => 'ledger_report', 'label' => 'Ledger Report', 'url' => url('/ledger-report')],
    ['key' => 'users', 'label' => 'User Management', 'url' => url('/users')],
    ['key' => 'roles', 'label' => 'Roles', 'url' => url('/roles')],
    ['key' => 'audit_logs', 'label' => 'Audit Trail', 'url' => url('/audit-logs')],
];

if (($_SESSION['user']['role'] ?? '') !== 'admin') {
    $menus = array_values(array_filter(
        $menus,
        static fn (array $menu): bool => ! in_array($menu['key'], ['users', 'roles', 'audit_logs'], true)
    ));
}
?>

<aside class="app-sidebar d-none d-lg-flex flex-column flex-shrink-0 text-white">
    <div class="p-3 border-bottom border-light border-opacity-10">
        <div class="fw-bold fs-5"><?= e($appName) ?></div>
        <div class="small text-white-50">Accounting Lite</div>
    </div>

    <nav class="p-3 overflow-auto">
        <div class="nav nav-pills flex-column gap-1">
            <?php foreach ($menus as $menu): ?>
                <a
                    class="nav-link <?= ($activeMenu ?? '') === $menu['key'] ? 'active' : '' ?>"
                    href="<?= e($menu['url']) ?>"
                    title="<?= e($menu['label']) ?>"
                >
                    <span><?= e($menu['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </nav>
</aside>

<aside class="app-sidebar offcanvas offcanvas-start d-lg-none text-white" tabindex="-1" id="appSidebar" aria-labelledby="appSidebarLabel">
    <div class="offcanvas-header border-bottom border-light border-opacity-10 p-3">
        <div>
            <div class="fw-bold fs-5" id="appSidebarLabel"><?= e($appName) ?></div>
            <div class="small text-white-50">Accounting Lite</div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <nav class="offcanvas-body p-3 overflow-auto">
        <div class="nav nav-pills flex-column gap-1">
            <?php foreach ($menus as $menu): ?>
                <a
                    class="nav-link <?= ($activeMenu ?? '') === $menu['key'] ? 'active' : '' ?>"
                    href="<?= e($menu['url']) ?>"
                    title="<?= e($menu['label']) ?>"
                >
                    <span><?= e($menu['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </nav>
</aside>
