<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Group;
use App\Models\Income;

class IncomeController extends Controller
{
    private Income $incomes;

    public function __construct()
    {
        $this->incomes = new Income();
    }

    public function index(): string
    {
        $filters = $this->filters();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = $this->perPage();
        $totalRows = $this->incomes->count($filters);
        $totalPages = $perPage === 'all' ? 1 : max(1, (int) ceil($totalRows / $perPage));
        $page = min($page, $totalPages);

        return $this->layout('incomes.index', [
            'title' => 'Income',
            'activeMenu' => 'income',
            'groups' => (new Group())->all(),
            'filters' => $filters,
            'incomes' => $this->incomes->paginate($filters, $page, $perPage),
            'totalRows' => $totalRows,
            'totalPages' => $totalPages,
            'page' => $page,
            'perPage' => $perPage,
            'sort' => $this->sort(),
            'rowStart' => $perPage === 'all' ? 1 : (($page - 1) * $perPage) + 1,
            'totalAmount' => $this->incomes->totalAmount($filters),
            'flash' => $_GET['message'] ?? null,
            'error' => $_GET['error'] ?? null,
        ]);
    }

    public function edit(string $id): string
    {
        $income = $this->findOrFail((int) $id);

        if ($income['closing_id'] !== null) {
            $this->redirect('/income?error=' . urlencode('Income locked tidak boleh diedit.'));
        }

        return $this->layout('incomes.edit', [
            'title' => 'Edit Income',
            'activeMenu' => 'income',
            'income' => $income,
            'errors' => [],
        ]);
    }

    public function update(string $id): void
    {
        $incomeId = (int) $id;
        $income = $this->findOrFail($incomeId);

        if ($income['closing_id'] !== null) {
            $this->redirect('/income?error=' . urlencode('Income locked tidak boleh diedit.'));
        }

        $data = $this->validatedData();
        $errors = $this->validate($data);

        if ($errors !== []) {
            echo $this->layout('incomes.edit', [
                'title' => 'Edit Income',
                'activeMenu' => 'income',
                'income' => array_merge($income, $data),
                'errors' => $errors,
            ]);
            return;
        }

        $this->incomes->update($incomeId, $data);
        $this->redirect('/income?message=updated');
    }

    public function destroy(string $id): void
    {
        $income = $this->findOrFail((int) $id);

        if ($income['closing_id'] !== null) {
            $this->redirect('/income?error=' . urlencode('Income locked tidak boleh dihapus.'));
        }

        $this->incomes->delete((int) $id);
        $this->redirect('/income?message=deleted');
    }

    private function filters(): array
    {
        return [
            'group_id' => (int) ($_GET['group_id'] ?? 0),
            'date_from' => trim($_GET['date_from'] ?? date('Y-m-01')),
            'date_to' => trim($_GET['date_to'] ?? date('Y-m-d')),
            'search' => trim($_GET['search'] ?? ''),
            'per_page' => $this->perPage(),
            'sort_by' => $this->sort()['by'],
            'sort_dir' => $this->sort()['dir'],
        ];
    }

    private function perPage(): int|string
    {
        $perPage = (string) ($_GET['per_page'] ?? '20');

        if ($perPage === 'all') {
            return 'all';
        }

        $allowed = [10, 20, 50];
        $value = (int) $perPage;

        return in_array($value, $allowed, true) ? $value : 20;
    }

    private function sort(): array
    {
        $allowed = ['transaction_date', 'group_name', 'reference_no', 'client_name', 'username', 'payment_method', 'amount', 'status'];
        $by = trim($_GET['sort_by'] ?? 'transaction_date');
        $dir = strtolower(trim($_GET['sort_dir'] ?? 'desc'));

        return [
            'by' => in_array($by, $allowed, true) ? $by : 'transaction_date',
            'dir' => $dir === 'asc' ? 'asc' : 'desc',
        ];
    }

    private function validatedData(): array
    {
        return [
            'transaction_date' => trim($_POST['transaction_date'] ?? ''),
            'reference_no' => trim($_POST['reference_no'] ?? ''),
            'payment_method' => trim($_POST['payment_method'] ?? ''),
            'bank_target' => trim($_POST['bank_target'] ?? ''),
            'income_type' => trim($_POST['income_type'] ?? ''),
            'client_name' => trim($_POST['client_name'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'username' => trim($_POST['username'] ?? ''),
            'profile_package' => trim($_POST['profile_package'] ?? ''),
            'amount' => (float) str_replace(',', '.', trim($_POST['amount'] ?? '0')),
            'description' => trim($_POST['description'] ?? ''),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];

        if ($data['transaction_date'] === '') {
            $errors['transaction_date'] = 'Tanggal wajib diisi.';
        }

        if ($data['reference_no'] === '') {
            $errors['reference_no'] = 'Reference wajib diisi.';
        }

        if ($data['client_name'] === '') {
            $errors['client_name'] = 'Client name wajib diisi.';
        }

        if ($data['amount'] <= 0) {
            $errors['amount'] = 'Amount harus lebih dari 0.';
        }

        return $errors;
    }

    private function findOrFail(int $id): array
    {
        $income = $this->incomes->find($id);

        if (! $income) {
            http_response_code(404);
            exit('404 - Income not found');
        }

        return $income;
    }

    private function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }
}
