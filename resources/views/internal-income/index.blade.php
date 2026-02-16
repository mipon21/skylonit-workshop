<x-app-layout>
    <x-slot name="title">Finance → Internal Income</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold text-white">Finance → Internal Income</h1>
            <a href="{{ route('internal-income.create') }}" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium text-sm transition">Add external income</a>
        </div>

        <p class="text-slate-400 text-sm">Income sources for Overhead and Profit funds. Project contributions are derived from project payouts; external income is added manually.</p>

        @if(session('success'))<p class="px-4 py-3 rounded-xl bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</p>@endif

        <div class="grid md:grid-cols-2 gap-4">
            <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-4">
                <h3 class="text-sm font-medium text-slate-400 mb-1">Overhead balance</h3>
                <p class="text-xl font-semibold text-white">৳{{ number_format($overheadBalance, 0) }}</p>
                <p class="text-slate-500 text-xs mt-0.5">From projects + support share cleared + external income − expenses</p>
            </div>
            <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-4">
                <h3 class="text-sm font-medium text-slate-400 mb-1">Profit pool balance</h3>
                <p class="text-xl font-semibold text-sky-400">৳{{ number_format($profitBalance ?? 0, 0) }}</p>
                <p class="text-slate-500 text-xs mt-0.5">From projects + external income − distributions</p>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-white mb-3">Project contributions (Overhead & Profit)</h2>
            <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl overflow-hidden">
                <table class="w-full">
                    <thead class="bg-slate-800/80 border-b border-slate-700/50">
                        <tr>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Project</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Overhead</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Profit</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @foreach($projects as $p)
                        <tr class="hover:bg-slate-800/40">
                            <td class="px-5 py-3">
                                <a href="{{ route('projects.show', $p) }}" class="font-medium text-white hover:text-sky-400">{{ $p->project_name }}</a>
                                @if($p->project_code)<span class="text-slate-500 text-xs ml-1">({{ $p->project_code }})</span>@endif
                            </td>
                            <td class="px-5 py-3 text-right text-slate-300">৳{{ number_format($p->paid_overhead, 0) }}</td>
                            <td class="px-5 py-3 text-right text-sky-400">৳{{ number_format($p->paid_profit, 0) }}</td>
                            <td class="px-5 py-3 text-right font-medium text-white">৳{{ number_format($p->paid_overhead + $p->paid_profit, 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-800/80 border-t border-slate-700/50">
                        <tr>
                            <td class="px-5 py-3 font-medium text-slate-300">Total from projects</td>
                            <td class="px-5 py-3 text-right font-medium text-white">৳{{ number_format($totalOverheadFromProjects, 0) }}</td>
                            <td class="px-5 py-3 text-right font-medium text-sky-400">৳{{ number_format($totalProfitFromProjects, 0) }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-white">৳{{ number_format($totalOverheadFromProjects + $totalProfitFromProjects, 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if($supportPackageShareCleared->isNotEmpty())
        <div>
            <h2 class="text-lg font-semibold text-white mb-3">Support package share cleared (Overhead)</h2>
            <p class="text-slate-400 text-sm mb-3">Amounts from support packages marked as share cleared. These are credited to Overhead.</p>
            <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl overflow-hidden">
                <table class="w-full">
                    <thead class="bg-slate-800/80 border-b border-slate-700/50">
                        <tr>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Date cleared</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Project</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Package</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @foreach($supportPackageShareCleared as $sp)
                        <tr class="hover:bg-slate-800/40">
                            <td class="px-5 py-3 text-slate-300">{{ $sp->share_cleared_at?->format('M j, Y') }}</td>
                            <td class="px-5 py-3">
                                <a href="{{ route('projects.show', $sp->project) }}#support" class="font-medium text-white hover:text-sky-400">{{ $sp->project->project_name ?? '—' }}</a>
                            </td>
                            <td class="px-5 py-3 text-slate-400">{{ $sp->package_label }}</td>
                            <td class="px-5 py-3 text-right font-medium text-emerald-400">৳{{ number_format($sp->amount, 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-800/80 border-t border-slate-700/50">
                        <tr>
                            <td colspan="3" class="px-5 py-3 font-medium text-slate-300">Total support share cleared</td>
                            <td class="px-5 py-3 text-right font-semibold text-white">৳{{ number_format($totalSupportShareCleared ?? 0, 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endif

        <div>
            <h2 class="text-lg font-semibold text-white mb-3">External income</h2>
            <div class="flex flex-wrap gap-2 mb-3">
                <a href="{{ route('internal-income.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ !request('fund') ? 'bg-sky-500/20 text-sky-400' : 'bg-slate-800 text-slate-400 hover:text-white' }}">All</a>
                <a href="{{ route('internal-income.index', ['fund' => 'overhead']) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ request('fund') === 'overhead' ? 'bg-sky-500/20 text-sky-400' : 'bg-slate-800 text-slate-400 hover:text-white' }}">Overhead</a>
                <a href="{{ route('internal-income.index', ['fund' => 'profit']) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ request('fund') === 'profit' ? 'bg-sky-500/20 text-sky-400' : 'bg-slate-800 text-slate-400 hover:text-white' }}">Profit</a>
            </div>
            <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl overflow-hidden">
                <table class="w-full">
                    <thead class="bg-slate-800/80 border-b border-slate-700/50">
                        <tr>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Date</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Title</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Amount</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Fund</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Created by</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($externalIncome as $e)
                        <tr class="hover:bg-slate-800/40">
                            <td class="px-5 py-3 text-slate-300">{{ $e->income_date->format('M j, Y') }}</td>
                            <td class="px-5 py-3">
                                <span class="font-medium text-white">{{ $e->title }}</span>
                                @if($e->description)<p class="text-xs text-slate-500 line-clamp-1">{{ Str::limit($e->description, 50) }}</p>@endif
                            </td>
                            <td class="px-5 py-3 text-right font-medium text-emerald-400">৳{{ number_format($e->amount, 0) }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium {{ $e->fund_type === 'overhead' ? 'bg-slate-500/20 text-slate-300' : 'bg-sky-500/20 text-sky-400' }}">{{ InternalIncome::fundTypeLabel($e->fund_type) }}</span>
                            </td>
                            <td class="px-5 py-3 text-slate-400 text-sm">{{ $e->creator->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('internal-income.edit', $e) }}" class="text-slate-400 hover:text-white text-sm mr-3">Edit</a>
                                <form action="{{ route('internal-income.destroy', $e) }}" method="POST" class="inline" onsubmit="return confirm('Delete this external income? The amount will be removed from the fund balance.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-500">No external income yet. Add one to credit Overhead or Profit fund.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($externalIncome->hasPages())
                <div class="px-5 py-3 border-t border-slate-700/50">{{ $externalIncome->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
