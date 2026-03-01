<x-app-layout>
    <x-slot name="title">Finance → Internal Income</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold theme-text-primary">Finance → Internal Income</h1>
            <a href="{{ route('internal-income.create') }}" class="px-4 py-2.5 rounded-xl theme-btn-primary font-medium text-sm transition">Add external income</a>
        </div>

        <p class="theme-text-secondary text-sm">Income sources for Overhead and Profit funds. Project contributions are derived from project payouts; external income is added manually.</p>

        @if(session('success'))<p class="px-4 py-3 rounded-xl bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</p>@endif

        <div class="grid md:grid-cols-2 gap-4">
            <div class="theme-card-bg-only theme-border border rounded-2xl p-4">
                <h3 class="text-sm font-medium theme-text-secondary mb-1">Overhead balance</h3>
                <p class="text-xl font-semibold theme-text-primary">৳{{ number_format($overheadBalance, 0) }}</p>
                <p class="theme-text-muted text-xs mt-0.5">From projects + support share cleared + external income − expenses</p>
            </div>
            <div class="theme-card-bg-only theme-border border rounded-2xl p-4">
                <h3 class="text-sm font-medium theme-text-secondary mb-1">Profit pool balance</h3>
                <p class="text-xl font-semibold text-orange-400">৳{{ number_format($profitBalance ?? 0, 0) }}</p>
                <p class="theme-text-muted text-xs mt-0.5">From projects + external income − distributions</p>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-semibold theme-text-primary mb-3">Project contributions (Overhead & Profit)</h2>
            <div class="theme-card-bg-only theme-border border rounded-2xl overflow-hidden">
                <table class="w-full">
                    <thead class="theme-bg-tertiary/80 border-b theme-border">
                        <tr>
                            <th class="text-left px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Project</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Overhead</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Profit</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @foreach($projects as $p)
                        <tr class="hover:theme-bg-tertiary/40">
                            <td class="px-5 py-3">
                                <a href="{{ route('projects.show', $p) }}" class="font-medium theme-text-primary hover:text-orange-400">{{ $p->project_name }}</a>
                                @if($p->project_code)<span class="theme-text-muted text-xs ml-1">({{ $p->project_code }})</span>@endif
                            </td>
                            <td class="px-5 py-3 text-right theme-text-secondary">৳{{ number_format($p->paid_overhead, 0) }}</td>
                            <td class="px-5 py-3 text-right text-orange-400">৳{{ number_format($p->paid_profit, 0) }}</td>
                            <td class="px-5 py-3 text-right font-medium theme-text-primary">৳{{ number_format($p->paid_overhead + $p->paid_profit, 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="theme-bg-tertiary/80 border-t theme-border">
                        <tr>
                            <td class="px-5 py-3 font-medium theme-text-secondary">Total from projects</td>
                            <td class="px-5 py-3 text-right font-medium theme-text-primary">৳{{ number_format($totalOverheadFromProjects, 0) }}</td>
                            <td class="px-5 py-3 text-right font-medium text-orange-400">৳{{ number_format($totalProfitFromProjects, 0) }}</td>
                            <td class="px-5 py-3 text-right font-semibold theme-text-primary">৳{{ number_format($totalOverheadFromProjects + $totalProfitFromProjects, 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if($supportPackageShareCleared->isNotEmpty())
        <div>
            <h2 class="text-lg font-semibold theme-text-primary mb-3">Support package share cleared (Overhead)</h2>
            <p class="theme-text-secondary text-sm mb-3">Amounts from support packages marked as share cleared. These are credited to Overhead.</p>
            <div class="theme-card-bg-only theme-border border rounded-2xl overflow-hidden">
                <table class="w-full">
                    <thead class="theme-bg-tertiary/80 border-b theme-border">
                        <tr>
                            <th class="text-left px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Date cleared</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Project</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Package</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @foreach($supportPackageShareCleared as $sp)
                        <tr class="hover:theme-bg-tertiary/40">
                            <td class="px-5 py-3 theme-text-secondary">{{ $sp->share_cleared_at?->format('M j, Y') }}</td>
                            <td class="px-5 py-3">
                                <a href="{{ route('projects.show', $sp->project) }}#support" class="font-medium theme-text-primary hover:text-orange-400">{{ $sp->project->project_name ?? '—' }}</a>
                            </td>
                            <td class="px-5 py-3 theme-text-secondary">{{ $sp->package_label }}</td>
                            <td class="px-5 py-3 text-right font-medium text-emerald-400">৳{{ number_format($sp->amount, 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="theme-bg-tertiary/80 border-t theme-border">
                        <tr>
                            <td colspan="3" class="px-5 py-3 font-medium theme-text-secondary">Total support share cleared</td>
                            <td class="px-5 py-3 text-right font-semibold theme-text-primary">৳{{ number_format($totalSupportShareCleared ?? 0, 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endif

        <div>
            <h2 class="text-lg font-semibold theme-text-primary mb-3">External income</h2>
            <div class="flex flex-wrap gap-2 mb-3">
                <a href="{{ route('internal-income.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ !request('fund') ? 'bg-orange-500/20 text-orange-400' : 'theme-bg-tertiary theme-text-secondary theme-hover-primary' }}">All</a>
                <a href="{{ route('internal-income.index', ['fund' => 'overhead']) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ request('fund') === 'overhead' ? 'bg-orange-500/20 text-orange-400' : 'theme-bg-tertiary theme-text-secondary theme-hover-primary' }}">Overhead</a>
                <a href="{{ route('internal-income.index', ['fund' => 'profit']) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ request('fund') === 'profit' ? 'bg-orange-500/20 text-orange-400' : 'theme-bg-tertiary theme-text-secondary theme-hover-primary' }}">Profit</a>
            </div>
            <div class="theme-card-bg-only theme-border border rounded-2xl overflow-hidden">
                <table class="w-full">
                    <thead class="theme-bg-tertiary/80 border-b theme-border">
                        <tr>
                            <th class="text-left px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Date</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Title</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Amount</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Fund</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Created by</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($externalIncome as $e)
                        <tr class="hover:theme-bg-tertiary/40">
                            <td class="px-5 py-3 theme-text-secondary">{{ $e->income_date->format('M j, Y') }}</td>
                            <td class="px-5 py-3">
                                <span class="font-medium theme-text-primary">{{ $e->title }}</span>
                                @if($e->description)<p class="text-xs theme-text-muted line-clamp-1">{{ Str::limit($e->description, 50) }}</p>@endif
                            </td>
                            <td class="px-5 py-3 text-right font-medium text-emerald-400">৳{{ number_format($e->amount, 0) }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium {{ $e->fund_type === 'overhead' ? 'bg-slate-500/20 theme-text-secondary' : 'bg-orange-500/20 text-orange-400' }}">{{ InternalIncome::fundTypeLabel($e->fund_type) }}</span>
                            </td>
                            <td class="px-5 py-3 theme-text-secondary text-sm">{{ $e->creator->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('internal-income.edit', $e) }}" class="theme-text-secondary theme-hover-primary text-sm mr-3">Edit</a>
                                <form action="{{ route('internal-income.destroy', $e) }}" method="POST" class="inline" onsubmit="return confirm('Delete this external income? The amount will be removed from the fund balance.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center theme-text-muted">No external income yet. Add one to credit Overhead or Profit fund.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($externalIncome->hasPages())
                <div class="px-5 py-3 border-t theme-border">{{ $externalIncome->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
