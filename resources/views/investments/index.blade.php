<x-app-layout>
    <x-slot name="title">Finance → Investors</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold theme-text-primary">Finance → Investors</h1>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('investments.profit-pool') }}" class="px-4 py-2.5 rounded-xl theme-btn-primary font-medium text-sm transition">Profit Pool Dashboard</a>
                <a href="{{ route('investments.create') }}" class="px-4 py-2.5 rounded-xl theme-btn-primary font-medium text-sm transition">Add Investor / Shareholder</a>
            </div>
        </div>

        <p class="theme-text-secondary text-sm theme-card-bg-only theme-border border rounded-xl px-4 py-3">
            <span class="text-orange-400 font-medium">Note:</span> Partner pool ({{ config('investor.investor_pool_percent', 95) }}%) splits between shareholders (weighted by share %) and investors (capped). Shareholder share % must total 100%.
        </p>
        @if($shareholderTotalError ?? null)
            <p class="text-amber-400 text-sm bg-amber-500/10 border border-amber-500/30 rounded-xl px-4 py-3">{{ $shareholderTotalError }}</p>
        @endif

        <div class="theme-bg-tertiary/60 backdrop-blur border theme-border rounded-2xl overflow-hidden max-md:overflow-x-auto">
            <div class="overflow-x-auto">
                <table class="w-full max-md:min-w-[640px]">
                    <thead class="theme-bg-tertiary/80 border-b theme-border">
                        <tr>
                            <th class="text-left px-5 py-4 text-xs font-semibold theme-text-secondary uppercase tracking-wider">Name</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold theme-text-secondary uppercase tracking-wider">Category</th>
                            <th class="text-right px-5 py-4 text-xs font-semibold theme-text-secondary uppercase tracking-wider">Invested</th>
                            <th class="text-right px-5 py-4 text-xs font-semibold theme-text-secondary uppercase tracking-wider">Returned</th>
                            <th class="text-right px-5 py-4 text-xs font-semibold theme-text-secondary uppercase tracking-wider">Share %</th>
                            <th class="text-right px-5 py-4 text-xs font-semibold theme-text-secondary uppercase tracking-wider">Remaining cap</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold theme-text-secondary uppercase tracking-wider">Risk</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold theme-text-secondary uppercase tracking-wider">Status</th>
                            <th class="text-right px-5 py-4 text-xs font-semibold theme-text-secondary uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($investments as $inv)
                            <tr class="hover:theme-bg-tertiary/40 transition">
                                <td class="px-5 py-4">
                                    <a href="{{ route('investments.show', $inv) }}" class="font-medium text-orange-400 hover:text-orange-300">{{ $inv->investor_name }}</a>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-medium {{ $inv->category === 'investor' ? 'bg-amber-500/20 text-amber-400' : 'bg-violet-500/20 text-violet-400' }}">
                                        {{ \App\Models\Investment::categoryLabel($inv->category) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right theme-text-secondary">৳{{ number_format($inv->amount, 0) }}</td>
                                <td class="px-5 py-4 text-right theme-text-secondary">৳{{ number_format($inv->returned_amount, 0) }}</td>
                                <td class="px-5 py-4 text-right theme-text-secondary">
                                    @if($inv->category === 'shareholder')
                                        {{ number_format($inv->share_percent ?? 0, 1) }}%
                                    @else
                                        <span class="theme-text-muted">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right theme-text-secondary">
                                    @if($inv->category === 'shareholder')
                                        <span class="theme-text-muted">—</span>
                                    @else
                                        ৳{{ number_format($inv->remaining_cap, 0) }}
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if($inv->category === 'shareholder')
                                        <span class="theme-text-muted">—</span>
                                    @else
                                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-medium
                                        {{ $inv->risk_level === 'low' ? 'bg-emerald-500/20 text-emerald-400' : '' }}
                                        {{ $inv->risk_level === 'medium' ? 'bg-amber-500/20 text-amber-400' : '' }}
                                        {{ $inv->risk_level === 'high' ? 'bg-rose-500/20 text-rose-400' : '' }}
                                    ">{{ \App\Models\Investment::riskLabel($inv->risk_level) }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if($inv->category === 'shareholder')
                                        <span class="px-2.5 py-0.5 rounded-lg text-xs font-medium bg-orange-500/20 text-orange-400">Active</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-lg text-xs font-medium {{ $inv->status === 'active' ? 'bg-orange-500/20 text-orange-400' : 'theme-bg-tertiary theme-text-secondary' }}">
                                            {{ \App\Models\Investment::statusLabel($inv->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('investments.show', $inv) }}" class="theme-text-secondary theme-hover-primary text-sm mr-3">View</a>
                                    @if($inv->status === 'active')
                                        <a href="{{ route('investments.edit', $inv) }}" class="theme-text-secondary theme-hover-primary text-sm mr-3">Edit</a>
                                    @endif
                                    <form action="{{ route('investments.destroy', $inv) }}" method="POST" class="inline" onsubmit="return confirm('Remove this investor? Their payout history will be deleted.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-12 text-center theme-text-muted">No investors or shareholders yet. Add one to track growth funding (profit sharing; investors are capped, shareholders are permanent).</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
