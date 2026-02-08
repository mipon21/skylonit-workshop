<x-app-layout>
    <x-slot name="title">Finance → Internal Expenses</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold text-white">Finance → Internal Expenses</h1>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('internal-expenses.ledger') }}" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700 font-medium text-sm transition">Audit trail</a>
                <a href="{{ route('internal-expenses.report.overhead') }}" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700 font-medium text-sm transition">Overhead report</a>
                <a href="{{ route('internal-expenses.report.investment') }}" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700 font-medium text-sm transition">Investor capital report</a>
                <a href="{{ route('internal-expenses.create') }}" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium text-sm transition">New internal expense</a>
            </div>
        </div>

        <p class="text-slate-400 text-sm">Company-wide costs (not project-specific). Default funding: Overhead. Fallback: Investor Capital only (explicit).</p>

        <div class="grid md:grid-cols-2 gap-4">
            <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-4">
                <h3 class="text-sm font-medium text-slate-400 mb-1">Overhead balance</h3>
                <p class="text-xl font-semibold text-white">৳{{ number_format($overheadBalance, 0) }}</p>
            </div>
            <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-4">
                <h3 class="text-sm font-medium text-slate-400 mb-1">Investor capital (total available)</h3>
                <p class="text-xl font-semibold text-emerald-400">৳{{ number_format(array_sum($investmentBalances), 0) }}</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('internal-expenses.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ !request('fund') ? 'bg-sky-500/20 text-sky-400' : 'bg-slate-800 text-slate-400 hover:text-white' }}">All</a>
            <a href="{{ route('internal-expenses.index', ['fund' => 'overhead']) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ request('fund') === 'overhead' ? 'bg-sky-500/20 text-sky-400' : 'bg-slate-800 text-slate-400 hover:text-white' }}">Overhead</a>
            <a href="{{ route('internal-expenses.index', ['fund' => 'investment']) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ request('fund') === 'investment' ? 'bg-sky-500/20 text-sky-400' : 'bg-slate-800 text-slate-400 hover:text-white' }}">Investment</a>
        </div>

        <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl overflow-hidden">
            <table class="w-full">
                <thead class="bg-slate-800/80 border-b border-slate-700/50">
                    <tr>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Date</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Title</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Amount</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Funded from</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Created by</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @forelse($expenses as $e)
                        <tr class="hover:bg-slate-800/40">
                            <td class="px-5 py-3 text-slate-300">{{ $e->expense_date->format('M j, Y') }}</td>
                            <td class="px-5 py-3">
                                <span class="font-medium text-white">{{ $e->title }}</span>
                                @if($e->description)
                                    <p class="text-xs text-slate-500 line-clamp-1">{{ Str::limit($e->description, 50) }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right font-medium text-white">৳{{ number_format($e->amount, 0) }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    {{ $e->funded_from === 'overhead' ? 'bg-slate-500/20 text-slate-300' : '' }}
                                    {{ $e->funded_from === 'profit' ? 'bg-sky-500/20 text-sky-400' : '' }}
                                    {{ $e->funded_from === 'investment' ? 'bg-emerald-500/20 text-emerald-400' : '' }}
                                ">{{ \App\Models\InternalExpense::fundedFromLabel($e->funded_from) }}</span>
                                @if($e->funded_from === 'investment' && $e->investment)
                                    <span class="text-slate-500 text-xs block">{{ $e->investment->investor_name }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-400 text-sm">{{ $e->creator->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('internal-expenses.edit', $e) }}" class="text-slate-400 hover:text-white text-sm mr-3">Edit</a>
                                <form action="{{ route('internal-expenses.destroy', $e) }}" method="POST" class="inline" onsubmit="return confirm('Delete this internal expense? The amount will be restored to the fund balance.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-500">No internal expenses yet. Create one to track company-wide costs.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($expenses->hasPages())
                <div class="px-5 py-3 border-t border-slate-700/50">{{ $expenses->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
