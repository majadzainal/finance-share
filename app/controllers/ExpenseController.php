<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Group;
use App\Models\TransferMethod;

class ExpenseController extends Controller
{
    private Expense $expenses;

    public function __construct()
    {
        $this->expenses = new Expense();
    }

    public function index(): string
    {
        $filters = $this->filters();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = $this->perPage();
        $totalRows = $this->expenses->count($filters);
        $totalPages = $perPage === 'all' ? 1 : max(1, (int) ceil($totalRows / $perPage));
        $page = min($page, $totalPages);

        return $this->layout('expenses.index', [
            'title' => 'Expenses',
            'activeMenu' => 'expenses',
            'groups' => (new Group())->all(),
            'filters' => $filters,
            'expenses' => $this->expenses->paginate($filters, $page, $perPage),
            'totalRows' => $totalRows,
            'totalPages' => $totalPages,
            'page' => $page,
            'perPage' => $perPage,
            'sort' => $this->sort(),
            'rowStart' => $perPage === 'all' ? 1 : (($page - 1) * $perPage) + 1,
            'totalExpense' => $this->expenses->totalAmount($filters),
            'flash' => $_GET['message'] ?? null,
            'error' => $_GET['error'] ?? null,
        ]);
    }

    public function create(): string
    {
        return $this->layout('expenses.create', $this->formData([
            'title' => 'Create Expense',
            'expense' => $this->emptyExpense(),
            'errors' => [],
        ]));
    }

    public function store(): void
    {
        $data = $this->validatedData();
        $errors = $this->validate($data);

        if ($errors !== []) {
            echo $this->layout('expenses.create', $this->formData([
                'title' => 'Create Expense',
                'expense' => $data,
                'errors' => $errors,
            ]));
            return;
        }

        $this->expenses->create($data);
        $this->redirect('/expenses?message=created');
    }

    public function edit(string $id): string
    {
        $expense = $this->findOrFail((int) $id);

        if ($expense['closing_id'] !== null) {
            $this->redirect('/expenses?error=' . urlencode('Expense locked tidak boleh diedit.'));
        }

        return $this->layout('expenses.edit', $this->formData([
            'title' => 'Edit Expense',
            'expense' => $expense,
            'errors' => [],
        ]));
    }

    public function update(string $id): void
    {
        $expenseId = (int) $id;
        $expense = $this->findOrFail($expenseId);

        if ($expense['closing_id'] !== null) {
            $this->redirect('/expenses?error=' . urlencode('Expense locked tidak boleh diedit.'));
        }

        $data = $this->validatedData();
        $errors = $this->validate($data);

        if ($errors !== []) {
            echo $this->layout('expenses.edit', $this->formData([
                'title' => 'Edit Expense',
                'expense' => array_merge($expense, $data),
                'errors' => $errors,
            ]));
            return;
        }

        $this->expenses->update($expenseId, $data);
        $this->redirect('/expenses?message=updated');
    }

    public function destroy(string $id): void
    {
        $expense = $this->findOrFail((int) $id);

        if ($expense['closing_id'] !== null) {
            $this->redirect('/expenses?error=' . urlencode('Expense locked tidak boleh dihapus.'));
        }

        $this->expenses->delete((int) $id);
        $this->redirect('/expenses?message=deleted');
    }

    private function filters(): array
    {
        return [
            'group_id' => (int) ($_GET['group_id'] ?? 0),
            'date_from' => trim($_GET['date_from'] ?? date('Y-m-01')),
            'date_to' => trim($_GET['date_to'] ?? date('Y-m-d')),
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
        $allowed = ['expense_date', 'group_name', 'category_name', 'description', 'created_by', 'transfer_method', 'amount', 'status'];
        $by = trim($_GET['sort_by'] ?? 'expense_date');
        $dir = strtolower(trim($_GET['sort_dir'] ?? 'desc'));

        return [
            'by' => in_array($by, $allowed, true) ? $by : 'expense_date',
            'dir' => $dir === 'asc' ? 'asc' : 'desc',
        ];
    }

    private function formData(array $data): array
    {
        return array_merge([
            'activeMenu' => 'expenses',
            'groups' => (new Group())->all(),
            'categories' => (new ExpenseCategory())->active(),
            'transferMethods' => (new TransferMethod())->active(),
        ], $data);
    }

    private function validatedData(): array
    {
        return [
            'group_id' => (int) ($_POST['group_id'] ?? 0),
            'category_id' => (int) ($_POST['category_id'] ?? 0),
            'expense_date' => trim($_POST['expense_date'] ?? ''),
            'amount' => (float) str_replace(',', '.', trim($_POST['amount'] ?? '0')),
            'description' => trim($_POST['description'] ?? ''),
            'created_by' => trim($_POST['created_by'] ?? ''),
            'transfer_method_id' => (int) ($_POST['transfer_method_id'] ?? 0),
            'transfer_fee_amount' => (float) str_replace(',', '.', trim($_POST['transfer_fee_amount'] ?? '0')),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];

        if ($data['group_id'] <= 0) {
            $errors['group_id'] = 'Group/store wajib dipilih.';
        }

        if ($data['category_id'] <= 0) {
            $errors['category_id'] = 'Category wajib dipilih.';
        }

        if ($data['expense_date'] === '') {
            $errors['expense_date'] = 'Tanggal expense wajib diisi.';
        }

        if ($data['amount'] <= 0) {
            $errors['amount'] = 'Amount harus lebih dari 0.';
        }

        if ($data['description'] === '') {
            $errors['description'] = 'Description wajib diisi.';
        }

        if ($data['created_by'] === '') {
            $errors['created_by'] = 'Created by wajib diisi.';
        }

        if ($data['transfer_fee_amount'] < 0) {
            $errors['transfer_fee_amount'] = 'Biaya transfer tidak boleh minus.';
        }

        return $errors;
    }

    private function emptyExpense(): array
    {
        return [
            'group_id' => 0,
            'category_id' => 0,
            'expense_date' => date('Y-m-d'),
            'amount' => '',
            'description' => '',
            'created_by' => '',
            'transfer_method_id' => 0,
            'transfer_fee_amount' => 0,
        ];
    }

    private function findOrFail(int $id): array
    {
        $expense = $this->expenses->find($id);

        if (! $expense) {
            http_response_code(404);
            exit('404 - Expense not found');
        }

        return $expense;
    }

    private function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }
}
