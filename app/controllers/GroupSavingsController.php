<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Group;
use App\Models\GroupSavings;
use RuntimeException;

class GroupSavingsController extends Controller
{
    private GroupSavings $savings;
    private Group $groups;

    public function __construct()
    {
        $this->savings = new GroupSavings();
        $this->groups = new Group();
    }

    public function index(): string
    {
        $filters = [
            'group_id' => trim($_GET['group_id'] ?? ''),
            'type' => trim($_GET['type'] ?? ''),
            'source' => trim($_GET['source'] ?? ''),
            'start_date' => trim($_GET['start_date'] ?? ''),
            'end_date' => trim($_GET['end_date'] ?? ''),
            'search' => trim($_GET['search'] ?? ''),
        ];

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 15;

        $groupBalances = $this->savings->allGroupBalances();
        $totalAllSavings = $this->savings->totalAllBalances();
        $mutations = $this->savings->paginateMutations($filters, $page, $perPage);
        $totalMutations = $this->savings->countMutations($filters);
        $totalPages = (int) ceil($totalMutations / $perPage);

        return $this->layout('group_savings.index', [
            'title' => 'Tabungan Group / Store',
            'activeMenu' => 'group_savings',
            'groupBalances' => $groupBalances,
            'totalAllSavings' => $totalAllSavings,
            'mutations' => $mutations,
            'filters' => $filters,
            'groups' => $this->groups->all(),
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $totalMutations,
                'totalPages' => $totalPages,
            ],
            'flash' => $_GET['message'] ?? null,
            'flashError' => $_GET['error'] ?? null,
        ]);
    }

    public function show(string $id): string
    {
        $groupId = (int) $id;
        $group = $this->groups->find($groupId);

        if (! $group) {
            http_response_code(404);
            exit('404 - Group not found');
        }

        $startDate = trim($_GET['start_date'] ?? '');
        $endDate = trim($_GET['end_date'] ?? '');
        $type = trim($_GET['type'] ?? '');

        $mutations = $this->savings->getMutations($groupId, $startDate, $endDate, $type);
        $currentBalance = $this->savings->getBalance($groupId);

        $totalDeposited = 0.0;
        $totalWithdrawn = 0.0;
        foreach ($mutations as $m) {
            if ($m['type'] === 'deposit') {
                $totalDeposited += (float) $m['amount'];
            } else {
                $totalWithdrawn += (float) $m['amount'];
            }
        }

        return $this->layout('group_savings.show', [
            'title' => 'Detail Tabungan - ' . $group['name'],
            'activeMenu' => 'group_savings',
            'group' => $group,
            'currentBalance' => $currentBalance,
            'totalDeposited' => $totalDeposited,
            'totalWithdrawn' => $totalWithdrawn,
            'mutations' => $mutations,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'type' => $type,
            ],
            'flash' => $_GET['message'] ?? null,
            'flashError' => $_GET['error'] ?? null,
        ]);
    }

    public function createWithdrawal(): string
    {
        $groupId = (int) ($_GET['group_id'] ?? 0);
        $groups = $this->groups->all();

        return $this->layout('group_savings.withdraw', [
            'title' => 'Tarik / Pengeluaran Tabungan Toko',
            'activeMenu' => 'group_savings',
            'groups' => $groups,
            'selectedGroupId' => $groupId,
            'groupBalances' => $this->savings->allGroupBalances(),
            'data' => [
                'group_id' => $groupId,
                'transaction_date' => date('Y-m-d'),
                'amount' => '',
                'reference_no' => '',
                'description' => '',
            ],
            'errors' => [],
        ]);
    }

    public function storeWithdrawal(): void
    {
        $data = [
            'group_id' => (int) ($_POST['group_id'] ?? 0),
            'transaction_date' => trim($_POST['transaction_date'] ?? ''),
            'amount' => (float) str_replace(',', '.', trim($_POST['amount'] ?? '0')),
            'reference_no' => trim($_POST['reference_no'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
        ];

        $errors = [];
        if ($data['group_id'] <= 0) {
            $errors['group_id'] = 'Pilih group/store terlebih dahulu.';
        }
        if ($data['transaction_date'] === '') {
            $errors['transaction_date'] = 'Tanggal penarikan wajib diisi.';
        }
        if ($data['amount'] <= 0) {
            $errors['amount'] = 'Nominal penarikan harus lebih dari 0.';
        }
        if ($data['description'] === '') {
            $errors['description'] = 'Keterangan pengeluaran tabungan wajib diisi.';
        }

        if ($data['group_id'] > 0 && $data['amount'] > 0) {
            $currentBalance = $this->savings->getBalance($data['group_id']);
            if ($data['amount'] > $currentBalance) {
                $errors['amount'] = 'Saldo tabungan tidak mencukupi. Saldo saat ini: Rp ' . number_format($currentBalance, 0, ',', '.');
            }
        }

        if ($errors !== []) {
            echo $this->layout('group_savings.withdraw', [
                'title' => 'Tarik / Pengeluaran Tabungan Toko',
                'activeMenu' => 'group_savings',
                'groups' => $this->groups->all(),
                'selectedGroupId' => $data['group_id'],
                'groupBalances' => $this->savings->allGroupBalances(),
                'data' => $data,
                'errors' => $errors,
            ]);
            return;
        }

        try {
            $createdBy = $_SESSION['user']['username'] ?? 'admin';
            $this->savings->recordWithdrawal(
                $data['group_id'],
                $data['amount'],
                $data['transaction_date'],
                $data['description'],
                null,
                $data['reference_no'] ?: null,
                $createdBy
            );

            $this->redirect('/group-savings/' . $data['group_id'] . '?message=withdrawn');
        } catch (RuntimeException $e) {
            echo $this->layout('group_savings.withdraw', [
                'title' => 'Tarik / Pengeluaran Tabungan Toko',
                'activeMenu' => 'group_savings',
                'groups' => $this->groups->all(),
                'selectedGroupId' => $data['group_id'],
                'groupBalances' => $this->savings->allGroupBalances(),
                'data' => $data,
                'errors' => ['general' => $e->getMessage()],
            ]);
        }
    }

    public function createDeposit(): string
    {
        $groupId = (int) ($_GET['group_id'] ?? 0);
        $groups = $this->groups->all();

        return $this->layout('group_savings.deposit', [
            'title' => 'Setor Tabungan Toko',
            'activeMenu' => 'group_savings',
            'groups' => $groups,
            'selectedGroupId' => $groupId,
            'groupBalances' => $this->savings->allGroupBalances(),
            'data' => [
                'group_id' => $groupId,
                'transaction_date' => date('Y-m-d'),
                'amount' => '',
                'reference_no' => '',
                'description' => '',
            ],
            'errors' => [],
        ]);
    }

    public function storeDeposit(): void
    {
        $data = [
            'group_id' => (int) ($_POST['group_id'] ?? 0),
            'transaction_date' => trim($_POST['transaction_date'] ?? ''),
            'amount' => (float) str_replace(',', '.', trim($_POST['amount'] ?? '0')),
            'reference_no' => trim($_POST['reference_no'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
        ];

        $errors = [];
        if ($data['group_id'] <= 0) {
            $errors['group_id'] = 'Pilih group/store terlebih dahulu.';
        }
        if ($data['transaction_date'] === '') {
            $errors['transaction_date'] = 'Tanggal setoran wajib diisi.';
        }
        if ($data['amount'] <= 0) {
            $errors['amount'] = 'Nominal setoran harus lebih dari 0.';
        }

        if ($errors !== []) {
            echo $this->layout('group_savings.deposit', [
                'title' => 'Setor Tabungan Toko',
                'activeMenu' => 'group_savings',
                'groups' => $this->groups->all(),
                'selectedGroupId' => $data['group_id'],
                'groupBalances' => $this->savings->allGroupBalances(),
                'data' => $data,
                'errors' => $errors,
            ]);
            return;
        }

        try {
            $createdBy = $_SESSION['user']['username'] ?? 'admin';
            $this->savings->recordDeposit(
                $data['group_id'],
                $data['amount'],
                $data['transaction_date'],
                'manual_deposit',
                null,
                $data['description'] ?: 'Setoran manual tabungan toko',
                $data['reference_no'] ?: null,
                $createdBy
            );

            $this->redirect('/group-savings/' . $data['group_id'] . '?message=deposited');
        } catch (RuntimeException $e) {
            echo $this->layout('group_savings.deposit', [
                'title' => 'Setor Tabungan Toko',
                'activeMenu' => 'group_savings',
                'groups' => $this->groups->all(),
                'selectedGroupId' => $data['group_id'],
                'groupBalances' => $this->savings->allGroupBalances(),
                'data' => $data,
                'errors' => ['general' => $e->getMessage()],
            ]);
        }
    }

    private function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }
}
