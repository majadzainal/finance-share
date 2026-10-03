<?php

declare(strict_types=1);

use App\Controllers\DashboardController;
use App\Controllers\AuthController;
use App\Controllers\AccountBalanceController;
use App\Controllers\AuditLogController;
use App\Controllers\CashAdvanceController;
use App\Controllers\ClosingController;
use App\Controllers\ExpenseController;
use App\Controllers\GroupController;
use App\Controllers\GroupMemberController;
use App\Controllers\GroupSavingsController;
use App\Controllers\IncomeController;
use App\Controllers\IncomeImportController;
use App\Controllers\LedgerReportController;
use App\Controllers\MemberController;
use App\Controllers\ProfitDistributionController;
use App\Controllers\RoleController;
use App\Controllers\UserController;
use App\Core\Router;
use App\Middleware\AuthMiddleware;

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/app/helpers/functions.php';

$appConfig = config('app');
date_default_timezone_set($appConfig['timezone'] ?? 'UTC');
session_start();

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require $file;
    }
});

$router = new Router();

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/', [DashboardController::class, 'index']);
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/groups', [GroupController::class, 'index']);
$router->get('/groups/create', [GroupController::class, 'create']);
$router->post('/groups', [GroupController::class, 'store']);
$router->get('/groups/{id}', [GroupController::class, 'show']);
$router->get('/groups/{id}/edit', [GroupController::class, 'edit']);
$router->post('/groups/{id}', [GroupController::class, 'update']);
$router->post('/groups/{id}/activate', [GroupController::class, 'activate']);
$router->post('/groups/{id}/deactivate', [GroupController::class, 'deactivate']);
$router->get('/members', [MemberController::class, 'index']);
$router->get('/members/create', [MemberController::class, 'create']);
$router->post('/members', [MemberController::class, 'store']);
$router->get('/members/{id}/edit', [MemberController::class, 'edit']);
$router->post('/members/{id}', [MemberController::class, 'update']);
$router->post('/members/{id}/activate', [MemberController::class, 'activate']);
$router->post('/members/{id}/deactivate', [MemberController::class, 'deactivate']);
$router->get('/group-members', [GroupMemberController::class, 'index']);
$router->get('/group-members/create', [GroupMemberController::class, 'create']);
$router->post('/group-members', [GroupMemberController::class, 'store']);
$router->get('/group-members/{id}/edit', [GroupMemberController::class, 'edit']);
$router->post('/group-members/{id}', [GroupMemberController::class, 'update']);
$router->post('/group-members/{id}/delete', [GroupMemberController::class, 'destroy']);
$router->get('/import-income', [IncomeImportController::class, 'index']);
$router->get('/import-income/errors', [IncomeImportController::class, 'errors']);
$router->post('/import-income', [IncomeImportController::class, 'store']);
$router->get('/income', [IncomeController::class, 'index']);
$router->get('/income/{id}/edit', [IncomeController::class, 'edit']);
$router->post('/income/{id}', [IncomeController::class, 'update']);
$router->post('/income/{id}/delete', [IncomeController::class, 'destroy']);
$router->post('/income/{id}/restore', [IncomeController::class, 'restore']);
$router->get('/expenses', [ExpenseController::class, 'index']);
$router->get('/expenses/create', [ExpenseController::class, 'create']);
$router->post('/expenses', [ExpenseController::class, 'store']);
$router->get('/expenses/{id}/edit', [ExpenseController::class, 'edit']);
$router->post('/expenses/{id}', [ExpenseController::class, 'update']);
$router->post('/expenses/{id}/approve', [ExpenseController::class, 'approve']);
$router->post('/expenses/{id}/reject', [ExpenseController::class, 'reject']);
$router->post('/expenses/{id}/delete', [ExpenseController::class, 'destroy']);
$router->get('/cash-advances', [CashAdvanceController::class, 'index']);
$router->get('/cash-advances/create', [CashAdvanceController::class, 'create']);
$router->post('/cash-advances', [CashAdvanceController::class, 'store']);
$router->get('/cash-advances/{id}', [CashAdvanceController::class, 'show']);
$router->get('/group-savings', [GroupSavingsController::class, 'index']);
$router->get('/group-savings/withdraw', [GroupSavingsController::class, 'createWithdrawal']);
$router->post('/group-savings/withdraw', [GroupSavingsController::class, 'storeWithdrawal']);
$router->get('/group-savings/deposit', [GroupSavingsController::class, 'createDeposit']);
$router->post('/group-savings/deposit', [GroupSavingsController::class, 'storeDeposit']);
$router->get('/group-savings/{id}', [GroupSavingsController::class, 'show']);
$router->get('/account-balances', [AccountBalanceController::class, 'index']);
$router->post('/account-balances', [AccountBalanceController::class, 'update']);
$router->get('/closing', [ClosingController::class, 'index']);
$router->post('/closing', [ClosingController::class, 'preview']);
$router->get('/closing/preview', [ClosingController::class, 'index']);
$router->post('/closing/preview', [ClosingController::class, 'preview']);
$router->post('/closing/finalize', [ClosingController::class, 'finalize']);
$router->get('/profit-distribution', [ProfitDistributionController::class, 'index']);
$router->get('/profit-distribution/{id}', [ProfitDistributionController::class, 'show']);
$router->post('/profit-distribution/{id}/paid', [ProfitDistributionController::class, 'markPaid']);
$router->get('/ledger-report', [LedgerReportController::class, 'index']);
$router->get('/ledger-report/export', [LedgerReportController::class, 'export']);
$router->get('/users', [UserController::class, 'index']);
$router->get('/users/create', [UserController::class, 'create']);
$router->post('/users', [UserController::class, 'store']);
$router->get('/users/{id}/edit', [UserController::class, 'edit']);
$router->post('/users/{id}', [UserController::class, 'update']);
$router->post('/users/{id}/activate', [UserController::class, 'activate']);
$router->post('/users/{id}/deactivate', [UserController::class, 'deactivate']);
$router->get('/roles', [RoleController::class, 'index']);
$router->get('/roles/create', [RoleController::class, 'create']);
$router->post('/roles', [RoleController::class, 'store']);
$router->get('/roles/{id}/edit', [RoleController::class, 'edit']);
$router->post('/roles/{id}', [RoleController::class, 'update']);
$router->post('/roles/{id}/activate', [RoleController::class, 'activate']);
$router->post('/roles/{id}/deactivate', [RoleController::class, 'deactivate']);
$router->get('/audit-logs', [AuditLogController::class, 'index']);

(new AuthMiddleware())->handle(request_path());
$router->dispatch($_SERVER['REQUEST_METHOD'], request_path());
