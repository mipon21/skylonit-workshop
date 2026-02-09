<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use App\Models\Setting;
use App\Services\ProfitPoolService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvestmentController extends Controller
{
    public function index(): View
    {
        $investments = Investment::orderByDesc('invested_at')->get();
        $shareholderTotalError = Investment::validateShareholderTotal();

        return view('investments.index', compact('investments', 'shareholderTotalError'));
    }

    public function create(): View
    {
        $riskTiers = config('investor.risk_tiers') ?: [
            'low' => ['share_percent' => 25, 'cap_multiplier' => 2],
            'medium' => ['share_percent' => 30, 'cap_multiplier' => 2.5],
            'high' => ['share_percent' => 40, 'cap_multiplier' => 3],
        ];
        $shareholderTotalError = Investment::validateShareholderTotal();
        return view('investments.create', compact('riskTiers', 'shareholderTotalError'));
    }

    public function store(Request $request): RedirectResponse
    {
        $valid = $request->validate([
            'category' => 'required|in:investor,shareholder',
            'investor_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'invested_at' => 'required|date',
            'risk_level' => 'required_if:category,investor|nullable|in:low,medium,high',
            'share_percent' => 'required_if:category,shareholder|nullable|numeric|min:0.01|max:100',
            'notes' => 'nullable|string|max:2000',
        ], [
            'share_percent.required_if' => 'Share percent is required for shareholders.',
        ]);

        $category = $valid['category'];
        $amount = (float) $valid['amount'];

        if ($category === Investment::CATEGORY_SHAREHOLDER) {
            $sharePercent = (float) $valid['share_percent'];
            $err = Investment::validateShareholderTotalForSave(null, $sharePercent);
            if ($err !== null) {
                return redirect()->back()->withInput()->withErrors(['share_percent' => $err]);
            }
            Investment::create([
                'category' => $category,
                'share_percent' => $sharePercent,
                'investor_name' => $valid['investor_name'],
                'amount' => $amount,
                'invested_at' => $valid['invested_at'],
                'risk_level' => 'medium',
                'profit_share_percent' => 0,
                'return_cap_multiplier' => 0,
                'return_cap_amount' => 999999999.99,
                'returned_amount' => 0,
                'status' => Investment::STATUS_ACTIVE,
                'notes' => $valid['notes'] ?? null,
            ]);
        } else {
            $tiers = config('investor.risk_tiers');
            $risk = $valid['risk_level'];
            $profitSharePercent = $tiers[$risk]['share_percent'] ?? 25;
            $capMultiplier = $tiers[$risk]['cap_multiplier'] ?? 2;
            $returnCapAmount = round($amount * $capMultiplier, 2);
            Investment::create([
                'category' => $category,
                'share_percent' => null,
                'investor_name' => $valid['investor_name'],
                'amount' => $amount,
                'invested_at' => $valid['invested_at'],
                'risk_level' => $risk,
                'profit_share_percent' => $profitSharePercent,
                'return_cap_multiplier' => $capMultiplier,
                'return_cap_amount' => $returnCapAmount,
                'returned_amount' => 0,
                'status' => Investment::STATUS_ACTIVE,
                'notes' => $valid['notes'] ?? null,
            ]);
        }

        return redirect()->route('investments.index')->with('success', Investment::categoryLabel($category) . ' added.');
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
        $shareholderTotalError = Investment::validateShareholderTotal();
        return view('investments.edit', compact('investment', 'riskTiers', 'shareholderTotalError'));
    }

    public function update(Request $request, Investment $investment): RedirectResponse
    {
        if ($investment->status === Investment::STATUS_EXITED) {
            return redirect()->route('investments.show', $investment)->with('error', 'Exited investments cannot be edited.');
        }

        $rules = [
            'investor_name' => 'required|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ];
        if ($investment->category === Investment::CATEGORY_SHAREHOLDER) {
            $rules['share_percent'] = 'required|numeric|min:0.01|max:100';
        }
        $valid = $request->validate($rules, [
            'share_percent.required' => 'Share percent is required for shareholders.',
        ]);

        if ($investment->category === Investment::CATEGORY_SHAREHOLDER) {
            $sharePercent = (float) $valid['share_percent'];
            $err = Investment::validateShareholderTotalForSave($investment->id, $sharePercent);
            if ($err !== null) {
                return redirect()->back()->withInput()->withErrors(['share_percent' => $err]);
            }
            $investment->update([
                'investor_name' => $valid['investor_name'],
                'share_percent' => $sharePercent,
                'notes' => $valid['notes'] ?? null,
            ]);
        } else {
            $investment->update([
                'investor_name' => $valid['investor_name'],
                'notes' => $valid['notes'] ?? null,
            ]);
        }

        return redirect()->route('investments.show', $investment)->with('success', 'Updated.');
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
        $leftForPartnersPool = round($totalPool * $investorPoolPercent, 2);

        $investments = Investment::all();
        $totalReturnedToInvestors = $investments->where('category', Investment::CATEGORY_INVESTOR)->sum('returned_amount');
        $totalReturnedToShareholders = $investments->where('category', Investment::CATEGORY_SHAREHOLDER)->sum('returned_amount');
        $totalReturnedToPartners = $totalReturnedToInvestors + $totalReturnedToShareholders;
        $founderRetained = round($totalPool - $totalReturnedToPartners, 2);

        $byPeriod = \App\Models\ProfitDistribution::query()
            ->selectRaw('period, MAX(profit_pool_amount) as pool, SUM(investor_share_amount) as investor_share, MAX(founder_share_amount) as founder_share')
            ->groupBy('period')
            ->orderByDesc('period')
            ->limit(24)
            ->get();

        $partnerShareholdersPercent = (int) (Setting::get('investor_partner_shareholders_percent') ?? config('investor.partner_shareholders_percent', 50));
        $partnerInvestorsPercent = (int) (Setting::get('investor_partner_investors_percent') ?? config('investor.partner_investors_percent', 50));

        return view('investments.profit-pool', [
            'totalPool' => $totalPool,
            'ownerShareFromPool' => $ownerShareFromPool,
            'leftForInvestorsPool' => $leftForPartnersPool,
            'totalReturnedToInvestors' => $totalReturnedToInvestors,
            'totalReturnedToShareholders' => $totalReturnedToShareholders,
            'totalReturnedToPartners' => $totalReturnedToPartners,
            'founderRetained' => $founderRetained,
            'byPeriod' => $byPeriod,
            'partnerShareholdersPercent' => $partnerShareholdersPercent,
            'partnerInvestorsPercent' => $partnerInvestorsPercent,
        ]);
    }

    /**
     * Update partner pool split (shareholder % vs investor % of the partner pool).
     */
    public function updatePartnerPoolSplit(Request $request): RedirectResponse
    {
        $valid = $request->validate([
            'partner_shareholders_percent' => 'required|numeric|min:0|max:100',
            'partner_investors_percent' => 'required|numeric|min:0|max:100',
        ]);

        $shareholders = (float) $valid['partner_shareholders_percent'];
        $investors = (float) $valid['partner_investors_percent'];
        $total = round($shareholders + $investors, 2);

        if (abs($total - 100) > 0.01) {
            return redirect()->route('investments.profit-pool')
                ->with('error', "Shareholder % + Investor % must equal 100% (currently {$total}%).");
        }

        Setting::set('investor_partner_shareholders_percent', $shareholders);
        Setting::set('investor_partner_investors_percent', $investors);

        return redirect()->route('investments.profit-pool')->with('success', 'Partner pool split updated.');
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
