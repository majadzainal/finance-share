<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\CashAdvance;
use App\Models\Group;
use App\Models\Member;
use App\Models\TransferMethod;

class CashAdvanceController extends Controller
{
    private CashAdvance $cashAdvances;

    public function __construct()
    {
        $this->cashAdvances = new CashAdvance();
    }

    public function index(): string
    {
        return $this->layout('cash_advances.index', [
            'title' => 'Cash Advances / Kasbon',
            'activeMenu' => 'cash_advances',
            'cashAdvances' => $this->cashAdvances->outstanding(),
            'flash' => $_GET['message'] ?? null,
        ]);
    }

    public function create(): string
    {
        return $this->layout('cash_advances.create', $this->formData([
            'title' => 'Create Kasbon',
            'cashAdvance' => $this->emptyCashAdvance(),
            'errors' => [],
        ]));
    }

    public function store(): void
    {
        $data = $this->validatedData();
        $errors = $this->validate($data);

        if ($errors !== []) {
            echo $this->layout('cash_advances.create', $this->formData([
                'title' => 'Create Kasbon',
                'cashAdvance' => $data,
                'errors' => $errors,
            ]));
            return;
        }

        $id = $this->cashAdvances->create($data);
        $this->redirect('/cash-advances/' . $id . '?message=created');
    }

    public function show(string $id): string
    {
        $cashAdvance = $this->findOrFail((int) $id);
        $payments = $this->cashAdvances->payments((int) $id);

        return $this->layout('cash_advances.show', [
            'title' => 'Kasbon Detail',
            'activeMenu' => 'cash_advances',
            'cashAdvance' => $cashAdvance,
            'payments' => $payments,
            'totalPayments' => $this->cashAdvances->totalPayments((int) $id),
            'flash' => $_GET['message'] ?? null,
        ]);
    }

    private function formData(array $data): array
    {
        return array_merge([
            'activeMenu' => 'cash_advances',
            'groups' => (new Group())->all(),
            'members' => (new Member())->all(),
            'transferMethods' => (new TransferMethod())->active(),
        ], $data);
    }

    private function validatedData(): array
    {
        return [
            'group_id' => (int) ($_POST['group_id'] ?? 0),
            'member_id' => (int) ($_POST['member_id'] ?? 0),
            'advance_date' => trim($_POST['advance_date'] ?? ''),
            'amount' => (float) str_replace(',', '.', trim($_POST['amount'] ?? '0')),
            'description' => trim($_POST['description'] ?? ''),
            'status' => (int) ($_POST['status'] ?? 0) === 1 ? 1 : 0,
            'transfer_method_id' => (int) ($_POST['transfer_method_id'] ?? 0),
            'transfer_fee_amount' => (float) str_replace(',', '.', trim($_POST['transfer_fee_amount'] ?? '0')),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];

        if ($data['member_id'] <= 0) {
            $errors['member_id'] = 'Member wajib dipilih.';
        }

        if ($data['advance_date'] === '') {
            $errors['advance_date'] = 'Tanggal kasbon wajib diisi.';
        }

        if ($data['amount'] <= 0) {
            $errors['amount'] = 'Amount harus lebih dari 0.';
        }

        if ($data['description'] === '') {
            $errors['description'] = 'Description wajib diisi.';
        }

        if ($data['transfer_fee_amount'] < 0) {
            $errors['transfer_fee_amount'] = 'Biaya transfer tidak boleh minus.';
        }

        if ($data['transfer_fee_amount'] > 0 && $data['group_id'] <= 0) {
            $errors['group_id'] = 'Group/store wajib dipilih jika ada biaya transfer.';
        }

        return $errors;
    }

    private function emptyCashAdvance(): array
    {
        return [
            'group_id' => 0,
            'member_id' => 0,
            'advance_date' => date('Y-m-d'),
            'amount' => '',
            'description' => '',
            'status' => 0,
            'transfer_method_id' => 0,
            'transfer_fee_amount' => 0,
        ];
    }

    private function findOrFail(int $id): array
    {
        $cashAdvance = $this->cashAdvances->find($id);

        if (! $cashAdvance) {
            http_response_code(404);
            exit('404 - Kasbon not found');
        }

        return $cashAdvance;
    }

    private function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }
}
