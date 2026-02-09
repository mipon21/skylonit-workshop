<x-app-layout>
    <x-slot name="title">{{ $investment->investor_name }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('investments.index') }}" class="text-slate-400 hover:text-white text-sm mb-2 inline-block">← Finance → Investors</a>
                <h1 class="text-2xl font-semibold text-white">{{ $investment->investor_name }}</h1>
            </div>
            <div class="flex gap-2">
                @if($investment->category === 'shareholder' || $investment->status === 'active')
                    <a href="{{ route('investments.edit', $investment) }}" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700 font-medium text-sm">Edit</a>
                @endif
                <form action="{{ route('investments.destroy', $investment) }}" method="POST" class="inline" onsubmit="return confirm('Remove this investor? Their payout history will be deleted.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2.5 rounded-xl border border-red-500/50 text-red-400 hover:bg-red-500/20 font-medium text-sm">Delete</button>
                </form>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-5">
                <h2 class="text-sm font-medium text-slate-400 mb-3">Investment terms</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Category</dt><dd><span class="px-2 py-0.5 rounded text-xs font-medium {{ $investment->category === 'investor' ? 'bg-amber-500/20 text-amber-400' : 'bg-violet-500/20 text-violet-400' }}">{{ \App\Models\Investment::categoryLabel($investment->category) }}</span></dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Amount</dt><dd class="text-white font-medium">৳{{ number_format($investment->amount, 0) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Invested at</dt><dd class="text-slate-300">{{ $investment->invested_at->format('M j, Y') }}</dd></div>
                    @if($investment->category === 'shareholder')
                    <div class="flex justify-between"><dt class="text-slate-500">Share %</dt><dd class="text-slate-300">{{ number_format($investment->share_percent ?? 0, 1) }}%</dd></div>
                    @else
                    <div class="flex justify-between"><dt class="text-slate-500">Risk</dt><dd><span class="px-2 py-0.5 rounded text-xs font-medium {{ $investment->risk_level === 'low' ? 'bg-emerald-500/20 text-emerald-400' : ($investment->risk_level === 'medium' ? 'bg-amber-500/20 text-amber-400' : 'bg-rose-500/20 text-rose-400') }}">{{ \App\Models\Investment::riskLabel($investment->risk_level) }}</span></dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Profit share</dt><dd class="text-slate-300">{{ number_format($investment->profit_share_percent, 0) }}%</dd></div>
                    @endif
                    @if($investment->category === 'investor')
                    <div class="flex justify-between"><dt class="text-slate-500">Return cap</dt><dd class="text-slate-300">৳{{ number_format($investment->return_cap_amount, 0) }} ({{ $investment->return_cap_multiplier }}×)</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Remaining cap</dt><dd class="text-slate-300">৳{{ number_format($investment->remaining_cap, 0) }}</dd></div>
                    @endif
                    <div class="flex justify-between"><dt class="text-slate-500">Returned to date</dt><dd class="text-emerald-400 font-medium">৳{{ number_format($investment->returned_amount, 0) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Status</dt><dd><span class="px-2 py-0.5 rounded text-xs font-medium {{ ($investment->category === 'shareholder' || $investment->status === 'active') ? 'bg-sky-500/20 text-sky-400' : 'bg-slate-600 text-slate-400' }}">{{ $investment->category === 'shareholder' ? 'Active' : \App\Models\Investment::statusLabel($investment->status) }}</span></dd></div>
                </dl>
            </div>
            @if($investment->notes)
                <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-5">
                    <h2 class="text-sm font-medium text-slate-400 mb-3">Notes</h2>
                    <p class="text-slate-300 text-sm whitespace-pre-wrap">{{ $investment->notes }}</p>
                </div>
            @endif
        </div>

        <div>
            <h2 class="text-lg font-medium text-white mb-3">Payout history</h2>
            <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl overflow-hidden">
                <table class="w-full">
                    <thead class="bg-slate-800/80 border-b border-slate-700/50">
                        <tr>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Period</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Profit pool</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Investor share</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Founder share</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($investment->profitDistributions as $d)
                            <tr class="hover:bg-slate-800/40">
                                <td class="px-5 py-3 text-slate-300">{{ $d->period }}</td>
                                <td class="px-5 py-3 text-right text-slate-300">৳{{ number_format($d->profit_pool_amount, 0) }}</td>
                                <td class="px-5 py-3 text-right text-emerald-400">৳{{ number_format($d->investor_share_amount, 0) }}</td>
                                <td class="px-5 py-3 text-right text-slate-400">৳{{ number_format($d->founder_share_amount, 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-slate-500">No distributions yet. Run the monthly profit distribution job to record payouts.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
