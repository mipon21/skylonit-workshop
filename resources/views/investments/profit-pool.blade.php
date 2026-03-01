<x-app-layout>
    <x-slot name="title">Profit Pool Dashboard</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('investments.index') }}" class="theme-text-secondary theme-hover-primary text-sm mb-2 inline-block">← Finance → Investors</a>
                <h1 class="text-2xl font-semibold theme-text-primary">Profit Pool Dashboard</h1>
            </div>
        </div>

        <p class="theme-text-secondary text-sm">Profit pool: {{ config('investor.founder_percent', 5) }}% chairman, {{ config('investor.investor_pool_percent', 95) }}% partner pool. Partner pool splits between shareholders (weighted by share %) and investors (capped, exit at cap). Shareholder total must be 100%.</p>

        @if(session('success'))
            <p class="text-emerald-400 text-sm bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3">{{ session('success') }}</p>
        @endif
        @if(session('error'))
            <p class="text-red-400 text-sm bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3">{{ session('error') }}</p>
        @endif

        {{-- Partner pool split: shareholder % vs investor % (of the partner pool) --}}
        <div class="bg-violet-500/10 border border-violet-500/30 rounded-2xl p-5">
            <h2 class="text-lg font-medium text-violet-400 mb-2">Partner pool split (dynamic)</h2>
            <p class="theme-text-secondary text-sm mb-4">Of the {{ config('investor.investor_pool_percent', 95) }}% partner pool, how much goes to shareholders vs investors? Must total 100%. Change anytime—e.g. 100% shareholders now, 70/30 when you add investors later.</p>
            <form action="{{ route('investments.partner-pool-split') }}" method="POST" class="flex flex-wrap items-end gap-4">
                @csrf
                <div>
                    <label for="partner_shareholders_percent" class="block text-sm font-medium theme-text-secondary mb-1">Shareholder pool (%)</label>
                    <input type="number" name="partner_shareholders_percent" id="partner_shareholders_percent" value="{{ old('partner_shareholders_percent', $partnerShareholdersPercent ?? 50) }}" step="0.01" min="0" max="100" required class="rounded-xl theme-bg-tertiary border theme-border theme-text-primary px-3 py-2 w-24 focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
                </div>
                <div>
                    <label for="partner_investors_percent" class="block text-sm font-medium theme-text-secondary mb-1">Investor pool (%)</label>
                    <input type="number" name="partner_investors_percent" id="partner_investors_percent" value="{{ old('partner_investors_percent', $partnerInvestorsPercent ?? 50) }}" step="0.01" min="0" max="100" required class="rounded-xl theme-bg-tertiary border theme-border theme-text-primary px-3 py-2 w-24 focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
                </div>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-violet-500 hover:bg-violet-600 theme-text-primary font-medium text-sm">Save split</button>
            </form>
            <p class="theme-text-muted text-xs mt-2">Current: {{ $partnerShareholdersPercent ?? 50 }}% shareholders → {{ number_format((config('investor.investor_pool_percent', 95) * ($partnerShareholdersPercent ?? 50) / 100), 1) }}% of total profit. {{ $partnerInvestorsPercent ?? 50 }}% investors → {{ number_format((config('investor.investor_pool_percent', 95) * ($partnerInvestorsPercent ?? 50) / 100), 1) }}% of total profit.</p>
        </div>

        {{-- Run distribution: clear/settle investor share for a chosen period --}}
        <div class="theme-card-bg-only theme-border border rounded-2xl p-5" x-data="{ periodType: 'monthly' }">
            <h2 class="text-lg font-medium theme-text-primary mb-3">Run distribution (settle partner share)</h2>
            <p class="theme-text-secondary text-sm mb-4">Settle partner profit share for a single month or for every month in a year. Pays active investors (capped) and shareholders (uncapped); records the breakdown.</p>
            <form action="{{ route('investments.run-distribution') }}" method="POST" class="flex flex-wrap items-end gap-4">
                @csrf
                <div class="flex flex-wrap gap-4">
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Period</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="period_type" value="monthly" x-model="periodType" class="rounded theme-border theme-bg-tertiary text-orange-500 focus:ring-orange-500">
                                <span class="theme-text-secondary">Monthly</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="period_type" value="yearly" x-model="periodType" class="rounded theme-border theme-bg-tertiary text-orange-500 focus:ring-orange-500">
                                <span class="theme-text-secondary">Yearly</span>
                            </label>
                        </div>
                    </div>
                    <div x-show="periodType === 'monthly'" x-transition>
                        <label for="run_month" class="block text-sm font-medium theme-text-secondary mb-1">Month</label>
                        <select name="month" id="run_month" class="rounded-xl theme-bg-tertiary border theme-border theme-text-primary px-3 py-2 theme-input-focus">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $m == now()->subMonth()->month ? 'selected' : '' }}>{{ now()->setMonth($m)->format('F') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="run_year" class="block text-sm font-medium theme-text-secondary mb-1">Year</label>
                        <select name="year" id="run_year" required class="rounded-xl theme-bg-tertiary border theme-border theme-text-primary px-3 py-2 theme-input-focus">
                            @foreach(range(now()->year, max(2020, now()->year - 5)) as $y)
                                <option value="{{ $y }}" {{ $y == now()->subMonth()->year ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 theme-text-primary font-medium text-sm">Run distribution</button>
                </div>
            </form>
            <p class="theme-text-muted text-xs mt-3">Monthly: runs for the chosen month only. Yearly: runs for Jan–Dec of the chosen year.</p>
        </div>

        <div class="space-y-4">
            <div class="theme-card-bg-only theme-border border rounded-2xl p-5">
                <h3 class="text-sm font-medium theme-text-secondary mb-1">Total profit pool</h3>
                <p class="text-2xl font-semibold theme-text-primary">৳{{ number_format($totalPool, 0) }}</p>
                <p class="text-xs theme-text-muted mt-1">Realized profit across all projects (15% of base)</p>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div class="bg-orange-500/10 border border-orange-500/30 rounded-2xl p-5">
                    <h3 class="text-sm font-medium text-orange-400 mb-1">Company chairman minimal salary ({{ config('investor.founder_percent', 5) }}%)</h3>
                    <p class="text-2xl font-semibold text-orange-300">৳{{ number_format($ownerShareFromPool, 0) }}</p>
                    <p class="text-xs theme-text-secondary mt-1">Company chairman takes a minimal salary from the profit pool</p>
                </div>
                <div class="theme-card-bg-only theme-border border rounded-2xl p-5">
                    <h3 class="text-sm font-medium theme-text-secondary mb-1">Partner pool ({{ config('investor.investor_pool_percent', 95) }}%)</h3>
                    <p class="text-2xl font-semibold theme-text-secondary">৳{{ number_format($leftForInvestorsPool, 0) }}</p>
                    <p class="text-xs theme-text-muted mt-1">Split: shareholders (weighted) + investors (capped)</p>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                <div class="bg-amber-500/10 border border-amber-500/30 rounded-2xl p-5">
                    <h3 class="text-sm font-medium text-amber-400 mb-1">Investor share (temporary)</h3>
                    <p class="text-2xl font-semibold text-amber-300">৳{{ number_format($totalReturnedToInvestors, 0) }}</p>
                    <p class="text-xs theme-text-muted mt-1">Returned to investors (capped)</p>
                </div>
                <div class="bg-violet-500/10 border border-violet-500/30 rounded-2xl p-5">
                    <h3 class="text-sm font-medium text-violet-400 mb-1">Shareholder share (permanent)</h3>
                    <p class="text-2xl font-semibold text-violet-300">৳{{ number_format($totalReturnedToShareholders ?? 0, 0) }}</p>
                    <p class="text-xs theme-text-muted mt-1">Returned to shareholders (uncapped)</p>
                </div>
                <div class="theme-card-bg-only theme-border border rounded-2xl p-5 md:col-span-1">
                    <h3 class="text-sm font-medium theme-text-secondary mb-1">Owner actually gets</h3>
                    <p class="text-2xl font-semibold theme-text-primary">৳{{ number_format($founderRetained, 0) }}</p>
                    <p class="text-xs theme-text-muted mt-1">{{ config('investor.founder_percent', 5) }}% of pool + any unclaimed part of the {{ config('investor.investor_pool_percent', 95) }}%</p>
                </div>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-medium theme-text-primary mb-3">Monthly breakdown</h2>
            <div class="theme-card-bg-only theme-border border rounded-2xl overflow-hidden">
                <table class="w-full max-md:min-w-[480px]">
                    <thead class="theme-bg-tertiary/80 border-b theme-border">
                        <tr>
                            <th class="text-left px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Period</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Profit pool</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Investor share</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Founder share</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($byPeriod as $row)
                            <tr class="hover:theme-bg-tertiary/40">
                                <td class="px-5 py-3 theme-text-secondary">{{ $row->period }}</td>
                                <td class="px-5 py-3 text-right theme-text-secondary">৳{{ number_format($row->pool ?? 0, 0) }}</td>
                                <td class="px-5 py-3 text-right text-emerald-400">৳{{ number_format($row->investor_share ?? 0, 0) }}</td>
                                <td class="px-5 py-3 text-right theme-text-secondary">৳{{ number_format($row->founder_share ?? 0, 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center theme-text-muted">No distribution records yet. Run <code class="theme-bg-tertiary px-1 rounded">php artisan profit:distribute --last-month</code> to generate.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
