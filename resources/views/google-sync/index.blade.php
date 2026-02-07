<x-app-layout>
    <x-slot name="title">Google Sync</x-slot>

    <div class="max-w-4xl">
        <h1 class="text-2xl font-semibold text-white mb-2">Google Sheets Sync</h1>
        <p class="text-slate-400 text-sm mb-6">Bidirectional sync with the Projects tab. ERP is the source of truth for expenses, net base, overhead, sales, developer, and profit. Sheet can edit allowed fields; payments and expense are imported from the sheet.</p>

        <div class="space-y-6">
            <div class="rounded-xl bg-slate-800/80 border border-slate-700/50 p-4">
                <h2 class="text-sm font-medium text-slate-300 mb-3">Configuration</h2>
                <dl class="grid grid-cols-1 gap-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Sheet ID</dt>
                        <dd class="font-mono text-slate-300 break-all">{{ $sheetId ?: '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Status</dt>
                        <dd>
                            @if($enabled)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-500/20 text-emerald-400">Enabled</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-500/20 text-amber-400">Disabled / Not configured</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Last sync</dt>
                        <dd class="text-slate-300">{{ $lastSync ? \Carbon\Carbon::parse($lastSync)->format('M j, Y g:i A') : 'Never' }}</dd>
                    </div>
                </dl>
            </div>

            @if($enabled)
            <div class="rounded-xl bg-slate-800/80 border border-slate-700/50 p-4">
                <form method="post" action="{{ route('google-sync.sync-now') }}" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-medium text-sm transition">
                        Sync Now
                    </button>
                </form>
                <p class="text-slate-500 text-xs mt-2">Runs ERP → Sheet then Sheet → ERP. Scheduled every 5 minutes.</p>
            </div>
            @endif

            <div class="rounded-xl bg-slate-800/80 border border-slate-700/50 overflow-hidden">
                <h2 class="text-sm font-medium text-slate-300 px-4 py-3 border-b border-slate-700/50">Recent sync logs</h2>
                <div class="overflow-x-auto max-h-80 overflow-y-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-900/80 sticky top-0">
                            <tr>
                                <th class="px-4 py-2 text-slate-500 font-medium">Time</th>
                                <th class="px-4 py-2 text-slate-500 font-medium">Direction</th>
                                <th class="px-4 py-2 text-slate-500 font-medium">Project ID</th>
                                <th class="px-4 py-2 text-slate-500 font-medium">Status</th>
                                <th class="px-4 py-2 text-slate-500 font-medium">Message</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            @forelse($logs as $log)
                            <tr class="hover:bg-slate-800/40">
                                <td class="px-4 py-2 text-slate-400">{{ $log->created_at->format('M j H:i') }}</td>
                                <td class="px-4 py-2 text-slate-400">{{ $log->direction }}</td>
                                <td class="px-4 py-2 text-slate-400">{{ $log->erp_project_id ?? '—' }}</td>
                                <td class="px-4 py-2">
                                    @if($log->status === 'success')
                                        <span class="text-emerald-400">success</span>
                                    @else
                                        <span class="text-red-400">error</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-slate-400 max-w-xs truncate" title="{{ $log->message }}">{{ $log->message }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-slate-500">No sync logs yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
