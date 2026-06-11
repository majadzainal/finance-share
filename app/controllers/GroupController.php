<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Group;

class GroupController extends Controller
{
    private Group $groups;

    public function __construct()
    {
        $this->groups = new Group();
    }

    public function index(): string
    {
        return $this->layout('groups.index', [
            'title' => 'Groups / Store',
            'activeMenu' => 'groups',
            'groups' => $this->groups->all(),
            'flash' => $_GET['message'] ?? null,
        ]);
    }

    public function create(): string
    {
        return $this->layout('groups.create', [
            'title' => 'Create Group',
            'activeMenu' => 'groups',
            'group' => ['code' => '', 'name' => '', 'is_active' => 1],
            'errors' => [],
        ]);
    }

    public function store(): void
    {
        $data = $this->validatedData();
        $errors = $this->validateRequired($data);

        if ($data['code'] !== '' && $this->groups->codeExists($data['code'])) {
            $errors['code'] = 'Code sudah digunakan.';
        }

        if ($errors !== []) {
            $this->renderCreateWithErrors($data, $errors);
            return;
        }

        $this->groups->create($data);
        $this->redirect('/groups?message=created');
    }

    public function show(string $id): string
    {
        $group = $this->findOrFail((int) $id);

        return $this->layout('groups.show', [
            'title' => 'Group Detail',
            'activeMenu' => 'groups',
            'group' => $group,
        ]);
    }

    public function edit(string $id): string
    {
        $group = $this->findOrFail((int) $id);

        return $this->layout('groups.edit', [
            'title' => 'Edit Group',
            'activeMenu' => 'groups',
            'group' => $group,
            'errors' => [],
        ]);
    }

    public function update(string $id): void
    {
        $groupId = (int) $id;
        $this->findOrFail($groupId);
        $data = $this->validatedData();
        $errors = $this->validateRequired($data);

        if ($data['code'] !== '' && $this->groups->codeExists($data['code'], $groupId)) {
            $errors['code'] = 'Code sudah digunakan.';
        }

        if ($errors !== []) {
            $this->renderEditWithErrors($groupId, $data, $errors);
            return;
        }

        $this->groups->update($groupId, $data);
        $this->redirect('/groups?message=updated');
    }

    public function activate(string $id): void
    {
        $this->groups->setStatus((int) $id, 1);
        $this->redirect('/groups?message=activated');
    }

    public function deactivate(string $id): void
    {
        $this->groups->setStatus((int) $id, 0);
        $this->redirect('/groups?message=deactivated');
    }

    private function validatedData(): array
    {
        return [
            'code' => strtoupper(trim($_POST['code'] ?? '')),
            'name' => trim($_POST['name'] ?? ''),
            'status' => (int) ($_POST['status'] ?? 0) === 1 ? 1 : 0,
        ];
    }

    private function validateRequired(array $data): array
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

    private function renderCreateWithErrors(array $data, array $errors): void
    {
        echo $this->layout('groups.create', [
            'title' => 'Create Group',
            'activeMenu' => 'groups',
            'group' => [
                'code' => $data['code'],
                'name' => $data['name'],
                'is_active' => $data['status'],
            ],
            'errors' => $errors,
        ]);
    }

    private function renderEditWithErrors(int $id, array $data, array $errors): void
    {
        echo $this->layout('groups.edit', [
            'title' => 'Edit Group',
            'activeMenu' => 'groups',
            'group' => [
                'id' => $id,
                'code' => $data['code'],
                'name' => $data['name'],
                'is_active' => $data['status'],
            ],
            'errors' => $errors,
        ]);
    }

    private function findOrFail(int $id): array
    {
        $group = $this->groups->find($id);

        if (! $group) {
            http_response_code(404);
            exit('404 - Group not found');
        }

        return $group;
    }

    private function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }
}
