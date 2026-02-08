<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\Payment;
use App\Models\ProfitDistribution;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Profit Pool & distribution engine.
 * - Profit Pool = sum of project profits (15%) realized in a period. Calculated dynamically.
 * - Investor payouts come ONLY from Profit Pool; capped and temporary.
 */
class ProfitPoolService
{
    /**
     * Calculate profit pool for a given month (profit realized from payments received that month).
     * Only PAID payments in that month count; profit portion = payment_amount * (project_profit / contract_amount).
     */
    public function getProfitPoolForMonth(int $year, int $month): float
    {
        $start = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth();

        $payments = Payment::with('project')
            ->where(function ($q) {
                $q->where('payment_status', Payment::PAYMENT_STATUS_PAID)
                    ->orWhere('status', Payment::STATUS_COMPLETED);
            })
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$start, $end])
            ->get();

        $total = 0.0;
        foreach ($payments as $payment) {
            $project = $payment->project;
            if (!$project || $project->contract_amount <= 0) {
                continue;
            }
            // Developer–Sales mode projects contribute ZERO to profit pool (investor compatibility).
            if ($project->is_developer_sales_mode) {
                continue;
            }
            $profitRatio = $project->profit / $project->contract_amount;
            $total += $payment->amount * $profitRatio;
        }

        return round($total, 2);
    }

    /**
     * Total profit pool (all-time) = sum of all projects' realized_profit. Dynamic, not stored.
     */
    public function getTotalProfitPool(): float
    {
        $total = \App\Models\Project::with([])->get()->sum(function ($p) {
            // Developer–Sales mode projects contribute ZERO to profit pool.
            return $p->is_developer_sales_mode ? 0 : $p->realized_profit;
        });
        return round($total, 2);
    }

    /**
     * Run profit distribution for a given month: aggregate profit pool, pay active investors (capped), record rows, exit if cap reached.
     */
    public function runDistributionForMonth(int $year, int $month): array
    {
        $period = sprintf('%04d-%02d', $year, $month);
        $profitPool = $this->getProfitPoolForMonth($year, $month);

        $activeInvestors = Investment::where('status', Investment::STATUS_ACTIVE)->get();
        $results = [];

        $founderPercent = config('investor.founder_percent', 60) / 100;
        $investorPoolPercent = config('investor.investor_pool_percent', 40) / 100;
        $founderShareBase = round($profitPool * $founderPercent, 2);
        $investorPoolTotal = round($profitPool * $investorPoolPercent, 2);

        DB::transaction(function () use ($period, $profitPool, $activeInvestors, $founderShareBase, $investorPoolTotal, &$results) {
            $totalInvestorShare = 0.0;
            $payouts = [];
            $sumWeights = $activeInvestors->sum('profit_share_percent');
            $sumWeights = $sumWeights > 0 ? $sumWeights : 1;

            foreach ($activeInvestors as $investor) {
                $weight = $investor->profit_share_percent / $sumWeights;
                $rawPayout = round($investorPoolTotal * $weight, 2);
                $remainingCap = max(0, $investor->return_cap_amount - $investor->returned_amount);
                $actualPayout = round(min($rawPayout, $remainingCap), 2);
                $totalInvestorShare += $actualPayout;
                $payouts[] = ['investor' => $investor, 'actualPayout' => $actualPayout];
            }

            $founderShareTotal = round($profitPool - $totalInvestorShare, 2);

            foreach ($payouts as ['investor' => $investor, 'actualPayout' => $actualPayout]) {
                ProfitDistribution::updateOrCreate(
                    [
                        'investor_id' => $investor->id,
                        'period' => $period,
                    ],
                    [
                        'profit_pool_amount' => $profitPool,
                        'investor_share_amount' => $actualPayout,
                        'founder_share_amount' => $founderShareTotal,
                    ]
                );

                $investor->increment('returned_amount', $actualPayout);
                $investor->refresh();

                if ($investor->returned_amount >= $investor->return_cap_amount) {
                    $investor->update([
                        'status' => Investment::STATUS_EXITED,
                        'profit_share_percent' => 0,
                    ]);
                }

                $results['distributions'][] = [
                    'investor_id' => $investor->id,
                    'investor_name' => $investor->investor_name,
                    'payout' => $actualPayout,
                    'exited' => $investor->status === Investment::STATUS_EXITED,
                ];
            }

            $results['period'] = $period;
            $results['profit_pool'] = $profitPool;
            $results['total_investor_share'] = $totalInvestorShare;
            $results['founder_share'] = $founderShareTotal;
        });

        if (!isset($results['distributions'])) {
            $results['distributions'] = [];
        }

        return $results;
    }
}
