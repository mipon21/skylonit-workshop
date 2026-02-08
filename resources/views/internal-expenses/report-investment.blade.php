<x-app-layout>
    <x-slot name="title">Investor capital usage report</x-slot>

    <div class="space-y-6">
        <div>
            <a href="{{ route('internal-expenses.index') }}" class="text-slate-400 hover:text-white text-sm mb-2 inline-block">← Internal Expenses</a>
            <h1 class="text-2xl font-semibold text-white">Investor capital usage report</h1>
        </div>

        <p class="text-slate-400 text-sm">Usage of investor capital for internal expenses. This does not affect investor return cap or profit distribution.</p>

        @foreach($investments as $inv)
            @php
                $bal = $balances[$inv->id] ?? 0;
                $expenses = $expensesByInvestment[$inv->id] ?? collect();
                $totalUsed = $expenses->sum('amount');
            @endphp
            <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-700/50 flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-lg font-medium text-white">{{ $inv->investor_name }}</h2>
                    <div class="flex gap-4 text-sm">
                        <span class="text-slate-400">Available: <span class="text-emerald-400 font-medium">৳{{ number_format($bal, 0) }}</span></span>
                        <span class="text-slate-400">Used for internal: <span class="text-rose-400 font-medium">৳{{ number_format($totalUsed, 0) }}</span></span>
                    </div>
                </div>
                <table class="w-full">
                    <thead class="bg-slate-800/80 border-b border-slate-700/50">
                        <tr>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Date</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Title</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Amount</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Created by</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($expenses as $e)
                            <tr class="hover:bg-slate-800/40">
                                <td class="px-5 py-3 text-slate-300">{{ $e->expense_date->format('M j, Y') }}</td>
                                <td class="px-5 py-3 font-medium text-white">{{ $e->title }}</td>
                                <td class="px-5 py-3 text-right text-white">৳{{ number_format($e->amount, 0) }}</td>
                                <td class="px-5 py-3 text-slate-400 text-sm">{{ $e->creator->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-6 text-center text-slate-500">No internal expenses funded from this investment.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endforeach

        @if($investments->isEmpty())
            <p class="text-slate-500">No investments to show.</p>
        @endif
    </div>
</x-app-layout>
