<x-app-layout>
    <x-slot name="title">Leads</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold text-white">Marketing → Leads</h1>
        </div>

        <div class="bg-slate-800/60 backdrop-blur border border-slate-700/50 rounded-2xl overflow-hidden max-md:overflow-x-auto">
            <div class="overflow-x-auto">
                <table class="w-full max-md:min-w-[600px]">
                    <thead class="bg-slate-800/80 border-b border-slate-700/50">
                        <tr>
                            <th class="text-left px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Name</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Email</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Phone</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Interested type</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Message</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($leads as $lead)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="px-5 py-4 font-medium text-white">{{ $lead->name }}</td>
                                <td class="px-5 py-4 text-slate-400">{{ $lead->email }}</td>
                                <td class="px-5 py-4 text-slate-400">{{ $lead->phone ?? '—' }}</td>
                                <td class="px-5 py-4 text-slate-400">{{ $lead->interested_project_type ?? '—' }}</td>
                                <td class="px-5 py-4 text-slate-400 max-w-xs truncate" title="{{ $lead->message }}">{{ Str::limit($lead->message, 40) ?: '—' }}</td>
                                <td class="px-5 py-4 text-slate-500 text-sm">{{ $lead->created_at->format('M j, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-slate-500">No leads yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-slate-700/50">
                {{ $leads->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
