<x-app-layout>
    <x-slot name="title">Profit Pool Dashboard</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('investments.index') }}" class="text-slate-400 hover:text-white text-sm mb-2 inline-block">← Finance → Investors</a>
                <h1 class="text-2xl font-semibold text-white">Profit Pool Dashboard</h1>
            </div>
        </div>

        <p class="text-slate-400 text-sm">Profit pool is the sum of project profits (15% share). You keep 60% as owner; 40% goes to investors (shared by risk tier, capped).</p>

        {{-- Run distribution: clear/settle investor share for a chosen period --}}
        <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-5" x-data="{ periodType: 'monthly' }">
            <h2 class="text-lg font-medium text-white mb-3">Run distribution (clear investor share)</h2>
            <p class="text-slate-400 text-sm mb-4">Settle investor profit share for a single month or for every month in a year. This calculates the profit pool for the selected period(s), pays active investors (capped), and records the breakdown.</p>
            <form action="{{ route('investments.run-distribution') }}" method="POST" class="flex flex-wrap items-end gap-4">
                @csrf
                <div class="flex flex-wrap gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Period</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="period_type" value="monthly" x-model="periodType" class="rounded border-slate-600 bg-slate-800 text-sky-500 focus:ring-sky-500">
                                <span class="text-slate-300">Monthly</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="period_type" value="yearly" x-model="periodType" class="rounded border-slate-600 bg-slate-800 text-sky-500 focus:ring-sky-500">
                                <span class="text-slate-300">Yearly</span>
                            </label>
                        </div>
                    </div>
                    <div x-show="periodType === 'monthly'" x-transition>
                        <label for="run_month" class="block text-sm font-medium text-slate-400 mb-1">Month</label>
                        <select name="month" id="run_month" class="rounded-xl bg-slate-800 border border-slate-600 text-white px-3 py-2 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $m == now()->subMonth()->month ? 'selected' : '' }}>{{ now()->setMonth($m)->format('F') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="run_year" class="block text-sm font-medium text-slate-400 mb-1">Year</label>
                        <select name="year" id="run_year" required class="rounded-xl bg-slate-800 border border-slate-600 text-white px-3 py-2 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                            @foreach(range(now()->year, max(2020, now()->year - 5)) as $y)
                                <option value="{{ $y }}" {{ $y == now()->subMonth()->year ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium text-sm">Run distribution</button>
                </div>
            </form>
            <p class="text-slate-500 text-xs mt-3">Monthly: runs for the chosen month only. Yearly: runs for Jan–Dec of the chosen year.</p>
        </div>

        <div class="space-y-4">
            <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-5">
                <h3 class="text-sm font-medium text-slate-400 mb-1">Total profit pool</h3>
                <p class="text-2xl font-semibold text-white">৳{{ number_format($totalPool, 0) }}</p>
                <p class="text-xs text-slate-500 mt-1">Realized profit across all projects (15% of base)</p>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div class="bg-sky-500/10 border border-sky-500/30 rounded-2xl p-5">
                    <h3 class="text-sm font-medium text-sky-400 mb-1">Owner takes (60%)</h3>
                    <p class="text-2xl font-semibold text-sky-300">৳{{ number_format($ownerShareFromPool, 0) }}</p>
                    <p class="text-xs text-slate-400 mt-1">Your share from the total profit pool above</p>
                </div>
                <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-5">
                    <h3 class="text-sm font-medium text-slate-400 mb-1">Left for investors (40%)</h3>
                    <p class="text-2xl font-semibold text-slate-300">৳{{ number_format($leftForInvestorsPool, 0) }}</p>
                    <p class="text-xs text-slate-500 mt-1">From this amount investors are paid (capped by terms)</p>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-5">
                    <h3 class="text-sm font-medium text-slate-400 mb-1">Returned to investors</h3>
                    <p class="text-2xl font-semibold text-emerald-400">৳{{ number_format($totalReturnedToInvestors, 0) }}</p>
                    <p class="text-xs text-slate-500 mt-1">Actually paid from the 40% pool (capped)</p>
                </div>
                <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-5">
                    <h3 class="text-sm font-medium text-slate-400 mb-1">Owner actually gets</h3>
                    <p class="text-2xl font-semibold text-white">৳{{ number_format($founderRetained, 0) }}</p>
                    <p class="text-xs text-slate-500 mt-1">60% of pool + any uncapped part of the 40%</p>
                </div>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-medium text-white mb-3">Monthly breakdown</h2>
            <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl overflow-hidden">
                <table class="w-full max-md:min-w-[480px]">
                    <thead class="bg-slate-800/80 border-b border-slate-700/50">
                        <tr>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Period</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Profit pool</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Investor share</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Founder share</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($byPeriod as $row)
                            <tr class="hover:bg-slate-800/40">
                                <td class="px-5 py-3 text-slate-300">{{ $row->period }}</td>
                                <td class="px-5 py-3 text-right text-slate-300">৳{{ number_format($row->pool ?? 0, 0) }}</td>
                                <td class="px-5 py-3 text-right text-emerald-400">৳{{ number_format($row->investor_share ?? 0, 0) }}</td>
                                <td class="px-5 py-3 text-right text-slate-400">৳{{ number_format($row->founder_share ?? 0, 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-slate-500">No distribution records yet. Run <code class="bg-slate-700 px-1 rounded">php artisan profit:distribute --last-month</code> to generate.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
