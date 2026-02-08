<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use App\Services\ProfitPoolService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvestmentController extends Controller
{
    public function index(): View
    {
        $investments = Investment::orderByDesc('invested_at')->get();

        return view('investments.index', compact('investments'));
    }

    public function create(): View
    {
        $riskTiers = config('investor.risk_tiers') ?: [
            'low' => ['share_percent' => 25, 'cap_multiplier' => 2],
            'medium' => ['share_percent' => 30, 'cap_multiplier' => 2.5],
            'high' => ['share_percent' => 40, 'cap_multiplier' => 3],
        ];
        return view('investments.create', compact('riskTiers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $valid = $request->validate([
            'investor_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'invested_at' => 'required|date',
            'risk_level' => 'required|in:low,medium,high',
            'notes' => 'nullable|string|max:2000',
        ]);

        $tiers = config('investor.risk_tiers');
        $risk = $valid['risk_level'];
        $sharePercent = $tiers[$risk]['share_percent'] ?? 25;
        $capMultiplier = $tiers[$risk]['cap_multiplier'] ?? 2;
        $amount = (float) $valid['amount'];
        $returnCapAmount = round($amount * $capMultiplier, 2);

        Investment::create([
            'investor_name' => $valid['investor_name'],
            'amount' => $amount,
            'invested_at' => $valid['invested_at'],
            'risk_level' => $risk,
            'profit_share_percent' => $sharePercent,
            'return_cap_multiplier' => $capMultiplier,
            'return_cap_amount' => $returnCapAmount,
            'returned_amount' => 0,
            'status' => Investment::STATUS_ACTIVE,
            'notes' => $valid['notes'] ?? null,
        ]);

        return redirect()->route('investments.index')->with('success', 'Investor added.');
    }

    public function show(Investment $investment): View
    {
        $investment->load(['profitDistributions' => fn ($q) => $q->orderByDesc('period')]);
        return view('investments.show', compact('investment'));
    }

    public function edit(Investment $investment): View|RedirectResponse
    {
        if ($investment->status === Investment::STATUS_EXITED) {
            return redirect()->route('investments.show', $investment)
                ->with('error', 'Exited investments cannot be edited.');
        }
        $riskTiers = config('investor.risk_tiers', []);
        return view('investments.edit', compact('investment', 'riskTiers'));
    }

    public function update(Request $request, Investment $investment): RedirectResponse
    {
        if ($investment->status === Investment::STATUS_EXITED) {
            return redirect()->route('investments.show', $investment)->with('error', 'Exited investments cannot be edited.');
        }

        $valid = $request->validate([
            'investor_name' => 'required|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ]);

        $investment->update([
            'investor_name' => $valid['investor_name'],
            'notes' => $valid['notes'] ?? null,
        ]);

        return redirect()->route('investments.show', $investment)->with('success', 'Investor updated.');
    }

    public function destroy(Investment $investment): RedirectResponse
    {
        $investment->delete();
        return redirect()->route('investments.index')->with('success', 'Investor removed.');
    }

    public function profitPool(ProfitPoolService $service): View
    {
        $totalPool = $service->getTotalProfitPool();
        $founderPercent = config('investor.founder_percent', 60) / 100;
        $investorPoolPercent = config('investor.investor_pool_percent', 40) / 100;

        $ownerShareFromPool = round($totalPool * $founderPercent, 2);
        $leftForInvestorsPool = round($totalPool * $investorPoolPercent, 2);

        $investments = Investment::all();
        $totalReturnedToInvestors = $investments->sum('returned_amount');
        $founderRetained = round($totalPool - $totalReturnedToInvestors, 2);

        $byPeriod = \App\Models\ProfitDistribution::query()
            ->selectRaw('period, MAX(profit_pool_amount) as pool, SUM(investor_share_amount) as investor_share, MAX(founder_share_amount) as founder_share')
            ->groupBy('period')
            ->orderByDesc('period')
            ->limit(24)
            ->get();

        return view('investments.profit-pool', [
            'totalPool' => $totalPool,
            'ownerShareFromPool' => $ownerShareFromPool,
            'leftForInvestorsPool' => $leftForInvestorsPool,
            'totalReturnedToInvestors' => $totalReturnedToInvestors,
            'founderRetained' => $founderRetained,
            'byPeriod' => $byPeriod,
        ]);
    }

    /**
     * Run profit distribution for a chosen period (single month or all months in a year).
     */
    public function runDistribution(Request $request, ProfitPoolService $service): RedirectResponse
    {
        $valid = $request->validate([
            'period_type' => 'required|in:monthly,yearly',
            'month' => 'required_if:period_type,monthly|nullable|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
        ], [
            'year.required' => 'Please select a year.',
        ]);

        $year = (int) $valid['year'];

        if ($valid['period_type'] === 'monthly') {
            $month = (int) $valid['month'];
            $service->runDistributionForMonth($year, $month);
            $periodLabel = now()->setYear($year)->setMonth($month)->format('F Y');
            return redirect()->route('investments.profit-pool')->with('success', "Distribution run for {$periodLabel}.");
        }

        // Yearly: run for each month Jan–Dec
        $runCount = 0;
        for ($m = 1; $m <= 12; $m++) {
            $service->runDistributionForMonth($year, $m);
            $runCount++;
        }
        return redirect()->route('investments.profit-pool')->with('success', "Distribution run for all 12 months of {$year}.");
    }
}
