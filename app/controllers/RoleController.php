<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Role;

class RoleController extends Controller
{
    private Role $roles;

    public function __construct()
    {
        $this->roles = new Role();
    }

    public function index(): string
    {
        return $this->layout('roles.index', [
            'title' => 'Roles',
            'activeMenu' => 'roles',
            'roles' => $this->roles->all(),
            'flash' => $_GET['message'] ?? null,
        ]);
    }

    public function create(): string
    {
        return $this->layout('roles.create', [
            'title' => 'Create Role',
            'activeMenu' => 'roles',
            'role' => ['code' => '', 'name' => '', 'description' => '', 'is_active' => 1],
            'errors' => [],
        ]);
    }

    public function store(): void
    {
        $data = $this->validatedData();
        $errors = $this->validate($data);

        if ($data['code'] !== '' && $this->roles->codeExists($data['code'])) {
            $errors['code'] = 'Code sudah digunakan.';
        }

        if ($errors !== []) {
            echo $this->layout('roles.create', [
                'title' => 'Create Role',
                'activeMenu' => 'roles',
                'role' => $data,
                'errors' => $errors,
            ]);
            return;
        }

        $this->roles->create($data);
        $this->redirect('/roles?message=created');
    }

    public function edit(string $id): string
    {
        return $this->layout('roles.edit', [
            'title' => 'Edit Role',
            'activeMenu' => 'roles',
            'role' => $this->findOrFail((int) $id),
            'errors' => [],
        ]);
    }

    public function update(string $id): void
    {
        $roleId = (int) $id;
        $this->findOrFail($roleId);
        $data = $this->validatedData();
        $errors = $this->validate($data);

        if ($data['code'] !== '' && $this->roles->codeExists($data['code'], $roleId)) {
            $errors['code'] = 'Code sudah digunakan.';
        }

        if ($errors !== []) {
            echo $this->layout('roles.edit', [
                'title' => 'Edit Role',
                'activeMenu' => 'roles',
                'role' => array_merge(['id' => $roleId], $data),
                'errors' => $errors,
            ]);
            return;
        }

        $this->roles->update($roleId, $data);
        $this->redirect('/roles?message=updated');
    }

    public function activate(string $id): void
    {
        $this->roles->setStatus((int) $id, 1);
        $this->redirect('/roles?message=activated');
    }

    public function deactivate(string $id): void
    {
        $this->roles->setStatus((int) $id, 0);
        $this->redirect('/roles?message=deactivated');
    }

    private function validatedData(): array
    {
        return [
            'code' => strtolower(trim($_POST['code'] ?? '')),
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'status' => (int) ($_POST['status'] ?? 0) === 1 ? 1 : 0,
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];

        if ($data['code'] === '') {
            $errors['code'] = 'Code wajib diisi.';
        }

        if ($data['name'] === '') {
            $errors['name'] = 'Name wajib diisi.';
        }

        return $errors;
    }

    private function findOrFail(int $id): array
    {
        $role = $this->roles->find($id);

        if (! $role) {
            http_response_code(404);
            exit('404 - Role not found');
        }

        return $role;
    }

    private function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }
}
