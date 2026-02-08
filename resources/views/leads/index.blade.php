<x-app-layout>
    <x-slot name="title">Leads</x-slot>

    <div class="space-y-6" x-data="{
        selectedLead: null,
        openModal(lead) {
            this.selectedLead = lead;
        },
        closeModal() {
            this.selectedLead = null;
        },
        async updateStatus(leadId, form) {
            const fd = new FormData(form);
            const r = await fetch(form.action, { method: 'PATCH', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } });
            if (!r.ok) return;
            const d = await r.json();
            if (this.selectedLead && this.selectedLead.id == leadId) this.selectedLead.status = d.status;
            const row = document.querySelector('[data-lead-id=\'' + leadId + '\']');
            if (row) {
                const badge = row.querySelector('[data-status-badge]');
                if (badge) badge.textContent = d.label;
            }
        }
    }" @keydown.escape.window="closeModal()">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold text-white">Marketing → Leads</h1>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('leads.export') }}" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700 font-medium text-sm transition">Export CSV</a>
                <a href="{{ route('leads.export.xlsx') }}" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700 font-medium text-sm transition">Export XLSX</a>
            </div>
        </div>

        <form action="{{ route('leads.index') }}" method="GET" class="flex flex-wrap items-center gap-4 max-md:w-full max-md:gap-3">
            <label for="leads-search" class="sr-only">Search leads</label>
            <input type="text" name="search" id="leads-search" value="{{ old('search', $search ?? '') }}" placeholder="Search by name, email, phone, type, message, ID…" class="flex-1 min-w-[200px] max-w-md rounded-xl bg-slate-800 border border-slate-600 text-white px-4 py-2.5 text-sm placeholder-slate-500 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 max-md:w-full max-md:max-w-none">
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium text-sm transition">Search</button>
            @if(isset($search) && $search !== '')
                <a href="{{ route('leads.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700 font-medium text-sm transition">Clear</a>
            @endif
        </form>

        <div class="bg-slate-800/60 backdrop-blur border border-slate-700/50 rounded-2xl overflow-hidden max-md:overflow-x-auto">
            <div class="overflow-x-auto">
                <table class="w-full max-md:min-w-[640px]">
                    <thead class="bg-slate-800/80 border-b border-slate-700/50">
                        <tr>
                            <th class="text-left px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Name</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Email</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Phone</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Interested type</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($leads as $lead)
                            <tr data-lead-id="{{ $lead->id }}"
                                class="hover:bg-slate-800/40 transition cursor-pointer"
                                @click="openModal({
                                    id: {{ $lead->id }},
                                    name: {{ json_encode($lead->name) }},
                                    email: {{ json_encode($lead->email) }},
                                    phone: {{ json_encode($lead->phone) }},
                                    interested_project_type: {{ json_encode($lead->interested_project_type) }},
                                    message: {{ json_encode($lead->message) }},
                                    status: {{ json_encode($lead->status ?? 'new') }},
                                    created_at: {{ json_encode($lead->created_at->format('M j, Y H:i')) }}
                                })">
                                <td class="px-5 py-4 font-medium text-white">{{ $lead->name }}</td>
                                <td class="px-5 py-4 text-slate-400">{{ $lead->email }}</td>
                                <td class="px-5 py-4 text-slate-400">{{ $lead->phone ?? '—' }}</td>
                                <td class="px-5 py-4 text-slate-400">{{ $lead->interested_project_type ?? '—' }}</td>
                                <td class="px-5 py-4">
                                    <span data-status-badge class="px-2.5 py-0.5 rounded-lg text-xs font-medium
                                        {{ ($lead->status ?? 'new') === 'new' ? 'bg-sky-500/20 text-sky-400' : '' }}
                                        {{ ($lead->status ?? '') === 'contacted' ? 'bg-amber-500/20 text-amber-400' : '' }}
                                        {{ ($lead->status ?? '') === 'closed' ? 'bg-emerald-500/20 text-emerald-400' : '' }}
                                    ">{{ \App\Models\Lead::statusLabel($lead->status ?? 'new') }}</span>
                                </td>
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

        {{-- Modal: lead details + description + status --}}
        <div x-show="selectedLead"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             @click.self="closeModal()">
            <div x-show="selectedLead"
                 x-transition
                 class="bg-slate-800 border border-slate-700 rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-hidden flex flex-col"
                 @click.stop>
                <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-white" x-text="selectedLead && selectedLead.name"></h2>
                    <button type="button" @click="closeModal()" class="p-2 text-slate-400 hover:text-white rounded-lg transition">✕</button>
                </div>
                <div class="p-6 overflow-y-auto space-y-4">
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Email</p>
                        <p class="text-slate-300 mt-0.5" x-text="selectedLead && selectedLead.email"></p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Phone</p>
                        <p class="text-slate-300 mt-0.5" x-text="selectedLead && (selectedLead.phone || '—')"></p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Interested project type</p>
                        <p class="text-slate-300 mt-0.5" x-text="selectedLead && (selectedLead.interested_project_type || '—')"></p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Description / Message</p>
                        <p class="text-slate-300 mt-1.5 whitespace-pre-wrap break-words" x-text="selectedLead && (selectedLead.message || '—')"></p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-2">Status</p>
                        <template x-if="selectedLead">
                            <form :action="'{{ url('marketing/leads') }}/' + selectedLead.id" method="post" @submit.prevent="updateStatus(selectedLead.id, $event.target)">
                                @csrf
                                @method('PATCH')
                                <select name="status"
                                        class="w-full rounded-xl bg-slate-700 border border-slate-600 text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                                        x-model="selectedLead.status"
                                        @change="updateStatus(selectedLead.id, $event.target.form)">
                                    @foreach(\App\Models\Lead::statusOptions() as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </template>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Date</p>
                        <p class="text-slate-400 text-sm mt-0.5" x-text="selectedLead && selectedLead.created_at"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
