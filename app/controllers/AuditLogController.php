<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    private AuditLog $logs;

    public function __construct()
    {
        $this->logs = new AuditLog();
    }

    public function index(): string
    {
        $filters = [
            'module' => trim($_GET['module'] ?? ''),
            'event' => trim($_GET['event'] ?? ''),
            'date_from' => trim($_GET['date_from'] ?? ''),
            'date_to' => trim($_GET['date_to'] ?? ''),
        ];
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 30;
        $totalRows = $this->logs->count($filters);

        return $this->layout('audit_logs.index', [
            'title' => 'Audit Trail',
            'activeMenu' => 'audit_logs',
            'filters' => $filters,
            'logs' => $this->logs->paginate($filters, $page, $perPage),
            'page' => $page,
            'totalPages' => max(1, (int) ceil($totalRows / $perPage)),
            'totalRows' => $totalRows,
        ]);
    }
}
