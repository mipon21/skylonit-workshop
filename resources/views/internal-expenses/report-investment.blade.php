<x-app-layout>
    <x-slot name="title">Investor capital usage report</x-slot>

    <div class="space-y-6">
        <div>
            <a href="{{ route('internal-expenses.index') }}" class="theme-text-secondary theme-hover-primary text-sm mb-2 inline-block">← Internal Expenses</a>
            <h1 class="text-2xl font-semibold theme-text-primary">Investor capital usage report</h1>
        </div>

        <p class="theme-text-secondary text-sm">Usage of investor capital for internal expenses. This does not affect investor return cap or profit distribution.</p>

        @foreach($investments as $inv)
            @php
                $bal = $balances[$inv->id] ?? 0;
                $expenses = $expensesByInvestment[$inv->id] ?? collect();
                $totalUsed = $expenses->sum('amount');
            @endphp
            <div class="theme-card-bg-only theme-border border rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b theme-border flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-lg font-medium theme-text-primary">{{ $inv->investor_name }}</h2>
                    <div class="flex gap-4 text-sm">
                        <span class="theme-text-secondary">Available: <span class="text-emerald-400 font-medium">৳{{ number_format($bal, 0) }}</span></span>
                        <span class="theme-text-secondary">Used for internal: <span class="text-rose-400 font-medium">৳{{ number_format($totalUsed, 0) }}</span></span>
                    </div>
                </div>
                <table class="w-full">
                    <thead class="theme-bg-tertiary/80 border-b theme-border">
                        <tr>
                            <th class="text-left px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Date</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Title</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Amount</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Created by</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($expenses as $e)
                            <tr class="hover:theme-bg-tertiary/40">
                                <td class="px-5 py-3 theme-text-secondary">{{ $e->expense_date->format('M j, Y') }}</td>
                                <td class="px-5 py-3 font-medium theme-text-primary">{{ $e->title }}</td>
                                <td class="px-5 py-3 text-right theme-text-primary">৳{{ number_format($e->amount, 0) }}</td>
                                <td class="px-5 py-3 theme-text-secondary text-sm">{{ $e->creator->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-6 text-center theme-text-muted">No internal expenses funded from this investment.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endforeach

        @if($investments->isEmpty())
            <p class="theme-text-muted">No investments to show.</p>
        @endif
    </div>
</x-app-layout>
