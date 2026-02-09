<x-app-layout>
    <x-slot name="title">Finance → Investors</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold text-white">Finance → Investors</h1>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('investments.profit-pool') }}" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700 font-medium text-sm transition">Profit Pool Dashboard</a>
                <a href="{{ route('investments.create') }}" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium text-sm transition">Add Investor / Shareholder</a>
            </div>
        </div>

        <p class="text-slate-400 text-sm bg-slate-800/40 border border-slate-700/50 rounded-xl px-4 py-3">
            <span class="text-sky-400 font-medium">Note:</span> Partner pool ({{ config('investor.investor_pool_percent', 95) }}%) splits between shareholders (weighted by share %) and investors (capped). Shareholder share % must total 100%.
        </p>
        @if($shareholderTotalError ?? null)
            <p class="text-amber-400 text-sm bg-amber-500/10 border border-amber-500/30 rounded-xl px-4 py-3">{{ $shareholderTotalError }}</p>
        @endif

        <div class="bg-slate-800/60 backdrop-blur border border-slate-700/50 rounded-2xl overflow-hidden max-md:overflow-x-auto">
            <div class="overflow-x-auto">
                <table class="w-full max-md:min-w-[640px]">
                    <thead class="bg-slate-800/80 border-b border-slate-700/50">
                        <tr>
                            <th class="text-left px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Name</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Category</th>
                            <th class="text-right px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Invested</th>
                            <th class="text-right px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Returned</th>
                            <th class="text-right px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Share %</th>
                            <th class="text-right px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Remaining cap</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Risk</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="text-right px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($investments as $inv)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="px-5 py-4">
                                    <a href="{{ route('investments.show', $inv) }}" class="font-medium text-sky-400 hover:text-sky-300">{{ $inv->investor_name }}</a>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-medium {{ $inv->category === 'investor' ? 'bg-amber-500/20 text-amber-400' : 'bg-violet-500/20 text-violet-400' }}">
                                        {{ \App\Models\Investment::categoryLabel($inv->category) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right text-slate-300">৳{{ number_format($inv->amount, 0) }}</td>
                                <td class="px-5 py-4 text-right text-slate-300">৳{{ number_format($inv->returned_amount, 0) }}</td>
                                <td class="px-5 py-4 text-right text-slate-300">
                                    @if($inv->category === 'shareholder')
                                        {{ number_format($inv->share_percent ?? 0, 1) }}%
                                    @else
                                        <span class="text-slate-500">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right text-slate-300">
                                    @if($inv->category === 'shareholder')
                                        <span class="text-slate-500">—</span>
                                    @else
                                        ৳{{ number_format($inv->remaining_cap, 0) }}
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if($inv->category === 'shareholder')
                                        <span class="text-slate-500">—</span>
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
                                        <span class="px-2.5 py-0.5 rounded-lg text-xs font-medium bg-sky-500/20 text-sky-400">Active</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-lg text-xs font-medium {{ $inv->status === 'active' ? 'bg-sky-500/20 text-sky-400' : 'bg-slate-600 text-slate-400' }}">
                                            {{ \App\Models\Investment::statusLabel($inv->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('investments.show', $inv) }}" class="text-slate-400 hover:text-white text-sm mr-3">View</a>
                                    @if($inv->status === 'active')
                                        <a href="{{ route('investments.edit', $inv) }}" class="text-slate-400 hover:text-white text-sm mr-3">Edit</a>
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
                                <td colspan="9" class="px-5 py-12 text-center text-slate-500">No investors or shareholders yet. Add one to track growth funding (profit sharing; investors are capped, shareholders are permanent).</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
