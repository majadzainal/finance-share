<?php

namespace App\Services;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Member;

class GroupMemberService
{
    private GroupMember $groupMembers;
    private Group $groups;
    private Member $members;

    public function __construct(?GroupMember $groupMembers = null, ?Group $groups = null, ?Member $members = null)
    {
        $this->groupMembers = $groupMembers ?? new GroupMember();
        $this->groups = $groups ?? new Group();
        $this->members = $members ?? new Member();
    }

    public function create(array $data): array
    {
        $errors = $this->validate($data);

        if ($errors === []) {
            $errors = $this->validateMembershipRules($data['group_id'], $data['member_id'], $data['share_percent']);
        }

        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        $this->groupMembers->create($data);

        return ['ok' => true, 'errors' => []];
    }

    public function updateSharePercent(int $id, array $data): array
    {
        $groupMember = $this->groupMembers->find($id);

        if (! $groupMember) {
            return ['ok' => false, 'errors' => ['general' => 'Relasi group member tidak ditemukan.']];
        }

        $sharePercent = $this->normalizeSharePercent($data['share_percent'] ?? null);
        $errors = $this->validateSharePercent($sharePercent);

        if ($errors === []) {
            $total = $this->groupMembers->totalSharePercent((int) $groupMember['group_id'], $id) + $sharePercent;

            if ($total > 100) {
                $errors['share_percent'] = 'Total share percent per group tidak boleh lebih dari 100%.';
            }
        }

        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors, 'group_member' => $groupMember];
        }

        $this->groupMembers->updateSharePercent($id, $sharePercent);

        return ['ok' => true, 'errors' => []];
    }

    public function delete(int $id): array
    {
        $groupMember = $this->groupMembers->find($id);

        if (! $groupMember) {
            return ['ok' => false, 'errors' => ['general' => 'Relasi group member tidak ditemukan.']];
        }

        if ($this->groupMembers->hasRelatedTransactions((int) $groupMember['group_id'], (int) $groupMember['member_id'])) {
            return [
                'ok' => false,
                'errors' => ['general' => 'Member tidak bisa dihapus dari group karena sudah ada transaksi terkait.'],
            ];
        }

        $this->groupMembers->delete($id);

        return ['ok' => true, 'errors' => []];
    }

    public function validate(array $data): array
    {
        $errors = [];

        if (($data['group_id'] ?? 0) <= 0 || ! $this->groups->find((int) $data['group_id'])) {
            $errors['group_id'] = 'Group/store wajib dipilih.';
        }

        if (($data['member_id'] ?? 0) <= 0 || ! $this->members->find((int) $data['member_id'])) {
            $errors['member_id'] = 'Member wajib dipilih.';
        }

        return array_merge($errors, $this->validateSharePercent($data['share_percent'] ?? null));
    }

    public function normalizeData(array $input): array
    {
        return [
            'group_id' => (int) ($input['group_id'] ?? 0),
            'member_id' => (int) ($input['member_id'] ?? 0),
            'share_percent' => $this->normalizeSharePercent($input['share_percent'] ?? null),
        ];
    }

    private function validateMembershipRules(int $groupId, int $memberId, float $sharePercent): array
    {
        $errors = [];

        if ($this->groupMembers->memberExistsInGroup($groupId, $memberId)) {
            $errors['member_id'] = 'Member sudah terdaftar di group/store ini.';
        }

        $total = $this->groupMembers->totalSharePercent($groupId) + $sharePercent;

        if ($total > 100) {
            $errors['share_percent'] = 'Total share percent per group tidak boleh lebih dari 100%.';
        }

        return $errors;
    }

    private function validateSharePercent(mixed $sharePercent): array
    {
        $errors = [];

        if (! is_float($sharePercent) && ! is_int($sharePercent)) {
            $errors['share_percent'] = 'Share percent wajib diisi.';
            return $errors;
        }

        if ($sharePercent <= 0 || $sharePercent > 100) {
            $errors['share_percent'] = 'Share percent harus lebih dari 0 dan maksimal 100.';
        }

        return $errors;
    }

    private function normalizeSharePercent(mixed $value): ?float
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return round((float) str_replace(',', '.', (string) $value), 3);
    }
}
