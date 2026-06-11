<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\BalanceAccount;

class AccountBalanceController extends Controller
{
    private BalanceAccount $accounts;

    public function __construct()
    {
        $this->accounts = new BalanceAccount();
    }

    public function index(): string
    {
        return $this->layout('account_balances.index', $this->viewData([
            'flash' => $_GET['message'] ?? null,
            'errors' => [],
        ]));
    }

    public function update(): void
    {
        $accounts = $this->accounts->all();
        $data = $this->validatedRows($accounts);
        $errors = $this->validate($data);

        if ($errors !== []) {
            echo $this->layout('account_balances.index', $this->viewData([
                'accounts' => $this->mergeInput($accounts, $data),
                'errors' => $errors,
                'flash' => null,
            ]));
            return;
        }

        $this->accounts->updateBalances($data);
        $this->redirect('/account-balances?message=updated');
    }

    private function viewData(array $data = []): array
    {
        $accounts = $data['accounts'] ?? $this->accounts->all();
        $manualTotal = $this->sumAccounts($accounts);
        $expectedBalance = $this->accounts->expectedBalance();
        $difference = $manualTotal - $expectedBalance;

        return array_merge([
            'title' => 'Saldo Rekening',
            'activeMenu' => 'account_balances',
            'accounts' => $accounts,
            'manualTotal' => $manualTotal,
            'expectedBalance' => $expectedBalance,
            'difference' => $difference,
            'isBalanced' => abs($difference) < 0.01,
        ], $data);
    }

    private function validatedRows(array $accounts): array
    {
        $balances = $_POST['balances'] ?? [];
        $notes = $_POST['notes'] ?? [];
        $rows = [];

        foreach ($accounts as $account) {
            $id = (int) $account['id'];
            $rows[] = [
                'id' => $id,
                'current_balance' => (float) str_replace(',', '.', trim((string) ($balances[$id] ?? '0'))),
                'notes' => trim((string) ($notes[$id] ?? '')),
            ];
        }

        return $rows;
    }

    private function validate(array $rows): array
    {
        $errors = [];

        foreach ($rows as $row) {
            if ($row['current_balance'] < 0) {
                $errors[$row['id']] = 'Saldo tidak boleh minus.';
            }
        }

        return $errors;
    }

    private function mergeInput(array $accounts, array $rows): array
    {
        $inputById = [];

        foreach ($rows as $row) {
            $inputById[$row['id']] = $row;
        }

        foreach ($accounts as &$account) {
            $id = (int) $account['id'];

            if (isset($inputById[$id])) {
                $account['current_balance'] = $inputById[$id]['current_balance'];
                $account['notes'] = $inputById[$id]['notes'];
            }
        }

        return $accounts;
    }

    private function sumAccounts(array $accounts): float
    {
        return array_reduce(
            $accounts,
            fn (float $total, array $account): float => $total + (float) $account['current_balance'],
            0.0
        );
    }

    private function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }
}
