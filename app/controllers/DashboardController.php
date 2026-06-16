<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Dashboard;

class DashboardController extends Controller
{
    public function index(): string
    {
        $dashboard = new Dashboard();
        $filters = $this->filters();
        $summary = $dashboard->summary($filters);
        $accountHealth = $dashboard->accountHealth();

        return $this->layout('dashboard.index', [
            'title' => 'Dashboard',
            'appName' => config('app.name'),
            'activeMenu' => 'dashboard',
            'filters' => $filters,
            'summary' => $summary,
            'accountHealth' => $accountHealth,
            'summaryCards' => [
                [
                    'label' => 'Total Income',
                    'value' => $summary['total_income'],
                    'tone' => 'success',
                    'hint' => 'Total dana masuk',
                ],
                [
                    'label' => 'Total Expense',
                    'value' => $summary['total_expense'],
                    'tone' => 'danger',
                    'hint' => 'Semua pengeluaran',
                ],
                [
                    'label' => 'Net Balance',
                    'value' => $summary['expected_balance'],
                    'tone' => 'primary',
                    'hint' => 'Income - expense - kasbon disalurkan',
                ],
                [
                    'label' => 'Outstanding Kasbon',
                    'value' => $summary['outstanding_cash_advance'],
                    'tone' => 'warning',
                    'hint' => 'Sisa kasbon aktif',
                ],
            ],
            'actionNeeded' => $dashboard->actionNeeded($summary, $accountHealth),
            'groupBalances' => $dashboard->groupBalances(),
            'cashAdvanceAging' => $dashboard->cashAdvanceAging(),
            'shortcuts' => [
                ['label' => 'Import Income', 'description' => 'Upload file income terbaru', 'url' => url('/import-income'), 'tone' => 'success'],
                ['label' => 'Input Expense', 'description' => 'Catat pengeluaran baru', 'url' => url('/expenses/create'), 'tone' => 'danger'],
                ['label' => 'Create Kasbon', 'description' => 'Buat kasbon member', 'url' => url('/cash-advances/create'), 'tone' => 'warning'],
                ['label' => 'Cek Saldo Rekening', 'description' => 'Validasi 3 rekening', 'url' => url('/account-balances'), 'tone' => 'primary'],
                ['label' => 'Preview Closing', 'description' => 'Simulasi closing profit', 'url' => url('/closing'), 'tone' => 'dark'],
                ['label' => 'Ledger Report', 'description' => 'Audit mutasi ledger', 'url' => url('/ledger-report'), 'tone' => 'secondary'],
            ],
            'recentActivity' => $dashboard->recentActivity($filters),
        ]);
    }

    private function filters(): array
    {
        $period = $_GET['period'] ?? 'month';
        $allowedPeriods = ['today', 'month', 'year', 'all', 'custom'];

        if (! in_array($period, $allowedPeriods, true)) {
            $period = 'month';
        }

        $today = date('Y-m-d');
        $dateFrom = '';
        $dateTo = '';

        if ($period === 'today') {
            $dateFrom = $today;
            $dateTo = $today;
        } elseif ($period === 'month') {
            $dateFrom = date('Y-m-01');
            $dateTo = $today;
        } elseif ($period === 'year') {
            $dateFrom = date('Y-01-01');
            $dateTo = $today;
        } elseif ($period === 'custom') {
            $customFrom = (string) ($_GET['date_from'] ?? '');
            $customTo = (string) ($_GET['date_to'] ?? '');
            $dateFrom = $this->validDate($customFrom) ? $customFrom : '';
            $dateTo = $this->validDate($customTo) ? $customTo : '';
        }

        return [
            'period' => $period,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];
    }

    private function validDate(string $date): bool
    {
        $parsed = \DateTime::createFromFormat('Y-m-d', $date);

        return $parsed instanceof \DateTime && $parsed->format('Y-m-d') === $date;
    }
}
