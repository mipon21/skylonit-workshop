<x-app-layout>
    <x-slot name="title">Fund ledger (audit trail)</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('internal-expenses.index') }}" class="theme-text-secondary theme-hover-primary text-sm mb-2 inline-block">← Internal Expenses</a>
                <h1 class="text-2xl font-semibold theme-text-primary">Fund ledger (audit trail)</h1>
            </div>
        </div>

        <p class="theme-text-secondary text-sm">All debits and credits to internal funds. Internal only.</p>

        <div class="grid md:grid-cols-3 gap-4 mb-6">
            <div class="theme-card-bg-only theme-border border rounded-2xl p-4">
                <h3 class="text-sm font-medium theme-text-secondary mb-1">Overhead balance</h3>
                <p class="text-xl font-semibold theme-text-primary">৳{{ number_format($overheadBalance, 0) }}</p>
            </div>
            <div class="theme-card-bg-only theme-border border rounded-2xl p-4">
                <h3 class="text-sm font-medium theme-text-secondary mb-1">Profit pool balance</h3>
                <p class="text-xl font-semibold text-orange-400">৳{{ number_format($profitBalance, 0) }}</p>
            </div>
            <div class="theme-card-bg-only theme-border border rounded-2xl p-4">
                <h3 class="text-sm font-medium theme-text-secondary mb-1">Investor capital (total)</h3>
                <p class="text-xl font-semibold text-emerald-400">৳{{ number_format(array_sum($investmentBalances), 0) }}</p>
            </div>
        </div>

        <div class="theme-card-bg-only theme-border border rounded-2xl overflow-hidden">
            <table class="w-full max-md:min-w-[640px]">
                <thead class="theme-bg-tertiary/80 border-b theme-border">
                    <tr>
                        <th class="text-left px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Date</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Fund</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Reference</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Amount</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Direction</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @forelse($entries as $e)
                        <tr class="hover:theme-bg-tertiary/40">
                            <td class="px-5 py-3 theme-text-secondary text-sm">{{ $e->created_at->format('M j, Y H:i') }}</td>
                            <td class="px-5 py-3">
                                <span class="theme-text-secondary">{{ \App\Models\InternalFundLedger::fundTypeLabel($e->fund_type) }}</span>
                                @if($e->fund_type === 'investment' && $e->investment)
                                    <span class="theme-text-muted text-xs block">{{ $e->investment->investor_name }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 theme-text-secondary text-sm">
                                {{ $e->reference_type === 'internal_expense' ? (optional($e->internalExpense)->title ?? '—') : $e->reference_type }}
                            </td>
                            <td class="px-5 py-3 text-right font-medium {{ $e->direction === 'debit' ? 'text-rose-400' : 'text-emerald-400' }}">৳{{ number_format($e->amount, 0) }}</td>
                            <td class="px-5 py-3"><span class="px-2 py-0.5 rounded text-xs {{ $e->direction === 'debit' ? 'bg-rose-500/20 text-rose-400' : 'bg-emerald-500/20 text-emerald-400' }}">{{ ucfirst($e->direction) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center theme-text-muted">No ledger entries yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($entries->hasPages())
                <div class="px-5 py-3 border-t theme-border">{{ $entries->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
