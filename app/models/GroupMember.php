<?php

namespace App\Models;

use App\Core\Model;

class GroupMember extends Model
{
    public function all(?int $groupId = null): array
    {
        $sql = 'SELECT gm.id, gm.group_id, gm.member_id, gm.share_percent, gm.is_active,
                       gm.created_at, gm.updated_at,
                       g.code AS group_code, g.name AS group_name,
                       m.code AS member_code, m.name AS member_name, m.nickname
                FROM mst_group_members gm
                JOIN mst_groups g ON g.id = gm.group_id
                JOIN mst_members m ON m.id = gm.member_id';
        $params = [];

        if ($groupId !== null) {
            $sql .= ' WHERE gm.group_id = :group_id';
            $params['group_id'] = $groupId;
        }

        $sql .= ' ORDER BY g.name ASC, m.name ASC';

        $statement = $this->db()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT gm.id, gm.group_id, gm.member_id, gm.share_percent, gm.is_active,
                    g.code AS group_code, g.name AS group_name,
                    m.code AS member_code, m.name AS member_name, m.nickname
             FROM mst_group_members gm
             JOIN mst_groups g ON g.id = gm.group_id
             JOIN mst_members m ON m.id = gm.member_id
             WHERE gm.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        $groupMember = $statement->fetch();

        return $groupMember ?: null;
    }

    public function create(array $data): int
    {
        $statement = $this->db()->prepare(
            'INSERT INTO mst_group_members (group_id, member_id, share_percent, joined_at, is_active)
             VALUES (:group_id, :member_id, :share_percent, CURRENT_DATE, 1)'
        );
        $statement->execute([
            'group_id' => $data['group_id'],
            'member_id' => $data['member_id'],
            'share_percent' => $data['share_percent'],
        ]);

        return (int) $this->db()->lastInsertId();
    }

    public function updateSharePercent(int $id, float $sharePercent): bool
    {
        $statement = $this->db()->prepare(
            'UPDATE mst_group_members
             SET share_percent = :share_percent
             WHERE id = :id'
        );

        return $statement->execute([
            'id' => $id,
            'share_percent' => $sharePercent,
        ]);
    }

    public function delete(int $id): bool
    {
        $statement = $this->db()->prepare('DELETE FROM mst_group_members WHERE id = :id');

        return $statement->execute(['id' => $id]);
    }

    public function memberExistsInGroup(int $groupId, int $memberId, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM mst_group_members WHERE group_id = :group_id AND member_id = :member_id';
        $params = [
            'group_id' => $groupId,
            'member_id' => $memberId,
        ];

        if ($ignoreId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $ignoreId;
        }

        $statement = $this->db()->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn() > 0;
    }

    public function totalSharePercent(int $groupId, ?int $ignoreId = null): float
    {
        $sql = 'SELECT COALESCE(SUM(share_percent), 0) FROM mst_group_members WHERE group_id = :group_id';
        $params = ['group_id' => $groupId];

        if ($ignoreId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $ignoreId;
        }

        $statement = $this->db()->prepare($sql);
        $statement->execute($params);

        return (float) $statement->fetchColumn();
    }

    public function totalsByGroup(): array
    {
        $statement = $this->db()->query(
            'SELECT g.id AS group_id, g.name AS group_name, COALESCE(SUM(gm.share_percent), 0) AS total_share_percent
             FROM mst_groups g
             LEFT JOIN mst_group_members gm ON gm.group_id = g.id
             GROUP BY g.id, g.name
             ORDER BY g.name ASC'
        );

        return $statement->fetchAll();
    }

    public function hasRelatedTransactions(int $groupId, int $memberId): bool
    {
        $checks = [
            'SELECT COUNT(*) FROM trx_cash_advances WHERE group_id = :group_id AND member_id = :member_id',
            'SELECT COUNT(*) FROM trx_expenses WHERE group_id = :group_id AND paid_by_member_id = :member_id',
            'SELECT COUNT(*) FROM trx_profit_distributions WHERE group_id = :group_id AND member_id = :member_id',
        ];

        foreach ($checks as $sql) {
            $statement = $this->db()->prepare($sql);
            $statement->execute([
                'group_id' => $groupId,
                'member_id' => $memberId,
            ]);

            if ((int) $statement->fetchColumn() > 0) {
                return true;
            }
        }

        return false;
    }
}
