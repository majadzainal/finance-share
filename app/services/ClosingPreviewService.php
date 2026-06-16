<?php

namespace App\Services;

use App\Core\Model;
use RuntimeException;

class ClosingPreviewService extends Model
{
    public function preview(int $groupId, string $periodStart, string $periodEnd, float $transferFeeAmount = 0.0): array
    {
        $this->validate($groupId, $periodStart, $periodEnd);

        $totalIncome = $this->sumUnlockedIncome($groupId, $periodStart, $periodEnd);
        $baseExpense = $this->sumUnlockedExpense($groupId, $periodStart, $periodEnd);
        $transferFeeAmount = max(0, $transferFeeAmount);
        $totalExpense = $baseExpense + $transferFeeAmount;
        $openingBalance = $this->openingBalance($groupId, $periodStart);
        $netProfit = $totalIncome - $totalExpense;
        $members = $this->groupMembers($groupId);
        $shareTotal = array_sum(array_map(static fn ($member) => (float) $member['share_percent'], $members));
        $rows = [];

        foreach ($members as $member) {
            $grossShareAmount = round($netProfit * ((float) $member['share_percent'] / 100), 2);
            $outstandingKasbon = $this->outstandingCashAdvance($groupId, (int) $member['member_id']);
            $cashAdvanceCut = $grossShareAmount > 0 ? min($grossShareAmount, $outstandingKasbon) : 0;

            $rows[] = [
                'member_id' => (int) $member['member_id'],
                'member_name' => $member['member_name'],
                'nickname' => $member['nickname'],
                'share_percent' => (float) $member['share_percent'],
                'gross_share_amount' => $grossShareAmount,
                'outstanding_kasbon' => $outstandingKasbon,
                'cash_advance_cut' => $cashAdvanceCut,
                'final_take_home_out' => $grossShareAmount - $cashAdvanceCut,
            ];
        }

        return [
            'group' => $this->group($groupId),
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'opening_balance' => $openingBalance,
            'total_income' => $totalIncome,
            'base_expense' => $baseExpense,
            'transfer_fee_amount' => $transferFeeAmount,
            'total_expense' => $totalExpense,
            'net_profit' => $netProfit,
            'share_total' => $shareTotal,
            'share_is_valid' => abs($shareTotal - 100) < 0.001,
            'members' => $rows,
            'warnings' => $this->warnings($groupId, $periodStart, $periodEnd, $shareTotal),
        ];
    }

    private function validate(int $groupId, string $periodStart, string $periodEnd): void
    {
        if ($groupId <= 0 || ! $this->group($groupId)) {
            throw new RuntimeException('Group/store wajib dipilih.');
        }

        if ($periodStart === '' || $periodEnd === '') {
            throw new RuntimeException('Period start dan period end wajib diisi.');
        }

        if ($periodStart > $periodEnd) {
            throw new RuntimeException('Period start tidak boleh lebih besar dari period end.');
        }
    }

    private function group(int $groupId): ?array
    {
        $statement = $this->db()->prepare('SELECT id, code, name FROM mst_groups WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $groupId]);
        $group = $statement->fetch();

        return $group ?: null;
    }

    private function sumUnlockedIncome(int $groupId, string $periodStart, string $periodEnd): float
    {
        $statement = $this->db()->prepare(
            'SELECT COALESCE(SUM(amount), 0)
             FROM trx_incomes
             WHERE group_id = :group_id
               AND transaction_date BETWEEN :period_start AND :period_end
               AND closing_id IS NULL
               AND deleted_at IS NULL'
        );
        $statement->execute(['group_id' => $groupId, 'period_start' => $periodStart, 'period_end' => $periodEnd]);

        return (float) $statement->fetchColumn();
    }

    private function sumUnlockedExpense(int $groupId, string $periodStart, string $periodEnd): float
    {
        $statement = $this->db()->prepare(
            'SELECT COALESCE(SUM(amount), 0)
             FROM trx_expenses
             WHERE group_id = :group_id
               AND expense_date BETWEEN :period_start AND :period_end
               AND approval_status = \'approved\'
               AND closing_id IS NULL'
        );
        $statement->execute(['group_id' => $groupId, 'period_start' => $periodStart, 'period_end' => $periodEnd]);

        return (float) $statement->fetchColumn();
    }

    private function openingBalance(int $groupId, string $periodStart): float
    {
        $statement = $this->db()->prepare(
            "SELECT
                (SELECT COALESCE(SUM(amount), 0)
                 FROM trx_incomes
                 WHERE group_id = :income_group_id
                   AND transaction_date < :income_period_start
                   AND deleted_at IS NULL)
                -
                (SELECT COALESCE(SUM(amount), 0)
                 FROM trx_expenses
                 WHERE group_id = :expense_group_id
                   AND expense_date < :expense_period_start
                   AND approval_status = 'approved')
                -
                (SELECT COALESCE(SUM(amount), 0)
                 FROM trx_cash_advances
                 WHERE group_id = :cash_advance_group_id AND advance_date < :cash_advance_period_start)"
        );
        $statement->execute([
            'income_group_id' => $groupId,
            'income_period_start' => $periodStart,
            'expense_group_id' => $groupId,
            'expense_period_start' => $periodStart,
            'cash_advance_group_id' => $groupId,
            'cash_advance_period_start' => $periodStart,
        ]);

        return (float) $statement->fetchColumn();
    }

    private function groupMembers(int $groupId): array
    {
        $statement = $this->db()->prepare(
            'SELECT gm.member_id, gm.share_percent, m.name AS member_name, m.nickname
             FROM mst_group_members gm
             JOIN mst_members m ON m.id = gm.member_id
             WHERE gm.group_id = :group_id AND gm.is_active = 1
             ORDER BY m.name ASC'
        );
        $statement->execute(['group_id' => $groupId]);

        return $statement->fetchAll();
    }

    private function outstandingCashAdvance(int $groupId, int $memberId): float
    {
        $statement = $this->db()->prepare(
            'SELECT COALESCE(SUM(remaining_amount), 0)
             FROM trx_cash_advances
             WHERE member_id = :member_id
               AND status = 0
               AND remaining_amount > 0
               AND (group_id = :group_id OR group_id IS NULL)'
        );
        $statement->execute(['group_id' => $groupId, 'member_id' => $memberId]);

        return (float) $statement->fetchColumn();
    }

    private function warnings(int $groupId, string $periodStart, string $periodEnd, float $shareTotal): array
    {
        $warnings = [];
        $lockedIncome = $this->lockedCount('trx_incomes', 'transaction_date', $groupId, $periodStart, $periodEnd);
        $lockedExpense = $this->lockedCount('trx_expenses', 'expense_date', $groupId, $periodStart, $periodEnd);

        if ($lockedIncome > 0) {
            $warnings[] = "{$lockedIncome} income pada periode ini sudah locked dan tidak dihitung ulang.";
        }

        if ($lockedExpense > 0) {
            $warnings[] = "{$lockedExpense} expense pada periode ini sudah locked dan tidak dihitung ulang.";
        }

        $unapprovedExpense = $this->unapprovedExpenseCount($groupId, $periodStart, $periodEnd);

        if ($unapprovedExpense > 0) {
            $warnings[] = "{$unapprovedExpense} expense belum approved dan tidak akan dihitung closing.";
        }

        if (abs($shareTotal - 100) >= 0.001) {
            $warnings[] = 'Total share_percent member group harus 100% sebelum closing bisa diproses.';
        }

        return $warnings;
    }

    private function lockedCount(string $table, string $dateColumn, int $groupId, string $periodStart, string $periodEnd): int
    {
        $deletedWhere = $table === 'trx_incomes' ? 'AND deleted_at IS NULL' : '';
        $statement = $this->db()->prepare(
            "SELECT COUNT(*)
             FROM {$table}
             WHERE group_id = :group_id
               AND {$dateColumn} BETWEEN :period_start AND :period_end
               AND closing_id IS NOT NULL
               {$deletedWhere}"
        );
        $statement->execute(['group_id' => $groupId, 'period_start' => $periodStart, 'period_end' => $periodEnd]);

        return (int) $statement->fetchColumn();
    }

    private function unapprovedExpenseCount(int $groupId, string $periodStart, string $periodEnd): int
    {
        $statement = $this->db()->prepare(
            "SELECT COUNT(*)
             FROM trx_expenses
             WHERE group_id = :group_id
               AND expense_date BETWEEN :period_start AND :period_end
               AND closing_id IS NULL
               AND approval_status <> 'approved'"
        );
        $statement->execute(['group_id' => $groupId, 'period_start' => $periodStart, 'period_end' => $periodEnd]);

        return (int) $statement->fetchColumn();
    }
}
