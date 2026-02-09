<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\Payment;
use App\Models\ProfitDistribution;
use App\Models\Setting;
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
     * Total profit pool (all-time) = sum of all projects' paid_profit. Only projects
     * with profit payout status "paid" (or partial with amount_paid) contribute. Dynamic, not stored.
     */
    public function getTotalProfitPool(): float
    {
        $total = \App\Models\Project::with([])->get()->sum(function ($p) {
            // Developer–Sales mode projects contribute ZERO to profit pool.
            return $p->is_developer_sales_mode ? 0 : $p->paid_profit;
        });
        return round($total, 2);
    }

    /**
     * Run profit distribution for a given month.
     * - Owner: 60% (fixed)
     * - Partner pool: 40%
     * - Shareholder pool = partner_pool × partner_shareholders_percent; split by share_percent (weighted)
     * - Investor pool = partner_pool × partner_investors_percent; split by profit_share_percent, capped, exit at cap
     */
    public function runDistributionForMonth(int $year, int $month): array
    {
        $period = sprintf('%04d-%02d', $year, $month);
        $profitPool = $this->getProfitPoolForMonth($year, $month);

        $shareholders = Investment::getShareholders();
        $activeInvestors = Investment::getActiveInvestors();

        $founderPercent = config('investor.founder_percent', 60) / 100;
        $partnerPoolPercent = config('investor.investor_pool_percent', 40) / 100;
        $partnerPoolTotal = round($profitPool * $partnerPoolPercent, 2);

        $partnerShareholdersPercent = (float) (Setting::get('investor_partner_shareholders_percent') ?? config('investor.partner_shareholders_percent', 50)) / 100;
        $partnerInvestorsPercent = (float) (Setting::get('investor_partner_investors_percent') ?? config('investor.partner_investors_percent', 50)) / 100;

        if ($shareholders->isEmpty()) {
            $shareholderPool = 0.0;
            $investorPool = $partnerPoolTotal;
        } elseif ($activeInvestors->isEmpty()) {
            $shareholderPool = $partnerPoolTotal;
            $investorPool = 0.0;
        } else {
            $shareholderPool = round($partnerPoolTotal * $partnerShareholdersPercent, 2);
            $investorPool = round($partnerPoolTotal * $partnerInvestorsPercent, 2);
        }

        $results = [];
        $payouts = [];

        DB::transaction(function () use ($period, $profitPool, $shareholders, $activeInvestors, $shareholderPool, $investorPool, &$results, &$payouts) {
            $totalPartnerShare = 0.0;

            foreach ($shareholders as $sh) {
                $sharePercent = max(0, (float) ($sh->share_percent ?? 0));
                $amount = round($shareholderPool * ($sharePercent / 100), 2);
                $totalPartnerShare += $amount;
                $payouts[] = ['partner' => $sh, 'actualPayout' => $amount];
            }

            $sumInvestorWeights = $activeInvestors->sum('profit_share_percent');
            $sumInvestorWeights = $sumInvestorWeights > 0 ? $sumInvestorWeights : 1;

            foreach ($activeInvestors as $inv) {
                $weight = $inv->profit_share_percent / $sumInvestorWeights;
                $rawPayout = round($investorPool * $weight, 2);
                $remainingCap = max(0, $inv->return_cap_amount - $inv->returned_amount);
                $actualPayout = round(min($rawPayout, $remainingCap), 2);
                $totalPartnerShare += $actualPayout;
                $payouts[] = ['partner' => $inv, 'actualPayout' => $actualPayout];
            }

            $founderShareTotal = round($profitPool - $totalPartnerShare, 2);

            foreach ($payouts as ['partner' => $partner, 'actualPayout' => $actualPayout]) {
                ProfitDistribution::updateOrCreate(
                    [
                        'investor_id' => $partner->id,
                        'period' => $period,
                    ],
                    [
                        'profit_pool_amount' => $profitPool,
                        'investor_share_amount' => $actualPayout,
                        'founder_share_amount' => $founderShareTotal,
                    ]
                );

                $partner->increment('returned_amount', $actualPayout);
                $partner->refresh();

                $exited = false;
                if ($partner->category === Investment::CATEGORY_INVESTOR
                    && $partner->returned_amount >= $partner->return_cap_amount) {
                    $partner->update([
                        'status' => Investment::STATUS_EXITED,
                        'profit_share_percent' => 0,
                    ]);
                    $exited = true;
                }

                $results['distributions'][] = [
                    'investor_id' => $partner->id,
                    'investor_name' => $partner->investor_name,
                    'payout' => $actualPayout,
                    'exited' => $exited,
                ];
            }

            $results['period'] = $period;
            $results['profit_pool'] = $profitPool;
            $results['total_investor_share'] = $totalPartnerShare;
            $results['founder_share'] = $founderShareTotal;
        });

        if (!isset($results['distributions'])) {
            $results['distributions'] = [];
        }

        return $results;
    }
}
