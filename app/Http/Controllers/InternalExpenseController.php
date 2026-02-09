<?php

namespace App\Http\Controllers;

use App\Models\InternalExpense;
use App\Models\InternalFundLedger;
use App\Models\Investment;
use App\Services\FundBalanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class InternalExpenseController extends Controller
{
    public function index(Request $request, FundBalanceService $fundBalance): View
    {
        $query = InternalExpense::with(['investment', 'creator'])->orderByDesc('expense_date');

        if ($request->filled('fund')) {
            $query->where('funded_from', $request->fund);
        }

        $expenses = $query->paginate(20);
        $overheadBalance = $fundBalance->getOverheadBalance();
        $investmentBalances = $fundBalance->getAllInvestmentBalances();

        return view('internal-expenses.index', compact('expenses', 'overheadBalance', 'investmentBalances'));
    }

    public function create(FundBalanceService $fundBalance): View
    {
        $overheadBalance = $fundBalance->getOverheadBalance();
        $profitBalance = $fundBalance->getProfitPoolBalance();
        $investmentBalances = $fundBalance->getAllInvestmentBalances();
        $investments = Investment::orderBy('investor_name')->get();

        return view('internal-expenses.create', compact(
            'overheadBalance', 'profitBalance', 'investmentBalances', 'investments'
        ));
    }

    public function store(Request $request, FundBalanceService $fundBalance): RedirectResponse
    {
        $valid = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'funded_from' => 'required|in:overhead,profit,investment',
            'investment_id' => 'required_if:funded_from,investment|nullable|exists:investments,id',
        ], [
            'investment_id.required_if' => 'Please select an investment when funding from Investor Capital.',
        ]);

        $amount = (float) $valid['amount'];
        $fundedFrom = $valid['funded_from'];
        $investmentId = $fundedFrom === 'investment' && isset($valid['investment_id']) ? (int) $valid['investment_id'] : null;

        if (!$fundBalance->canFundFrom($fundedFrom, $amount, $investmentId)) {
            $fundLabel = InternalExpense::fundedFromLabel($fundedFrom);
            return back()->withInput()->withErrors([
                'amount' => "Insufficient balance in {$fundLabel}. Choose another fund or reduce the amount.",
            ]);
        }

        $fallbackFund = null;
        if ($fundedFrom !== 'overhead') {
            $fallbackFund = $fundedFrom;
        }

        $fundBalance->recordInternalExpense([
            'title' => $valid['title'],
            'description' => $valid['description'] ?? null,
            'amount' => $amount,
            'expense_date' => $valid['expense_date'],
            'funded_from' => $fundedFrom,
            'fallback_fund' => $fallbackFund,
            'investment_id' => $investmentId,
        ], $request->user()?->id);

        return redirect()->route('internal-expenses.index')->with('success', 'Internal expense recorded.');
    }

    public function edit(InternalExpense $internal_expense, FundBalanceService $fundBalance): View
    {
        $overheadBalance = $fundBalance->getOverheadBalance();
        $profitBalance = $fundBalance->getProfitPoolBalance();
        $investmentBalances = $fundBalance->getAllInvestmentBalances();
        $investments = Investment::orderBy('investor_name')->get();
        $expense = $internal_expense;

        return view('internal-expenses.edit', compact(
            'expense', 'overheadBalance', 'profitBalance', 'investmentBalances', 'investments'
        ));
    }

    public function update(Request $request, InternalExpense $internal_expense, FundBalanceService $fundBalance): RedirectResponse
    {
        $valid = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'funded_from' => 'required|in:overhead,profit,investment',
            'investment_id' => 'required_if:funded_from,investment|nullable|exists:investments,id',
        ], [
            'investment_id.required_if' => 'Please select an investment when funding from Investor Capital.',
        ]);

        $amount = (float) $valid['amount'];
        $fundedFrom = $valid['funded_from'];
        $investmentId = $fundedFrom === 'investment' && isset($valid['investment_id']) ? (int) $valid['investment_id'] : null;
        $oldAmount = $internal_expense->amount;
        $oldFund = $internal_expense->funded_from;
        $oldInvId = $internal_expense->investment_id;

        if ($amount !== $oldAmount || $fundedFrom !== $oldFund || $investmentId != $oldInvId) {
            if (!$fundBalance->canFundFromForUpdate($fundedFrom, $amount, $investmentId, $oldFund, $oldAmount, $oldInvId)) {
                $fundLabel = InternalExpense::fundedFromLabel($fundedFrom);
                return back()->withInput()->withErrors([
                    'amount' => "Insufficient balance in {$fundLabel} for this change. Reduce the amount or choose another fund.",
                ]);
            }
        }

        $fallbackFund = $fundedFrom !== 'overhead' ? $fundedFrom : null;

        DB::transaction(function () use ($internal_expense, $valid, $amount, $fundedFrom, $investmentId, $fallbackFund) {
            $internal_expense->update([
                'title' => $valid['title'],
                'description' => $valid['description'] ?? null,
                'amount' => $amount,
                'expense_date' => $valid['expense_date'],
                'funded_from' => $fundedFrom,
                'fallback_fund' => $fallbackFund,
                'investment_id' => $investmentId,
            ]);

            $ledger = $internal_expense->ledgerEntry;
            if ($ledger) {
                $ledger->delete();
            }
            InternalFundLedger::create([
                'fund_type' => $fundedFrom,
                'reference_type' => InternalFundLedger::REFERENCE_INTERNAL_EXPENSE,
                'reference_id' => $internal_expense->id,
                'investment_id' => $investmentId,
                'amount' => $amount,
                'direction' => InternalFundLedger::DIRECTION_DEBIT,
            ]);
        });

        return redirect()->route('internal-expenses.index')->with('success', 'Internal expense updated.');
    }

    public function destroy(InternalExpense $internal_expense): RedirectResponse
    {
        DB::transaction(function () use ($internal_expense) {
            $internal_expense->ledgerEntry?->delete();
            $internal_expense->delete();
        });
        return redirect()->route('internal-expenses.index')->with('success', 'Internal expense deleted.');
    }

    public function ledger(FundBalanceService $fundBalance): View
    {
        $entries = InternalFundLedger::with(['investment', 'internalExpense'])
            ->orderByDesc('created_at')
            ->paginate(30);
        $overheadBalance = $fundBalance->getOverheadBalance();
        $profitBalance = $fundBalance->getProfitPoolBalance();
        $investmentBalances = $fundBalance->getAllInvestmentBalances();

        return view('internal-expenses.ledger', compact('entries', 'overheadBalance', 'profitBalance', 'investmentBalances'));
    }

    public function reportOverhead(FundBalanceService $fundBalance): View
    {
        $balance = $fundBalance->getOverheadBalance();
        $expenses = InternalExpense::with('creator')
            ->where('funded_from', 'overhead')
            ->orderByDesc('expense_date')
            ->get();
        $totalUsed = $expenses->sum('amount');

        return view('internal-expenses.report-overhead', compact('balance', 'expenses', 'totalUsed'));
    }

    public function reportInvestment(FundBalanceService $fundBalance): View
    {
        $investments = Investment::orderBy('investor_name')->get();
        $balances = [];
        $expensesByInvestment = [];
        foreach ($investments as $inv) {
            $balances[$inv->id] = $fundBalance->getInvestmentBalance($inv->id);
            $expensesByInvestment[$inv->id] = InternalExpense::with('creator')
                ->where('funded_from', 'investment')
                ->where('investment_id', $inv->id)
                ->orderByDesc('expense_date')
                ->get();
        }

        return view('internal-expenses.report-investment', compact('investments', 'balances', 'expensesByInvestment'));
    }
}
