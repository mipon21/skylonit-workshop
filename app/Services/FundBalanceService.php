<?php

namespace App\Services;

use App\Models\InternalExpense;
use App\Models\InternalFundLedger;
use App\Models\Investment;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

/**
 * Derived fund balances for internal expenses.
 * - Overhead: realized overhead from projects minus ledger debits.
 * - Profit: founder share (pool - investor payouts) minus ledger debits.
 * - Investment: per-investment (amount - returned_amount) minus ledger debits.
 */
class FundBalanceService
{
    public function getOverheadBalance(): float
    {
        $inflow = Project::query()->get()->sum(fn ($p) => $p->paid_overhead);
        $debits = InternalFundLedger::where('fund_type', InternalFundLedger::FUND_OVERHEAD)
            ->where('direction', InternalFundLedger::DIRECTION_DEBIT)
            ->sum('amount');
        $credits = InternalFundLedger::where('fund_type', InternalFundLedger::FUND_OVERHEAD)
            ->where('direction', InternalFundLedger::DIRECTION_CREDIT)
            ->sum('amount');
        return round($inflow - $debits + $credits, 2);
    }

    public function getProfitPoolBalance(): float
    {
        $profitPoolService = app(ProfitPoolService::class);
        $totalPool = $profitPoolService->getTotalProfitPool();
        $returnedToInvestors = Investment::sum('returned_amount');
        $founderShare = round($totalPool - $returnedToInvestors, 2);
        $debits = InternalFundLedger::where('fund_type', InternalFundLedger::FUND_PROFIT)
            ->where('direction', InternalFundLedger::DIRECTION_DEBIT)
            ->sum('amount');
        $credits = InternalFundLedger::where('fund_type', InternalFundLedger::FUND_PROFIT)
            ->where('direction', InternalFundLedger::DIRECTION_CREDIT)
            ->sum('amount');
        return round($founderShare - $debits + $credits, 2);
    }

    public function getInvestmentBalance(int $investmentId): float
    {
        $investment = Investment::find($investmentId);
        if (!$investment) {
            return 0.0;
        }
        $available = $investment->amount - $investment->returned_amount;
        $debits = InternalFundLedger::where('fund_type', InternalFundLedger::FUND_INVESTMENT)
            ->where('investment_id', $investmentId)
            ->where('direction', InternalFundLedger::DIRECTION_DEBIT)
            ->sum('amount');
        $credits = InternalFundLedger::where('fund_type', InternalFundLedger::FUND_INVESTMENT)
            ->where('investment_id', $investmentId)
            ->where('direction', InternalFundLedger::DIRECTION_CREDIT)
            ->sum('amount');
        return round($available - $debits + $credits, 2);
    }

    /** @return array<int, float> investment_id => balance */
    public function getAllInvestmentBalances(): array
    {
        $investments = Investment::all();
        $result = [];
        foreach ($investments as $inv) {
            $result[$inv->id] = $this->getInvestmentBalance($inv->id);
        }
        return $result;
    }

    /**
     * Create internal expense and ledger debit. Caller must have validated balance.
     */
    public function recordInternalExpense(array $data, ?int $createdBy): InternalExpense
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $expense = InternalExpense::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'amount' => (float) $data['amount'],
                'expense_date' => $data['expense_date'],
                'primary_fund' => InternalExpense::PRIMARY_FUND_OVERHEAD,
                'fallback_fund' => $data['fallback_fund'] ?? null,
                'funded_from' => $data['funded_from'],
                'investment_id' => $data['investment_id'] ?? null,
                'created_by' => $createdBy,
            ]);

            InternalFundLedger::create([
                'fund_type' => $expense->funded_from,
                'reference_type' => InternalFundLedger::REFERENCE_INTERNAL_EXPENSE,
                'reference_id' => $expense->id,
                'investment_id' => $expense->investment_id,
                'amount' => $expense->amount,
                'direction' => InternalFundLedger::DIRECTION_DEBIT,
            ]);

            return $expense;
        });
    }

    /**
     * Check if amount can be covered by the chosen fund.
     */
    public function canFundFrom(string $fundedFrom, float $amount, ?int $investmentId = null): bool
    {
        switch ($fundedFrom) {
            case InternalExpense::FUNDED_OVERHEAD:
                return $this->getOverheadBalance() >= $amount;
            case InternalExpense::FUNDED_PROFIT:
                return $this->getProfitPoolBalance() >= $amount;
            case InternalExpense::FUNDED_INVESTMENT:
                return $investmentId && $this->getInvestmentBalance($investmentId) >= $amount;
            default:
                return false;
        }
    }

    /**
     * Check if we can fund an update: when reversing old debit, new fund must have enough for new amount.
     * Same fund: balance + oldAmount >= newAmount. Different fund: new fund balance >= newAmount.
     */
    public function canFundFromForUpdate(
        string $newFund,
        float $newAmount,
        ?int $newInvestmentId,
        string $oldFund,
        float $oldAmount,
        ?int $oldInvestmentId
    ): bool {
        $sameFund = $newFund === $oldFund && $newInvestmentId == $oldInvestmentId;
        if ($sameFund) {
            $balance = match ($newFund) {
                InternalExpense::FUNDED_OVERHEAD => $this->getOverheadBalance(),
                InternalExpense::FUNDED_PROFIT => $this->getProfitPoolBalance(),
                default => $this->getInvestmentBalance((int) $newInvestmentId),
            };
            return ($balance + $oldAmount) >= $newAmount;
        }
        return $this->canFundFrom($newFund, $newAmount, $newInvestmentId);
    }
}
