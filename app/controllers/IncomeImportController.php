<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Group;
use App\Services\IncomeImportService;
use RuntimeException;
use Throwable;

class IncomeImportController extends Controller
{
    private IncomeImportService $service;

    public function __construct()
    {
        $this->service = new IncomeImportService();
    }

    public function index(): string
    {
        return $this->layout('income_imports.index', $this->viewData());
    }

    public function store(): string
    {
        $result = null;
        $error = null;

        try {
            $result = $this->service->import((int) ($_POST['group_id'] ?? 0), $_FILES['income_file'] ?? []);
        } catch (RuntimeException $exception) {
            $error = $exception->getMessage();
        } catch (Throwable $exception) {
            $error = 'Import gagal diproses.';
        }

        return $this->layout('income_imports.index', $this->viewData([
            'result' => $result,
            'error' => $error,
            'selectedGroupId' => (int) ($_POST['group_id'] ?? 0),
        ]));
    }

    private function viewData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Import Income',
            'activeMenu' => 'import_income',
            'groups' => (new Group())->all(),
            'imports' => $this->service->recentImports(),
            'result' => null,
            'error' => null,
            'selectedGroupId' => 0,
        ], $overrides);
    }
}
