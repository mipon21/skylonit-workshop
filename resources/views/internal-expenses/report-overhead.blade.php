<x-app-layout>
    <x-slot name="title">Overhead usage report</x-slot>

    <div class="space-y-6">
        <div>
            <a href="{{ route('internal-expenses.index') }}" class="text-slate-400 hover:text-white text-sm mb-2 inline-block">← Internal Expenses</a>
            <h1 class="text-2xl font-semibold text-white">Overhead usage report</h1>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-5">
                <h3 class="text-sm font-medium text-slate-400 mb-1">Current overhead balance</h3>
                <p class="text-2xl font-semibold text-white">৳{{ number_format($balance, 0) }}</p>
            </div>
            <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-5">
                <h3 class="text-sm font-medium text-slate-400 mb-1">Total used for internal expenses</h3>
                <p class="text-2xl font-semibold text-rose-400">৳{{ number_format($totalUsed, 0) }}</p>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-medium text-white mb-3">Expenses funded from overhead</h2>
            <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl overflow-hidden">
                <table class="w-full">
                    <thead class="bg-slate-800/80 border-b border-slate-700/50">
                        <tr>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Date</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Title</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Amount</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase">Created by</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($expenses as $e)
                            <tr class="hover:bg-slate-800/40">
                                <td class="px-5 py-3 text-slate-300">{{ $e->expense_date->format('M j, Y') }}</td>
                                <td class="px-5 py-3 font-medium text-white">{{ $e->title }}</td>
                                <td class="px-5 py-3 text-right text-white">৳{{ number_format($e->amount, 0) }}</td>
                                <td class="px-5 py-3 text-slate-400 text-sm">{{ $e->creator->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-slate-500">No expenses funded from overhead yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
