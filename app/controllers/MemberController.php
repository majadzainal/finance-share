<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Member;

class MemberController extends Controller
{
    private Member $members;

    public function __construct()
    {
        $this->members = new Member();
    }

    public function index(): string
    {
        $search = trim($_GET['search'] ?? '');

        return $this->layout('members.index', [
            'title' => 'Members',
            'activeMenu' => 'members',
            'members' => $this->members->all($search),
            'search' => $search,
            'flash' => $_GET['message'] ?? null,
        ]);
    }

    public function create(): string
    {
        return $this->layout('members.create', [
            'title' => 'Create Member',
            'activeMenu' => 'members',
            'member' => $this->emptyMember(),
            'errors' => [],
        ]);
    }

    public function store(): void
    {
        $data = $this->validatedData();
        $errors = $this->validate($data);

        if ($errors !== []) {
            $this->renderCreateWithErrors($data, $errors);
            return;
        }

        $this->members->create($data);
        $this->redirect('/members?message=created');
    }

    public function edit(string $id): string
    {
        return $this->layout('members.edit', [
            'title' => 'Edit Member',
            'activeMenu' => 'members',
            'member' => $this->findOrFail((int) $id),
            'errors' => [],
        ]);
    }

    public function update(string $id): void
    {
        $memberId = (int) $id;
        $this->findOrFail($memberId);
        $data = $this->validatedData();
        $errors = $this->validate($data);

        if ($errors !== []) {
            $this->renderEditWithErrors($memberId, $data, $errors);
            return;
        }

        $this->members->update($memberId, $data);
        $this->redirect('/members?message=updated');
    }

    public function deactivate(string $id): void
    {
        $this->members->setStatus((int) $id, 0);
        $this->redirect('/members?message=deactivated');
    }

    public function activate(string $id): void
    {
        $this->members->setStatus((int) $id, 1);
        $this->redirect('/members?message=activated');
    }

    private function validatedData(): array
    {
        return [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'nickname' => trim($_POST['nickname'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'status' => (int) ($_POST['status'] ?? 0) === 1 ? 1 : 0,
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];

        if ($data['full_name'] === '') {
            $errors['full_name'] = 'Full name wajib diisi.';
        }

        if ($data['nickname'] === '') {
            $errors['nickname'] = 'Nickname wajib diisi.';
        }

        if ($data['phone'] !== '' && ! preg_match('/^[0-9+\-\s()]{6,50}$/', $data['phone'])) {
            $errors['phone'] = 'Phone hanya boleh angka, spasi, +, -, dan tanda kurung.';
        }

        return $errors;
    }

    private function renderCreateWithErrors(array $data, array $errors): void
    {
        echo $this->layout('members.create', [
            'title' => 'Create Member',
            'activeMenu' => 'members',
            'member' => $this->memberFromData($data),
            'errors' => $errors,
        ]);
    }

    private function renderEditWithErrors(int $id, array $data, array $errors): void
    {
        echo $this->layout('members.edit', [
            'title' => 'Edit Member',
            'activeMenu' => 'members',
            'member' => array_merge(['id' => $id], $this->memberFromData($data)),
            'errors' => $errors,
        ]);
    }

    private function emptyMember(): array
    {
        return [
            'full_name' => '',
            'nickname' => '',
            'phone' => '',
            'is_active' => 1,
        ];
    }

    private function memberFromData(array $data): array
    {
        return [
            'full_name' => $data['full_name'],
            'nickname' => $data['nickname'],
            'phone' => $data['phone'],
            'is_active' => $data['status'],
        ];
    }

    private function findOrFail(int $id): array
    {
        $member = $this->members->find($id);

        if (! $member) {
            http_response_code(404);
            exit('404 - Member not found');
        }

        return $member;
    }

    private function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }
}
