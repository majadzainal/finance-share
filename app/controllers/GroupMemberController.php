<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Member;
use App\Services\GroupMemberService;

class GroupMemberController extends Controller
{
    private GroupMember $groupMembers;
    private GroupMemberService $service;

    public function __construct()
    {
        $this->groupMembers = new GroupMember();
        $this->service = new GroupMemberService();
    }

    public function index(): string
    {
        $groupId = (int) ($_GET['group_id'] ?? 0);

        return $this->layout('group_members.index', [
            'title' => 'Group Members',
            'activeMenu' => 'group_members',
            'groupMembers' => $this->groupMembers->all($groupId > 0 ? $groupId : null),
            'groups' => (new Group())->all(),
            'totals' => $this->groupMembers->totalsByGroup(),
            'selectedGroupId' => $groupId,
            'flash' => $_GET['message'] ?? null,
            'error' => $_GET['error'] ?? null,
        ]);
    }

    public function create(): string
    {
        return $this->layout('group_members.create', $this->formData([
            'title' => 'Add Group Member',
            'groupMember' => ['group_id' => 0, 'member_id' => 0, 'share_percent' => ''],
            'errors' => [],
        ]));
    }

    public function store(): void
    {
        $data = $this->service->normalizeData($_POST);
        $result = $this->service->create($data);

        if (! $result['ok']) {
            echo $this->layout('group_members.create', $this->formData([
                'title' => 'Add Group Member',
                'groupMember' => $data,
                'errors' => $result['errors'],
            ]));
            return;
        }

        $this->redirect('/group-members?message=created');
    }

    public function edit(string $id): string
    {
        $groupMember = $this->findOrFail((int) $id);

        return $this->layout('group_members.edit', $this->formData([
            'title' => 'Edit Share Percent',
            'groupMember' => $groupMember,
            'errors' => [],
        ]));
    }

    public function update(string $id): void
    {
        $groupMemberId = (int) $id;
        $result = $this->service->updateSharePercent($groupMemberId, $_POST);

        if (! $result['ok']) {
            $groupMember = $result['group_member'] ?? $this->findOrFail($groupMemberId);
            $groupMember['share_percent'] = $_POST['share_percent'] ?? $groupMember['share_percent'];

            echo $this->layout('group_members.edit', $this->formData([
                'title' => 'Edit Share Percent',
                'groupMember' => $groupMember,
                'errors' => $result['errors'],
            ]));
            return;
        }

        $this->redirect('/group-members?message=updated');
    }

    public function destroy(string $id): void
    {
        $result = $this->service->delete((int) $id);

        if (! $result['ok']) {
            $this->redirect('/group-members?error=' . urlencode($result['errors']['general'] ?? 'Gagal menghapus group member.'));
        }

        $this->redirect('/group-members?message=deleted');
    }

    private function formData(array $data): array
    {
        return array_merge([
            'activeMenu' => 'group_members',
            'groups' => (new Group())->all(),
            'members' => (new Member())->all(),
            'totals' => $this->groupMembers->totalsByGroup(),
        ], $data);
    }

    private function findOrFail(int $id): array
    {
        $groupMember = $this->groupMembers->find($id);

        if (! $groupMember) {
            http_response_code(404);
            exit('404 - Group member not found');
        }

        return $groupMember;
    }

    private function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }
}
