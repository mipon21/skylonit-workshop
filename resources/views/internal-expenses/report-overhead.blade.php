<x-app-layout>
    <x-slot name="title">Overhead usage report</x-slot>

    <div class="space-y-6">
        <div>
            <a href="{{ route('internal-expenses.index') }}" class="theme-text-secondary theme-hover-primary text-sm mb-2 inline-block">← Internal Expenses</a>
            <h1 class="text-2xl font-semibold theme-text-primary">Overhead usage report</h1>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div class="theme-card-bg-only theme-border border rounded-2xl p-5">
                <h3 class="text-sm font-medium theme-text-secondary mb-1">Current overhead balance</h3>
                <p class="text-2xl font-semibold theme-text-primary">৳{{ number_format($balance, 0) }}</p>
            </div>
            <div class="theme-card-bg-only theme-border border rounded-2xl p-5">
                <h3 class="text-sm font-medium theme-text-secondary mb-1">Total used for internal expenses</h3>
                <p class="text-2xl font-semibold text-rose-400">৳{{ number_format($totalUsed, 0) }}</p>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-medium theme-text-primary mb-3">Expenses funded from overhead</h2>
            <div class="theme-card-bg-only theme-border border rounded-2xl overflow-hidden">
                <table class="w-full">
                    <thead class="theme-bg-tertiary/80 border-b theme-border">
                        <tr>
                            <th class="text-left px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Date</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Title</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Amount</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold theme-text-secondary uppercase">Created by</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($expenses as $e)
                            <tr class="hover:theme-bg-tertiary/40">
                                <td class="px-5 py-3 theme-text-secondary">{{ $e->expense_date->format('M j, Y') }}</td>
                                <td class="px-5 py-3 font-medium theme-text-primary">{{ $e->title }}</td>
                                <td class="px-5 py-3 text-right theme-text-primary">৳{{ number_format($e->amount, 0) }}</td>
                                <td class="px-5 py-3 theme-text-secondary text-sm">{{ $e->creator->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center theme-text-muted">No expenses funded from overhead yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
