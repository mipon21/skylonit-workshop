<x-app-layout>
    <x-slot name="title">Google Sync</x-slot>

    <div class="max-w-4xl">
        <h1 class="text-2xl font-semibold theme-text-primary mb-2">Google Sheets Sync</h1>
        <p class="theme-text-secondary text-sm mb-6">Bidirectional sync with the Projects tab. ERP is the source of truth for expenses, net base, overhead, sales, developer, and profit. Sheet can edit allowed fields; payments and expense are imported from the sheet.</p>

        <div class="space-y-6">
            <div class="rounded-xl theme-bg-tertiary/80 border theme-border p-4">
                <h2 class="text-sm font-medium theme-text-secondary mb-3">Configuration</h2>
                <dl class="grid grid-cols-1 gap-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="theme-text-muted">Sheet ID</dt>
                        <dd class="font-mono theme-text-secondary break-all">{{ $sheetId ?: '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="theme-text-muted">Status</dt>
                        <dd>
                            @if($enabled)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-500/20 text-emerald-400">Enabled</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-500/20 text-amber-400">Disabled / Not configured</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="theme-text-muted">Last sync</dt>
                        <dd class="theme-text-secondary">{{ $lastSync ? \Carbon\Carbon::parse($lastSync)->format('M j, Y g:i A') : 'Never' }}</dd>
                    </div>
                </dl>
            </div>

            @if($enabled)
            <div class="rounded-xl theme-bg-tertiary/80 border theme-border p-4">
                <form method="post" action="{{ route('google-sync.sync-now') }}" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-orange-600 hover:bg-orange-500 theme-text-primary font-medium text-sm transition">
                        Sync Now
                    </button>
                </form>
                <p class="theme-text-muted text-xs mt-2">Runs ERP → Sheet then Sheet → ERP. Scheduled every 5 minutes.</p>
            </div>
            @endif

            <div class="rounded-xl theme-bg-tertiary/80 border theme-border overflow-hidden">
                <h2 class="text-sm font-medium theme-text-secondary px-4 py-3 border-b theme-border">Recent sync logs</h2>
                <div class="overflow-x-auto max-h-80 overflow-y-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="theme-input-bg/80 sticky top-0">
                            <tr>
                                <th class="px-4 py-2 theme-text-muted font-medium">Time</th>
                                <th class="px-4 py-2 theme-text-muted font-medium">Direction</th>
                                <th class="px-4 py-2 theme-text-muted font-medium">Project ID</th>
                                <th class="px-4 py-2 theme-text-muted font-medium">Status</th>
                                <th class="px-4 py-2 theme-text-muted font-medium">Message</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            @forelse($logs as $log)
                            <tr class="hover:theme-bg-tertiary/40">
                                <td class="px-4 py-2 theme-text-secondary">{{ $log->created_at->format('M j H:i') }}</td>
                                <td class="px-4 py-2 theme-text-secondary">{{ $log->direction }}</td>
                                <td class="px-4 py-2 theme-text-secondary">{{ $log->erp_project_id ?? '—' }}</td>
                                <td class="px-4 py-2">
                                    @if($log->status === 'success')
                                        <span class="text-emerald-400">success</span>
                                    @else
                                        <span class="text-red-400">error</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 theme-text-secondary max-w-xs truncate" title="{{ $log->message }}">{{ $log->message }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center theme-text-muted">No sync logs yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
