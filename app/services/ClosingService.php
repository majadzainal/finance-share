<?php

namespace App\Services;

use App\Core\Model;
use App\Models\Expense;
use RuntimeException;
use Throwable;

class ClosingService extends Model
{
    public function finalize(
        int $groupId,
        string $periodStart,
        string $periodEnd,
        string $closedBy = 'system',
        int $transferMethodId = 0,
        float $transferFeeAmount = 0.0,
        float $savingsAmount = 0.0
    ): int
    {
        $preview = (new ClosingPreviewService())->preview($groupId, $periodStart, $periodEnd, $transferFeeAmount, $savingsAmount);

        if (! $preview['share_is_valid']) {
            throw new RuntimeException('Total share_percent harus 100% sebelum closing bisa diproses.');
        }

        if ($this->hasExistingClosing($groupId, $periodStart, $periodEnd)) {
            throw new RuntimeException('Periode ini sudah pernah di-closing.');
        }

        $db = $this->db();
        $db->beginTransaction();

        try {
            $this->insertTransferFeeExpense($groupId, $periodEnd, $transferMethodId, $transferFeeAmount, $closedBy);
            $closingId = $this->insertClosing($groupId, $periodStart, $periodEnd, $preview, $closedBy);
            $this->lockIncomes($closingId, $groupId, $periodStart, $periodEnd);
            $this->lockExpenses($closingId, $groupId, $periodStart, $periodEnd);

            if (($preview['savings_amount'] ?? 0) > 0) {
                (new \App\Models\GroupSavings())->recordDeposit(
                    $groupId,
                    (float) $preview['savings_amount'],
                    $periodEnd,
                    'closing',
                    $closingId,
                    'Penyisihan Tabungan Closing Periode ' . $periodStart . ' s/d ' . $periodEnd,
                    'CLOSING-' . $closingId,
                    $closedBy
                );
            }

            foreach ($preview['members'] as $member) {
                $cashAdvanceCut = $this->cutCashAdvances(
                    $closingId,
                    $groupId,
                    (int) $member['member_id'],
                    (float) $member['cash_advance_cut'],
                    $periodEnd
                );

                $this->insertProfitDistribution($closingId, $groupId, $member, $cashAdvanceCut);
            }

            $this->insertLedgerEntries($closingId, $groupId, $periodEnd, $preview);
            $db->commit();

            return $closingId;
        } catch (Throwable $exception) {
            $db->rollBack();
            throw $exception;
        }
    }

    private function hasExistingClosing(int $groupId, string $periodStart, string $periodEnd): bool
    {
        $statement = $this->db()->prepare(
            "SELECT COUNT(*)
             FROM trx_closings
             WHERE group_id = :group_id
               AND period_start = :period_start
               AND period_end = :period_end
               AND status <> 'void'"
        );
        $statement->execute([
            'group_id' => $groupId,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
        ]);

        return (int) $statement->fetchColumn() > 0;
    }

    private function insertClosing(int $groupId, string $periodStart, string $periodEnd, array $preview, string $closedBy): int
    {
        $statement = $this->db()->prepare(
            "INSERT INTO trx_closings
                (group_id, period_start, period_end, total_income, total_expense,
                 total_cash_advance, total_cash_advance_payment, net_profit,
                 savings_amount, distributable_profit,
                 status, closed_at, closed_by)
             VALUES
                (:group_id, :period_start, :period_end, :total_income, :total_expense,
                 :total_cash_advance, :total_cash_advance_payment, :net_profit,
                 :savings_amount, :distributable_profit,
                 'closed', NOW(), :closed_by)"
        );
        $totalCashAdvanceCut = array_sum(array_map(static fn ($member) => (float) $member['cash_advance_cut'], $preview['members']));
        $statement->execute([
            'group_id' => $groupId,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'total_income' => $preview['total_income'],
            'total_expense' => $preview['total_expense'],
            'total_cash_advance' => array_sum(array_map(static fn ($member) => (float) $member['outstanding_kasbon'], $preview['members'])),
            'total_cash_advance_payment' => $totalCashAdvanceCut,
            'net_profit' => $preview['net_profit'],
            'savings_amount' => $preview['savings_amount'] ?? 0,
            'distributable_profit' => $preview['distributable_profit'] ?? $preview['net_profit'],
            'closed_by' => $closedBy,
        ]);

        return (int) $this->db()->lastInsertId();
    }

    private function insertTransferFeeExpense(
        int $groupId,
        string $expenseDate,
        int $transferMethodId,
        float $transferFeeAmount,
        string $createdBy
    ): ?int {
        if ($transferFeeAmount <= 0) {
            return null;
        }

        return (new Expense())->createTransferFee([
            'group_id' => $groupId,
            'expense_date' => $expenseDate,
            'amount' => $transferFeeAmount,
            'description' => 'Biaya transfer closing periode ' . $expenseDate,
            'created_by' => $createdBy,
            'transfer_method_id' => $transferMethodId,
            'approval_status' => 'approved',
        ]);
    }

    private function lockIncomes(int $closingId, int $groupId, string $periodStart, string $periodEnd): void
    {
        $statement = $this->db()->prepare(
            'UPDATE trx_incomes
             SET closing_id = :closing_id
             WHERE group_id = :group_id
               AND transaction_date BETWEEN :period_start AND :period_end
               AND closing_id IS NULL
               AND deleted_at IS NULL'
        );
        $statement->execute([
            'closing_id' => $closingId,
            'group_id' => $groupId,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
        ]);
    }

    private function lockExpenses(int $closingId, int $groupId, string $periodStart, string $periodEnd): void
    {
        $statement = $this->db()->prepare(
            'UPDATE trx_expenses
             SET closing_id = :closing_id
             WHERE group_id = :group_id
               AND expense_date BETWEEN :period_start AND :period_end
               AND approval_status = \'approved\'
               AND closing_id IS NULL'
        );
        $statement->execute([
            'closing_id' => $closingId,
            'group_id' => $groupId,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
        ]);
    }

    private function cutCashAdvances(int $closingId, int $groupId, int $memberId, float $cutAmount, string $paymentDate): float
    {
        $remainingCut = $cutAmount;
        $totalCut = 0.0;

        foreach ($this->outstandingCashAdvances($groupId, $memberId) as $advance) {
            if ($remainingCut <= 0) {
                break;
            }

            $paymentAmount = min($remainingCut, (float) $advance['remaining_amount']);
            $newRemaining = (float) $advance['remaining_amount'] - $paymentAmount;

            $this->insertCashAdvancePayment((int) $advance['id'], $closingId, $paymentDate, $paymentAmount);
            $this->updateCashAdvanceRemaining((int) $advance['id'], $newRemaining);

            $remainingCut -= $paymentAmount;
            $totalCut += $paymentAmount;
        }

        return $totalCut;
    }

    private function outstandingCashAdvances(int $groupId, int $memberId): array
    {
        $statement = $this->db()->prepare(
            'SELECT id, remaining_amount
             FROM trx_cash_advances
             WHERE member_id = :member_id
               AND status = 0
               AND remaining_amount > 0
               AND (group_id = :group_id OR group_id IS NULL)
             ORDER BY advance_date ASC, id ASC'
        );
        $statement->execute([
            'group_id' => $groupId,
            'member_id' => $memberId,
        ]);

        return $statement->fetchAll();
    }

    private function insertCashAdvancePayment(int $cashAdvanceId, int $closingId, string $paymentDate, float $amount): void
    {
        $statement = $this->db()->prepare(
            'INSERT INTO trx_cash_advance_payments
                (cash_advance_id, payment_date, reference_no, amount, payment_method, notes)
             VALUES
                (:cash_advance_id, :payment_date, :reference_no, :amount, :payment_method, :notes)'
        );
        $statement->execute([
            'cash_advance_id' => $cashAdvanceId,
            'payment_date' => $paymentDate,
            'reference_no' => 'CLOSING-' . $closingId,
            'amount' => $amount,
            'payment_method' => 'closing_cut',
            'notes' => 'Auto cut from closing #' . $closingId,
        ]);
    }

    private function updateCashAdvanceRemaining(int $cashAdvanceId, float $remainingAmount): void
    {
        $statement = $this->db()->prepare(
            'UPDATE trx_cash_advances
             SET remaining_amount = :remaining_amount,
                 status = :status
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $cashAdvanceId,
            'remaining_amount' => $remainingAmount,
            'status' => $remainingAmount <= 0 ? 1 : 0,
        ]);
    }

    private function insertProfitDistribution(int $closingId, int $groupId, array $member, float $cashAdvanceCut): void
    {
        $profitAmount = max(0, (float) $member['gross_share_amount']);
        $paidAmount = max(0, $profitAmount - $cashAdvanceCut);
        $statement = $this->db()->prepare(
            'INSERT INTO trx_profit_distributions
                (closing_id, group_id, member_id, share_percent, profit_amount, paid_amount, status, notes)
             VALUES
                (:closing_id, :group_id, :member_id, :share_percent, :profit_amount, :paid_amount, :status, :notes)'
        );
        $statement->execute([
            'closing_id' => $closingId,
            'group_id' => $groupId,
            'member_id' => $member['member_id'],
            'share_percent' => $member['share_percent'],
            'profit_amount' => $profitAmount,
            'paid_amount' => $paidAmount,
            'status' => 'pending',
            'notes' => 'Cash advance cut: ' . $cashAdvanceCut,
        ]);
    }

    private function insertLedgerEntries(int $closingId, int $groupId, string $transactionDate, array $preview): void
    {
        $entries = [
            [null, '4000', 'Income', $preview['total_income'], 0, 'Closing income'],
            [null, '5000', 'Expense', 0, $preview['total_expense'], 'Closing expense'],
        ];

        if (($preview['savings_amount'] ?? 0) > 0) {
            $entries[] = [
                null,
                '3100',
                'Group Savings',
                0,
                (float) $preview['savings_amount'],
                'Penyisihan Tabungan Toko Closing Periode ' . $preview['period_start'] . ' s/d ' . $preview['period_end'],
            ];
        }

        foreach ($preview['members'] as $member) {
            $entries[] = [
                (int) $member['member_id'],
                '1200',
                'Kasbon Cut',
                0,
                (float) $member['cash_advance_cut'],
                'Closing kasbon cut - ' . $member['member_name'],
            ];
            $entries[] = [
                (int) $member['member_id'],
                '3000',
                'Profit Share',
                0,
                (float) $member['final_take_home_out'],
                'Closing profit share - ' . $member['member_name'],
            ];
        }

        $statement = $this->db()->prepare(
            'INSERT INTO trx_ledger
                (group_id, member_id, transaction_date, source_table, source_id, account_code, account_name, debit, credit, description)
             VALUES
                (:group_id, :member_id, :transaction_date, :source_table, :source_id, :account_code, :account_name, :debit, :credit, :description)'
        );

        foreach ($entries as [$memberId, $code, $name, $debit, $credit, $description]) {
            if ((float) $debit <= 0 && (float) $credit <= 0) {
                continue;
            }

            $statement->execute([
                'group_id' => $groupId,
                'member_id' => $memberId,
                'transaction_date' => $transactionDate,
                'source_table' => 'trx_closings',
                'source_id' => $closingId,
                'account_code' => $code,
                'account_name' => $name,
                'debit' => $debit,
                'credit' => $credit,
                'description' => $description,
            ]);
        }
    }
}
