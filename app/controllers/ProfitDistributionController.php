<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Group;
use App\Models\ProfitDistribution;

class ProfitDistributionController extends Controller
{
    private ProfitDistribution $profitDistributions;

    public function __construct()
    {
        $this->profitDistributions = new ProfitDistribution();
    }

    public function index(): string
    {
        $filters = [
            'group_id' => (int) ($_GET['group_id'] ?? 0),
            'period_start' => trim($_GET['period_start'] ?? ''),
            'period_end' => trim($_GET['period_end'] ?? ''),
        ];

        return $this->layout('profit_distributions.index', [
            'title' => 'Profit Distribution',
            'activeMenu' => 'profit_distribution',
            'groups' => (new Group())->all(),
            'filters' => $filters,
            'closings' => $this->profitDistributions->closings($filters),
            'flash' => $_GET['message'] ?? null,
        ]);
    }

    public function show(string $id): string
    {
        $closing = $this->findClosingOrFail((int) $id);

        return $this->layout('profit_distributions.show', [
            'title' => 'Distribution Detail',
            'activeMenu' => 'profit_distribution',
            'closing' => $closing,
            'distributions' => $this->profitDistributions->distributions((int) $id),
            'flash' => $_GET['message'] ?? null,
        ]);
    }

    public function markPaid(string $id): void
    {
        $closingId = $this->profitDistributions->markPaid((int) $id);

        if ($closingId === null) {
            http_response_code(404);
            exit('404 - Distribution not found');
        }

        $this->redirect('/profit-distribution/' . $closingId . '?message=paid');
    }

    private function findClosingOrFail(int $id): array
    {
        $closing = $this->profitDistributions->closing($id);

        if (! $closing) {
            http_response_code(404);
            exit('404 - Closing not found');
        }

        return $closing;
    }

    private function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }
}
