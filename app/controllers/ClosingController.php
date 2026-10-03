<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Group;
use App\Models\TransferMethod;
use App\Services\ClosingService;
use App\Services\ClosingPreviewService;
use RuntimeException;

class ClosingController extends Controller
{
    private ClosingPreviewService $service;

    public function __construct()
    {
        $this->service = new ClosingPreviewService();
    }

    public function index(): string
    {
        return $this->layout('closings.preview', $this->viewData());
    }

    public function preview(): string
    {
        $input = [
            'group_id' => (int) ($_POST['group_id'] ?? 0),
            'period_start' => trim($_POST['period_start'] ?? ''),
            'period_end' => trim($_POST['period_end'] ?? ''),
            'transfer_method_id' => (int) ($_POST['transfer_method_id'] ?? 0),
            'transfer_fee_amount' => parse_money($_POST['transfer_fee_amount'] ?? '0'),
            'savings_amount' => parse_money($_POST['savings_amount'] ?? '0'),
        ];
        $preview = null;
        $error = null;

        try {
            $preview = $this->service->preview(
                $input['group_id'],
                $input['period_start'],
                $input['period_end'],
                $input['transfer_fee_amount'],
                $input['savings_amount']
            );
        } catch (RuntimeException $exception) {
            $error = $exception->getMessage();
        }

        return $this->layout('closings.preview', $this->viewData([
            'input' => $input,
            'preview' => $preview,
            'error' => $error,
        ]));
    }

    public function finalize(): string
    {
        $input = [
            'group_id' => (int) ($_POST['group_id'] ?? 0),
            'period_start' => trim($_POST['period_start'] ?? ''),
            'period_end' => trim($_POST['period_end'] ?? ''),
            'transfer_method_id' => (int) ($_POST['transfer_method_id'] ?? 0),
            'transfer_fee_amount' => parse_money($_POST['transfer_fee_amount'] ?? '0'),
            'savings_amount' => parse_money($_POST['savings_amount'] ?? '0'),
        ];
        $preview = null;
        $error = null;
        $success = null;

        try {
            $closingId = (new ClosingService())->finalize(
                $input['group_id'],
                $input['period_start'],
                $input['period_end'],
                $_SESSION['user']['username'] ?? 'admin',
                $input['transfer_method_id'],
                $input['transfer_fee_amount'],
                $input['savings_amount']
            );
            $success = 'Closing berhasil diproses dengan ID #' . $closingId . '.';
        } catch (RuntimeException $exception) {
            $error = $exception->getMessage();
            $preview = $this->safePreview($input);
        }

        return $this->layout('closings.preview', $this->viewData([
            'input' => $input,
            'preview' => $preview,
            'error' => $error,
            'success' => $success,
        ]));
    }

    private function viewData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Closing Preview',
            'activeMenu' => 'closing',
            'groups' => (new Group())->all(),
            'transferMethods' => (new TransferMethod())->active(),
            'input' => [
                'group_id' => 0,
                'period_start' => date('Y-m-01'),
                'period_end' => date('Y-m-t'),
                'transfer_method_id' => 0,
                'transfer_fee_amount' => 0,
                'savings_amount' => 0,
            ],
            'preview' => null,
            'error' => null,
            'success' => null,
        ], $overrides);
    }

    private function safePreview(array $input): ?array
    {
        try {
            return $this->service->preview(
                $input['group_id'],
                $input['period_start'],
                $input['period_end'],
                (float) ($input['transfer_fee_amount'] ?? 0),
                (float) ($input['savings_amount'] ?? 0)
            );
        } catch (RuntimeException) {
            return null;
        }
    }
}
