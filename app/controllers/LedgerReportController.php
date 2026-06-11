<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Group;
use App\Models\Ledger;
use App\Models\Member;

class LedgerReportController extends Controller
{
    private Ledger $ledger;

    public function __construct()
    {
        $this->ledger = new Ledger();
    }

    public function index(): string
    {
        $filters = $this->filters();

        return $this->layout('ledger_reports.index', [
            'title' => 'Ledger Report',
            'activeMenu' => 'ledger_report',
            'groups' => (new Group())->all(),
            'members' => (new Member())->all(),
            'filters' => $filters,
            'rows' => $this->ledger->report($filters),
        ]);
    }

    public function export(): void
    {
        $rows = $this->ledger->report($this->filters());

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="ledger-report-' . date('YmdHis') . '.csv"');

        $output = fopen('php://output', 'wb');
        fputcsv($output, ['trx_date', 'reference_type', 'reference_id', 'description', 'debit', 'credit', 'running_balance']);

        foreach ($rows as $row) {
            fputcsv($output, [
                $row['trx_date'],
                $row['reference_type'],
                $row['reference_id'],
                $row['description'],
                $row['debit'],
                $row['credit'],
                $row['running_balance'],
            ]);
        }

        fclose($output);
    }

    private function filters(): array
    {
        return [
            'group_id' => (int) ($_GET['group_id'] ?? 0),
            'member_id' => (int) ($_GET['member_id'] ?? 0),
            'date_from' => trim($_GET['date_from'] ?? ''),
            'date_to' => trim($_GET['date_to'] ?? ''),
        ];
    }
}
