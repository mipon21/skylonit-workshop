<?php

namespace App\Http\Controllers;

use App\Models\InternalFundLedger;
use App\Models\InternalIncome;
use App\Models\Project;
use App\Models\SupportPackage;
use App\Services\FundBalanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InternalIncomeController extends Controller
{
    public function index(Request $request, FundBalanceService $fundBalance): View
    {
        $projects = Project::with(['client', 'projectPayouts'])
            ->orderByDesc('created_at')
            ->get();

        $query = InternalIncome::with('creator')->orderByDesc('income_date');
        if ($request->filled('fund')) {
            $query->where('fund_type', $request->fund);
        }
        $externalIncome = $query->paginate(20);

        $overheadBalance = $fundBalance->getOverheadBalance();
        $profitBalance = $fundBalance->getProfitPoolBalance();

        $totalOverheadFromProjects = $projects->sum(fn (Project $p) => $p->paid_overhead);
        $totalProfitFromProjects = $projects->sum(fn (Project $p) => $p->paid_profit);

        $supportPackageShareCleared = SupportPackage::with('project')
            ->whereNotNull('share_cleared_at')
            ->orderByDesc('share_cleared_at')
            ->get();
        $totalSupportShareCleared = $supportPackageShareCleared->sum('amount');

        return view('internal-income.index', compact(
            'projects',
            'externalIncome',
            'overheadBalance',
            'profitBalance',
            'totalOverheadFromProjects',
            'totalProfitFromProjects',
            'supportPackageShareCleared',
            'totalSupportShareCleared'
        ));
    }

    public function create(FundBalanceService $fundBalance): View
    {
        $overheadBalance = $fundBalance->getOverheadBalance();
        $profitBalance = $fundBalance->getProfitPoolBalance();

        return view('internal-income.create', compact('overheadBalance', 'profitBalance'));
    }

    public function store(Request $request): RedirectResponse
    {
        $valid = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'amount' => 'required|numeric|min:0.01',
            'income_date' => 'required|date',
            'fund_type' => 'required|in:overhead,profit',
        ]);

        $amount = (float) $valid['amount'];
        $fundType = $valid['fund_type'];

        DB::transaction(function () use ($valid, $amount, $fundType, $request) {
            $income = InternalIncome::create([
                'title' => $valid['title'],
                'description' => $valid['description'] ?? null,
                'amount' => $amount,
                'income_date' => $valid['income_date'],
                'fund_type' => $fundType,
                'created_by' => $request->user()?->id,
            ]);

            InternalFundLedger::create([
                'fund_type' => $fundType,
                'reference_type' => InternalFundLedger::REFERENCE_EXTERNAL_INCOME,
                'reference_id' => $income->id,
                'amount' => $amount,
                'direction' => InternalFundLedger::DIRECTION_CREDIT,
            ]);
        });

        return redirect()->route('internal-income.index')->with('success', 'External income recorded.');
    }

    public function edit(InternalIncome $internal_income, FundBalanceService $fundBalance): View
    {
        $overheadBalance = $fundBalance->getOverheadBalance();
        $profitBalance = $fundBalance->getProfitPoolBalance();
        $income = $internal_income;

        return view('internal-income.edit', compact('income', 'overheadBalance', 'profitBalance'));
    }

    public function update(Request $request, InternalIncome $internal_income): RedirectResponse
    {
        $valid = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'amount' => 'required|numeric|min:0.01',
            'income_date' => 'required|date',
            'fund_type' => 'required|in:overhead,profit',
        ]);

        $amount = (float) $valid['amount'];
        $fundType = $valid['fund_type'];
        $oldAmount = $internal_income->amount;
        $oldFund = $internal_income->fund_type;

        DB::transaction(function () use ($internal_income, $valid, $amount, $fundType) {
            $internal_income->update([
                'title' => $valid['title'],
                'description' => $valid['description'] ?? null,
                'amount' => $amount,
                'income_date' => $valid['income_date'],
                'fund_type' => $fundType,
            ]);

            $ledger = $internal_income->ledgerEntry;
            if ($ledger) {
                $ledger->delete();
            }
            InternalFundLedger::create([
                'fund_type' => $fundType,
                'reference_type' => InternalFundLedger::REFERENCE_EXTERNAL_INCOME,
                'reference_id' => $internal_income->id,
                'amount' => $amount,
                'direction' => InternalFundLedger::DIRECTION_CREDIT,
            ]);
        });

        return redirect()->route('internal-income.index')->with('success', 'External income updated.');
    }

    public function destroy(InternalIncome $internal_income): RedirectResponse
    {
        DB::transaction(function () use ($internal_income) {
            $internal_income->ledgerEntry?->delete();
            $internal_income->delete();
        });
        return redirect()->route('internal-income.index')->with('success', 'External income deleted.');
    }
}
