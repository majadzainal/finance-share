<?php

namespace App\Controllers;

use App\Core\Controller;

class DashboardController extends Controller
{
    public function index(): string
    {
        return $this->layout('dashboard.index', [
            'title' => 'Dashboard',
            'appName' => config('app.name'),
            'activeMenu' => 'dashboard',
            'summaryCards' => [
                [
                    'label' => 'Total Income',
                    'value' => 'Rp 0',
                    'tone' => 'success',
                ],
                [
                    'label' => 'Total Expense',
                    'value' => 'Rp 0',
                    'tone' => 'danger',
                ],
                [
                    'label' => 'Net Balance',
                    'value' => 'Rp 0',
                    'tone' => 'primary',
                ],
                [
                    'label' => 'Outstanding Kasbon',
                    'value' => 'Rp 0',
                    'tone' => 'warning',
                ],
            ],
        ]);
    }
}
