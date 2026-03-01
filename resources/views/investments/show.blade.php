<x-app-layout>
    <x-slot name="title">{{ $investment->investor_name }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('investments.index') }}" class="theme-text-secondary theme-hover-primary text-sm mb-2 inline-block">← Finance → Investors</a>
                <h1 class="text-2xl font-semibold theme-text-primary">{{ $investment->investor_name }}</h1>
            </div>
            <div class="flex gap-2">
                @if($investment->category === 'shareholder' || $investment->status === 'active')
                    <a href="{{ route('investments.edit', $investment) }}" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover font-medium text-sm">Edit</a>
                @endif
                <form action="{{ route('investments.destroy', $investment) }}" method="POST" class="inline" onsubmit="return confirm('Remove this investor? Their payout history will be deleted.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2.5 rounded-xl border border-red-500/50 text-red-400 hover:bg-red-500/20 font-medium text-sm">Delete</button>
                </form>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div class="theme-card-bg-only theme-border border rounded-2xl p-5">
                <h2 class="text-sm font-medium theme-text-secondary mb-3">Investment terms</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="theme-text-muted">Category</dt><dd><span class="px-2 py-0.5 rounded text-xs font-medium {{ $investment->category === 'investor' ? 'bg-amber-500/20 text-amber-400' : 'bg-violet-500/20 text-violet-400' }}">{{ \App\Models\Investment::categoryLabel($investment->category) }}</span></dd></div>
                    <div class="flex justify-between"><dt class="theme-text-muted">Amount</dt><dd class="theme-text-primary font-medium">৳{{ number_format($investment->amount, 0) }}</dd></div>
                    <div class="flex justify-between"><dt class="theme-text-muted">Invested at</dt><dd class="theme-text-secondary">{{ $investment->invested_at->format('M j, Y') }}</dd></div>
                    @if($investment->category === 'shareholder')
                    <div class="flex justify-between"><dt class="theme-text-muted">Share %</dt><dd class="theme-text-secondary">{{ number_format($investment->share_percent ?? 0, 1) }}%</dd></div>
                    @else
                    <div class="flex justify-between"><dt class="theme-text-muted">Risk</dt><dd><span class="px-2 py-0.5 rounded text-xs font-medium {{ $investment->risk_level === 'low' ? 'bg-emerald-500/20 text-emerald-400' : ($investment->risk_level === 'medium' ? 'bg-amber-500/20 text-amber-400' : 'bg-rose-500/20 text-rose-400') }}">{{ \App\Models\Investment::riskLabel($investment->risk_level) }}</span></dd></div>
                    <div class="flex justify-between"><dt class="theme-text-muted">Profit share</dt><dd class="theme-text-secondary">{{ number_format($investment->profit_share_percent, 0) }}%</dd></div>
                    @endif
                    @if($investment->category === 'investor')
                    <div class="flex justify-between"><dt class="theme-text-muted">Return cap</dt><dd class="theme-text-secondary">৳{{ number_format($investment->return_cap_amount, 0) }} ({{ $investment->return_cap_multiplier }}×)</dd></div>
                    <div class="flex justify-between"><dt class="theme-text-muted">Remaining cap</dt><dd class="theme-text-secondary">৳{{ number_format($investment->remaining_cap, 0) }}</dd></div>
                    @endif
                    <div class="flex justify-between"><dt class="theme-text-muted">Returned to date</dt><dd class="text-emerald-400 font-medium">৳{{ number_format($investment->returned_amount, 0) }}</dd></div>
                    <div class="flex justify-between"><dt class="theme-text-muted">Status</dt><dd><span class="px-2 py-0.5 rounded text-xs font-medium {{ ($investment->category === 'shareholder' || $investment->status === 'active') ? 'bg-orange-500/20 text-orange-400' : 'theme-bg-tertiary theme-text-secondary' }}">{{ $investment->category === 'shareholder' ? 'Active' : \App\Models\Investment::statusLabel($investment->status) }}</span></dd></div>
                </dl>
            </div>
            @if($investment->notes)
                <div class="theme-card-bg-only theme-border border rounded-2xl p-5">
                    <h2 class="text-sm font-medium theme-text-secondary mb-3">Notes</h2>
                    <p class="theme-text-secondary text-sm whitespace-pre-wrap">{{ $investment->notes }}</p>
                </div>
            @endif
        </div>

        <div>
            <h2 class="text-lg font-medium theme-text-primary mb-3">Payout history</h2>
            <div class="theme-card-bg-only theme-border border rounded-2xl overflow-hidden">
                <table class="w-full">
                    <thead class="theme-bg-tertiary/80 border-b theme-border">
                        <tr>
                            <th class="text-left px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Period</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Profit pool</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Investor share</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Founder share</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($investment->profitDistributions as $d)
                            <tr class="hover:theme-bg-tertiary/40">
                                <td class="px-5 py-3 theme-text-secondary">{{ $d->period }}</td>
                                <td class="px-5 py-3 text-right theme-text-secondary">৳{{ number_format($d->profit_pool_amount, 0) }}</td>
                                <td class="px-5 py-3 text-right text-emerald-400">৳{{ number_format($d->investor_share_amount, 0) }}</td>
                                <td class="px-5 py-3 text-right theme-text-secondary">৳{{ number_format($d->founder_share_amount, 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center theme-text-muted">No distributions yet. Run the monthly profit distribution job to record payouts.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
