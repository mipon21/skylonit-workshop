<x-app-layout>
    <x-slot name="title">Leads</x-slot>

    @php
        $leadsData = $leads->getCollection()->map(function ($l) {
            return [
                'id' => $l->id,
                'name' => $l->name,
                'email' => $l->email,
                'phone' => $l->phone,
                'interested_project_type' => $l->interested_project_type,
                'message' => $l->message,
                'status' => $l->status ?? 'new',
                'created_at' => $l->created_at->format('M j, Y H:i'),
            ];
        })->values()->all();
    @endphp
    <script type="application/json" id="leads-page-data">{!! json_encode($leadsData) !!}</script>
    <script>
        function registerLeadsPage() {
            Alpine.data('leadsPage', function () {
                const el = document.getElementById('leads-page-data');
                const leadsList = el ? JSON.parse(el.textContent) : [];
                return {
                    leadsList: leadsList,
                    selectedLead: null,
                    openModal(leadId) {
                        const lead = this.leadsList.find(function (l) { return l.id == leadId; });
                        this.selectedLead = lead ? { ...lead } : null;
                    },
                    closeModal() {
                        this.selectedLead = null;
                    },
                    async updateStatus(leadId, form) {
                        const select = form.querySelector('select[name="status"]');
                        if (!select) return;
                        const status = select.value;
                        if (!status) return;
                        const fd = new FormData();
                        fd.append('_token', document.querySelector('meta[name=csrf-token]').content);
                        fd.append('_method', 'PATCH');
                        fd.append('status', status);
                        const routeTemplate = '{{ route("leads.update", ["lead" => 999999]) }}';
                        const actionUrl = routeTemplate.replace('999999', String(leadId));
                        try {
                            const r = await fetch(actionUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, credentials: 'same-origin' });
                            if (!r.ok) {
                                alert('Failed to update status.');
                                return;
                            }
                            const d = await r.json();
                            const inList = this.leadsList.find(function (l) { return l.id == leadId; });
                            if (inList) inList.status = d.status;
                            if (this.selectedLead && this.selectedLead.id == leadId) this.selectedLead.status = d.status;
                            const row = document.querySelector('[data-lead-id="' + leadId + '"]');
                            if (row) {
                                const badge = row.querySelector('[data-status-badge]');
                                if (badge) {
                                    badge.textContent = d.label;
                                    badge.className = 'px-2.5 py-0.5 rounded-lg text-xs font-medium' + (d.status === 'new' ? ' bg-orange-500/20 text-orange-400' : d.status === 'contacted' ? ' bg-amber-500/20 text-amber-400' : ' bg-emerald-500/20 text-emerald-400');
                                }
                            }
                        } catch (err) {
                            alert('An error occurred.');
                        }
                    }
                };
            });
        }
        if (window.Alpine) registerLeadsPage(); else document.addEventListener('alpine:init', registerLeadsPage);
    </script>

    <div class="space-y-6" x-data="leadsPage()" @keydown.escape.window="closeModal()">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold theme-text-primary">Marketing → Leads</h1>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('leads.export') }}" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover font-medium text-sm transition">Export CSV</a>
                <a href="{{ route('leads.export.xlsx') }}" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover font-medium text-sm transition">Export XLSX</a>
            </div>
        </div>

        <form action="{{ route('leads.index') }}" method="GET" class="flex flex-wrap items-center gap-4 max-md:w-full max-md:gap-3">
            <label for="leads-search" class="sr-only">Search leads</label>
            <input type="text" name="search" id="leads-search" value="{{ old('search', $search ?? '') }}" placeholder="Search by name, email, phone, type, message, ID…" class="flex-1 min-w-[200px] max-w-md rounded-xl theme-bg-tertiary border theme-border theme-text-primary px-4 py-2.5 text-sm theme-input-placeholder theme-input-focus max-md:w-full max-md:max-w-none">
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 theme-text-primary font-medium text-sm transition">Search</button>
            @if(isset($search) && $search !== '')
                <a href="{{ route('leads.index') }}" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover font-medium text-sm transition">Clear</a>
            @endif
        </form>

        <div class="theme-bg-tertiary/60 backdrop-blur border theme-border rounded-2xl overflow-hidden max-md:overflow-x-auto">
            <div class="overflow-x-auto">
                <table class="w-full max-md:min-w-[640px]">
                    <thead class="theme-bg-tertiary/80 border-b theme-border">
                        <tr>
                            <th class="text-left px-5 py-4 text-xs font-semibold theme-text-secondary uppercase tracking-wider">Name</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold theme-text-secondary uppercase tracking-wider">Email</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold theme-text-secondary uppercase tracking-wider">Phone</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold theme-text-secondary uppercase tracking-wider">Interested type</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold theme-text-secondary uppercase tracking-wider">Status</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold theme-text-secondary uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($leads as $lead)
                            <tr data-lead-id="{{ $lead->id }}"
                                class="hover:theme-bg-tertiary/40 transition cursor-pointer"
                                @click="openModal({{ $lead->id }})"
                                role="button"
                                tabindex="0"
                                @keydown.enter.prevent="openModal({{ $lead->id }})"
                                @keydown.space.prevent="openModal({{ $lead->id }})">
                                <td class="px-5 py-4 font-medium theme-text-primary">{{ $lead->name }}</td>
                                <td class="px-5 py-4 theme-text-secondary">{{ $lead->email }}</td>
                                <td class="px-5 py-4 theme-text-secondary">{{ $lead->phone ?? '—' }}</td>
                                <td class="px-5 py-4 theme-text-secondary">{{ $lead->interested_project_type ?? '—' }}</td>
                                <td class="px-5 py-4">
                                    <span data-status-badge class="px-2.5 py-0.5 rounded-lg text-xs font-medium
                                        {{ ($lead->status ?? 'new') === 'new' ? 'bg-orange-500/20 text-orange-400' : '' }}
                                        {{ ($lead->status ?? '') === 'contacted' ? 'bg-amber-500/20 text-amber-400' : '' }}
                                        {{ ($lead->status ?? '') === 'closed' ? 'bg-emerald-500/20 text-emerald-400' : '' }}
                                    ">{{ \App\Models\Lead::statusLabel($lead->status ?? 'new') }}</span>
                                </td>
                                <td class="px-5 py-4 theme-text-muted text-sm">{{ $lead->created_at->format('M j, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center theme-text-muted">No leads yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t theme-border">
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
                 class="theme-bg-tertiary border theme-border rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-hidden flex flex-col"
                 @click.stop>
                <div class="px-6 py-4 border-b theme-border flex items-center justify-between">
                    <h2 class="text-lg font-semibold theme-text-primary" x-text="selectedLead && selectedLead.name"></h2>
                    <button type="button" @click="closeModal()" class="p-2 theme-text-secondary theme-hover-primary rounded-lg transition">✕</button>
                </div>
                <div class="p-6 overflow-y-auto space-y-4">
                    <div>
                        <p class="text-xs font-medium theme-text-muted uppercase tracking-wide">Email</p>
                        <p class="theme-text-secondary mt-0.5" x-text="selectedLead && selectedLead.email"></p>
                    </div>
                    <div>
                        <p class="text-xs font-medium theme-text-muted uppercase tracking-wide">Phone</p>
                        <p class="theme-text-secondary mt-0.5" x-text="selectedLead && (selectedLead.phone || '—')"></p>
                    </div>
                    <div>
                        <p class="text-xs font-medium theme-text-muted uppercase tracking-wide">Interested project type</p>
                        <p class="theme-text-secondary mt-0.5" x-text="selectedLead && (selectedLead.interested_project_type || '—')"></p>
                    </div>
                    <div>
                        <p class="text-xs font-medium theme-text-muted uppercase tracking-wide">Description / Message</p>
                        <p class="theme-text-secondary mt-1.5 whitespace-pre-wrap break-words" x-text="selectedLead && (selectedLead.message || '—')"></p>
                    </div>
                    <div>
                        <p class="text-xs font-medium theme-text-muted uppercase tracking-wide mb-2">Status</p>
                        <template x-if="selectedLead">
                            <form method="post" @submit.prevent="updateStatus(selectedLead.id, $event.target)">
                                @csrf
                                @method('PATCH')
                                <select name="status"
                                        class="w-full rounded-xl theme-bg-tertiary border theme-border theme-text-primary px-4 py-2.5 text-sm theme-input-focus"
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
                        <p class="text-xs font-medium theme-text-muted uppercase tracking-wide">Date</p>
                        <p class="theme-text-secondary text-sm mt-0.5" x-text="selectedLead && selectedLead.created_at"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
