<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Role;
use App\Models\User;

class UserController extends Controller
{
    private User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    public function index(): string
    {
        return $this->layout('users.index', [
            'title' => 'User Management',
            'activeMenu' => 'users',
            'users' => $this->users->all(),
            'flash' => $_GET['message'] ?? null,
        ]);
    }

    public function create(): string
    {
        return $this->layout('users.create', $this->formData([
            'title' => 'Create User',
            'user' => $this->emptyUser(),
            'errors' => [],
        ]));
    }

    public function store(): void
    {
        $data = $this->validatedData();
        $errors = $this->validate($data, true);

        if ($data['username'] !== '' && $this->users->usernameExists($data['username'])) {
            $errors['username'] = 'Username sudah digunakan.';
        }

        if ($errors !== []) {
            echo $this->layout('users.create', $this->formData([
                'title' => 'Create User',
                'user' => $data,
                'errors' => $errors,
            ]));
            return;
        }

        $this->users->create($data);
        $this->redirect('/users?message=created');
    }

    public function edit(string $id): string
    {
        return $this->layout('users.edit', $this->formData([
            'title' => 'Edit User',
            'user' => $this->findOrFail((int) $id),
            'errors' => [],
        ]));
    }

    public function update(string $id): void
    {
        $userId = (int) $id;
        $existing = $this->findOrFail($userId);
        $data = $this->validatedData(false);
        $errors = $this->validate($data, false);

        if ($data['username'] !== '' && $this->users->usernameExists($data['username'], $userId)) {
            $errors['username'] = 'Username sudah digunakan.';
        }

        if ($errors !== []) {
            echo $this->layout('users.edit', $this->formData([
                'title' => 'Edit User',
                'user' => array_merge($existing, $data),
                'errors' => $errors,
            ]));
            return;
        }

        $this->users->update($userId, $data);
        $this->redirect('/users?message=updated');
    }

    public function activate(string $id): void
    {
        $this->users->setStatus((int) $id, 1);
        $this->redirect('/users?message=activated');
    }

    public function deactivate(string $id): void
    {
        if ((int) $id === (int) ($_SESSION['user']['id'] ?? 0)) {
            $this->redirect('/users?message=self_deactivate_blocked');
        }

        $this->users->setStatus((int) $id, 0);
        $this->redirect('/users?message=deactivated');
    }

    private function formData(array $data): array
    {
        return array_merge([
            'activeMenu' => 'users',
            'roles' => (new Role())->active(),
        ], $data);
    }

    private function validatedData(bool $withPassword = true): array
    {
        return [
            'name' => trim($_POST['name'] ?? ''),
            'username' => trim($_POST['username'] ?? ''),
            'password' => (string) ($_POST['password'] ?? ''),
            'role' => trim($_POST['role'] ?? ''),
            'status' => (int) ($_POST['status'] ?? 0) === 1 ? 1 : 0,
        ];
    }

    private function validate(array $data, bool $passwordRequired): array
    {
        $errors = [];

        if ($data['name'] === '') {
            $errors['name'] = 'Name wajib diisi.';
        }

        if ($data['username'] === '') {
            $errors['username'] = 'Username wajib diisi.';
        }

        if ($passwordRequired && $data['password'] === '') {
            $errors['password'] = 'Password wajib diisi.';
        }

        if ($data['password'] !== '' && strlen($data['password']) < 6) {
            $errors['password'] = 'Password minimal 6 karakter.';
        }

        if ($data['role'] === '') {
            $errors['role'] = 'Role wajib dipilih.';
        }

        return $errors;
    }

    private function emptyUser(): array
    {
        return [
            'name' => '',
            'username' => '',
            'role' => 'viewer',
            'status' => 1,
        ];
    }

    private function findOrFail(int $id): array
    {
        $user = $this->users->find($id);

        if (! $user) {
            http_response_code(404);
            exit('404 - User not found');
        }

        return $user;
    }

    private function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }
}
