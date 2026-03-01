<x-app-layout>
    <x-slot name="title">Finance → Internal Expenses</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold theme-text-primary">Finance → Internal Expenses</h1>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('internal-expenses.ledger') }}" class="px-4 py-2.5 rounded-xl theme-btn-primary font-medium text-sm transition">Audit trail</a>
                <a href="{{ route('internal-expenses.report.overhead') }}" class="px-4 py-2.5 rounded-xl theme-btn-primary font-medium text-sm transition">Overhead report</a>
                <a href="{{ route('internal-expenses.report.investment') }}" class="px-4 py-2.5 rounded-xl theme-btn-primary font-medium text-sm transition">Investor capital report</a>
                <a href="{{ route('internal-expenses.create') }}" class="px-4 py-2.5 rounded-xl theme-btn-primary font-medium text-sm transition">New internal expense</a>
            </div>
        </div>

        <p class="theme-text-secondary text-sm">Company-wide costs (not project-specific). Default funding: Overhead. Fallback: Investor Capital only (explicit).</p>

        <div class="grid md:grid-cols-2 gap-4">
            <div class="theme-card-bg-only theme-border border rounded-2xl p-4">
                <h3 class="text-sm font-medium theme-text-secondary mb-1">Overhead balance</h3>
                <p class="text-xl font-semibold theme-text-primary">৳{{ number_format($overheadBalance, 0) }}</p>
            </div>
            <div class="theme-card-bg-only theme-border border rounded-2xl p-4">
                <h3 class="text-sm font-medium theme-text-secondary mb-1">Investor capital (total available)</h3>
                <p class="text-xl font-semibold text-emerald-400">৳{{ number_format(array_sum($investmentBalances), 0) }}</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('internal-expenses.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ !request('fund') ? 'bg-orange-500/20 text-orange-400' : 'theme-bg-tertiary theme-text-secondary theme-hover-primary' }}">All</a>
            <a href="{{ route('internal-expenses.index', ['fund' => 'overhead']) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ request('fund') === 'overhead' ? 'bg-orange-500/20 text-orange-400' : 'theme-bg-tertiary theme-text-secondary theme-hover-primary' }}">Overhead</a>
            <a href="{{ route('internal-expenses.index', ['fund' => 'investment']) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ request('fund') === 'investment' ? 'bg-orange-500/20 text-orange-400' : 'theme-bg-tertiary theme-text-secondary theme-hover-primary' }}">Investment</a>
        </div>

        <div class="theme-card-bg-only theme-border border rounded-2xl overflow-hidden">
            <table class="w-full">
                <thead class="theme-bg-tertiary/80 border-b theme-border">
                    <tr>
                        <th class="text-left px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Date</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Title</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Amount</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Funded from</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Created by</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @forelse($expenses as $e)
                        <tr class="hover:theme-bg-tertiary/40">
                            <td class="px-5 py-3 theme-text-secondary">{{ $e->expense_date->format('M j, Y') }}</td>
                            <td class="px-5 py-3">
                                <span class="font-medium theme-text-primary">{{ $e->title }}</span>
                                @if($e->description)
                                    <p class="text-xs theme-text-muted line-clamp-1">{{ Str::limit($e->description, 50) }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right font-medium theme-text-primary">৳{{ number_format($e->amount, 0) }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    {{ $e->funded_from === 'overhead' ? 'bg-slate-500/20 theme-text-secondary' : '' }}
                                    {{ $e->funded_from === 'profit' ? 'bg-orange-500/20 text-orange-400' : '' }}
                                    {{ $e->funded_from === 'investment' ? 'bg-emerald-500/20 text-emerald-400' : '' }}
                                ">{{ \App\Models\InternalExpense::fundedFromLabel($e->funded_from) }}</span>
                                @if($e->funded_from === 'investment' && $e->investment)
                                    <span class="theme-text-muted text-xs block">{{ $e->investment->investor_name }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 theme-text-secondary text-sm">{{ $e->creator->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('internal-expenses.edit', $e) }}" class="theme-text-secondary theme-hover-primary text-sm mr-3">Edit</a>
                                <form action="{{ route('internal-expenses.destroy', $e) }}" method="POST" class="inline" onsubmit="return confirm('Delete this internal expense? The amount will be restored to the fund balance.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center theme-text-muted">No internal expenses yet. Create one to track company-wide costs.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($expenses->hasPages())
                <div class="px-5 py-3 border-t theme-border">{{ $expenses->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
