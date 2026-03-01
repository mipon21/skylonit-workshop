<x-app-layout>
    <x-slot name="title">{{ $client->name }}</x-slot>

    <script type="application/json" id="client-projects-initial-data">{!! json_encode(['projectsData' => $projectsData ?? []]) !!}</script>
    <script>
        function registerClientProjectsPage() {
            Alpine.data('clientProjectsPage', function () {
                const el = document.getElementById('client-projects-initial-data');
                const initial = el ? JSON.parse(el.textContent) : { projectsData: [] };
                return {
                    projectsData: initial.projectsData || [],
                    searchText: '',
                    statusFilter: 'all',
                    paymentStatusFilter: 'all',
                    get listAfterSearch() {
                        let list = this.projectsData;
                        const q = (this.searchText || '').toLowerCase().trim();
                        if (q) {
                            list = list.filter(function (p) {
                                const name = (p.project_name || '').toLowerCase();
                                const code = (p.project_code || '').toLowerCase();
                                const client = (p.client_name || '').toLowerCase();
                                const id = String(p.id);
                                return name.includes(q) || code.includes(q) || client.includes(q) || id.includes(q);
                            });
                        }
                        return list;
                    },
                    get filteredIds() {
                        let list = this.listAfterSearch;
                        if (this.statusFilter !== 'all') {
                            list = list.filter(function (p) { return p.status === this.statusFilter; }.bind(this));
                        }
                        if (this.paymentStatusFilter !== 'all') {
                            list = list.filter(function (p) { return p.payment_status === this.paymentStatusFilter; }.bind(this));
                        }
                        return list.map(function (p) { return p.id; });
                    },
                    get statusCountAll() { return this.listAfterSearch.length; },
                    get statusCountPending() { return this.listAfterSearch.filter(function (p) { return p.status === 'Pending'; }).length; },
                    get statusCountRunning() { return this.listAfterSearch.filter(function (p) { return p.status === 'Running'; }).length; },
                    get statusCountComplete() { return this.listAfterSearch.filter(function (p) { return p.status === 'Complete'; }).length; },
                    get statusCountOnHold() { return this.listAfterSearch.filter(function (p) { return p.status === 'On Hold'; }).length; },
                    get paymentCountAll() { return this.listAfterSearch.length; },
                    get paymentCountUnpaid() { return this.listAfterSearch.filter(function (p) { return p.payment_status === 'unpaid'; }).length; },
                    get paymentCountPartial() { return this.listAfterSearch.filter(function (p) { return p.payment_status === 'partial'; }).length; },
                    get paymentCountPaid() { return this.listAfterSearch.filter(function (p) { return p.payment_status === 'paid'; }).length; }
                };
            });
        }
        if (window.Alpine) registerClientProjectsPage(); else document.addEventListener('alpine:init', registerClientProjectsPage);
    </script>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('clients.index') }}" class="theme-text-secondary theme-hover-primary text-sm mb-2 inline-block">← Clients</a>
                <h1 class="text-2xl font-semibold theme-text-primary">{{ $client->name }}</h1>
            </div>
            <div class="flex gap-2 max-md:flex-wrap max-md:w-full">
                <a href="{{ route('clients.edit', $client) }}" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover max-md:flex-1 max-md:text-center">Edit</a>
                <a href="{{ route('projects.create') }}?client_id={{ $client->id }}" class="px-4 py-2.5 rounded-xl theme-btn-primary font-medium max-md:flex-1 max-md:text-center">New Project</a>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4 max-md:grid-cols-1 max-md:gap-3">
            <div class="theme-card-bg-only theme-border border rounded-2xl p-5 max-md:p-4">
                <h2 class="text-sm font-medium theme-text-secondary mb-3">Contact</h2>
                <p class="theme-text-secondary">Phone: {{ $client->phone ?? '—' }}</p>
                <p class="theme-text-secondary">Email: {{ $client->email ?? '—' }}</p>
                @if($client->fb_link)<p class="text-orange-400 mt-1"><a href="{{ $client->fb_link }}" target="_blank" rel="noopener">Facebook</a></p>@endif
                @if($client->whatsapp_link)<p class="text-orange-400 mt-1"><a href="{{ $client->whatsapp_link }}" target="_blank" rel="noopener">WhatsApp</a></p>@endif
            </div>
            @if($client->address || $client->kyc)
            <div class="theme-card-bg-only theme-border border rounded-2xl p-5">
                <h2 class="text-sm font-medium theme-text-secondary mb-3">Details</h2>
                @if($client->address)<p class="theme-text-secondary">{{ $client->address }}</p>@endif
                @if($client->kyc)<p class="theme-text-secondary mt-1">KYC: {{ $client->kyc }}</p>@endif
            </div>
            @endif
        </div>

        <div>
            <h2 class="text-lg font-medium theme-text-primary mb-3">Projects ({{ $client->projects->count() }})</h2>
            <div class="space-y-6" x-data="clientProjectsPage()">
                <div class="flex flex-wrap items-start gap-4">
                    <label for="client-projects-search" class="sr-only">Search projects</label>
                    <input type="text" id="client-projects-search" x-model="searchText" placeholder="Search by project name, code, ID…" class="flex-1 min-w-[200px] max-w-md rounded-lg theme-bg-tertiary border theme-border theme-text-primary px-3 py-1.5 text-sm theme-input-placeholder theme-input-focus">
                    <div class="flex flex-col gap-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="theme-text-secondary text-sm">Status:</span>
                            <button type="button" @click="statusFilter = 'all'" :class="statusFilter === 'all' ? 'bg-orange-500/30 text-orange-300 border-orange-500' : 'theme-bg-tertiary theme-text-secondary theme-border theme-hover-primary'" class="px-3 py-1.5 rounded-lg border text-sm font-medium transition">All <span class="opacity-80 tabular-nums" x-text="'(' + statusCountAll + ')'"></span></button>
                            <button type="button" @click="statusFilter = 'Pending'" :class="statusFilter === 'Pending' ? 'bg-orange-500/30 text-orange-300 border-orange-500' : 'theme-bg-tertiary theme-text-secondary theme-border theme-hover-primary'" class="px-3 py-1.5 rounded-lg border text-sm font-medium transition">Pending <span class="opacity-80 tabular-nums" x-text="'(' + statusCountPending + ')'"></span></button>
                            <button type="button" @click="statusFilter = 'Running'" :class="statusFilter === 'Running' ? 'bg-orange-500/30 text-orange-300 border-orange-500' : 'theme-bg-tertiary theme-text-secondary theme-border theme-hover-primary'" class="px-3 py-1.5 rounded-lg border text-sm font-medium transition">Running <span class="opacity-80 tabular-nums" x-text="'(' + statusCountRunning + ')'"></span></button>
                            <button type="button" @click="statusFilter = 'Complete'" :class="statusFilter === 'Complete' ? 'bg-orange-500/30 text-orange-300 border-orange-500' : 'theme-bg-tertiary theme-text-secondary theme-border theme-hover-primary'" class="px-3 py-1.5 rounded-lg border text-sm font-medium transition">Complete <span class="opacity-80 tabular-nums" x-text="'(' + statusCountComplete + ')'"></span></button>
                            <button type="button" @click="statusFilter = 'On Hold'" :class="statusFilter === 'On Hold' ? 'bg-orange-500/30 text-orange-300 border-orange-500' : 'theme-bg-tertiary theme-text-secondary theme-border theme-hover-primary'" class="px-3 py-1.5 rounded-lg border text-sm font-medium transition">On Hold <span class="opacity-80 tabular-nums" x-text="'(' + statusCountOnHold + ')'"></span></button>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="theme-text-secondary text-sm">Payment status:</span>
                            <button type="button" @click="paymentStatusFilter = 'all'" :class="paymentStatusFilter === 'all' ? 'bg-orange-500/30 text-orange-300 border-orange-500' : 'theme-bg-tertiary theme-text-secondary theme-border theme-hover-primary'" class="px-3 py-1.5 rounded-lg border text-sm font-medium transition">All <span class="opacity-80 tabular-nums" x-text="'(' + paymentCountAll + ')'"></span></button>
                            <button type="button" @click="paymentStatusFilter = 'unpaid'" :class="paymentStatusFilter === 'unpaid' ? 'bg-orange-500/30 text-orange-300 border-orange-500' : 'theme-bg-tertiary theme-text-secondary theme-border theme-hover-primary'" class="px-3 py-1.5 rounded-lg border text-sm font-medium transition">Unpaid <span class="opacity-80 tabular-nums" x-text="'(' + paymentCountUnpaid + ')'"></span></button>
                            <button type="button" @click="paymentStatusFilter = 'partial'" :class="paymentStatusFilter === 'partial' ? 'bg-orange-500/30 text-orange-300 border-orange-500' : 'theme-bg-tertiary theme-text-secondary theme-border theme-hover-primary'" class="px-3 py-1.5 rounded-lg border text-sm font-medium transition">Partially paid <span class="opacity-80 tabular-nums" x-text="'(' + paymentCountPartial + ')'"></span></button>
                            <button type="button" @click="paymentStatusFilter = 'paid'" :class="paymentStatusFilter === 'paid' ? 'bg-orange-500/30 text-orange-300 border-orange-500' : 'theme-bg-tertiary theme-text-secondary theme-border theme-hover-primary'" class="px-3 py-1.5 rounded-lg border text-sm font-medium transition">Paid <span class="opacity-80 tabular-nums" x-text="'(' + paymentCountPaid + ')'"></span></button>
                        </div>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 max-md:grid-cols-1 max-md:gap-3">
                    @forelse($client->projects as $project)
                    <div x-show="filteredIds.includes({{ $project->id }})" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="group relative theme-bg-tertiary/60 backdrop-blur border theme-border rounded-2xl p-5 shadow-lg hover:shadow-xl hover:theme-border transition-all hover:-translate-y-0.5 overflow-visible">
                        <a href="{{ route('projects.show', $project) }}" class="block">
                            <p class="font-semibold theme-text-primary group-hover:text-orange-400 transition">{{ $project->project_name }} <span class="theme-text-muted text-sm font-normal">· {{ $project->project_code ?: $project->formatted_id }}</span></p>
                            <p class="theme-text-secondary text-sm mt-1">{{ $client->name }}</p>
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                @if($project->project_type)
                                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-medium theme-bg-tertiary theme-text-secondary border theme-border">{{ $project->project_type }}</span>
                                @endif
                                <span @class([
                                    'px-2.5 py-0.5 rounded-lg text-xs font-medium',
                                    'bg-amber-500/20 text-amber-400' => $project->status === 'Pending',
                                    'bg-orange-500/20 text-orange-400' => $project->status === 'Running',
                                    'bg-emerald-500/20 text-emerald-400' => $project->status === 'Complete',
                                    'bg-violet-500/20 text-violet-400' => $project->status === 'On Hold',
                                ])>{{ $project->status }}</span>
                                @if($project->is_net_base_negative)
                                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-medium bg-amber-500/20 text-amber-400">Net &lt; 0</span>
                                @endif
                            </div>
                            @php
                                $cardTasksTotal = $project->tasks_count ?? 0;
                                $cardTasksDone = $project->tasks_done_count ?? 0;
                                $cardProgressPercent = $cardTasksTotal > 0 ? round(($cardTasksDone / $cardTasksTotal) * 100) : 0;
                                $paymentProgressPercent = $project->contract_amount > 0 ? min(100, round(($project->total_paid / $project->contract_amount) * 100)) : 0;
                            @endphp
                            <div class="mt-3" x-data="{ progressFill: 0, progressTarget: {{ $cardProgressPercent }} }" x-init="setTimeout(() => { progressFill = progressTarget }, 150)">
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="theme-text-muted font-medium">Project progress</span>
                                    <span class="text-orange-400 tabular-nums" x-text="progressFill + '%'">{{ $cardProgressPercent }}%</span>
                                </div>
                                <div class="relative w-full overflow-hidden rounded-full border theme-border/50 theme-bg-tertiary" style="height: 13px;">
                                    <div class="absolute top-0 left-0 bottom-0 rounded-full transition-[width] duration-700 ease-out" style="height: 13px; background: linear-gradient(to right, #EF8121, #EF8121);" :style="{ width: progressFill + '%' }"></div>
                                </div>
                            </div>
                            <div class="mt-3" x-data="{ payFill: 0, payTarget: {{ $paymentProgressPercent }} }" x-init="setTimeout(() => { payFill = payTarget }, 200)">
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="theme-text-muted font-medium">Payment progress</span>
                                    <span class="text-emerald-400 tabular-nums" x-text="payFill + '%'">{{ $paymentProgressPercent }}%</span>
                                </div>
                                <div class="relative w-full overflow-hidden rounded-full border theme-border/50 theme-bg-tertiary" style="height: 13px;">
                                    <div class="absolute top-0 left-0 bottom-0 rounded-full transition-[width] duration-700 ease-out" style="height: 13px; background: linear-gradient(to right, #10b981, #34d399);" :style="{ width: payFill + '%' }"></div>
                                </div>
                            </div>
                            <div class="mt-4 pt-4 border-t theme-border flex justify-between text-sm">
                                <span class="theme-text-secondary">Contract</span>
                                <span class="theme-text-primary font-medium">৳ {{ number_format($project->contract_amount, 0) }}</span>
                            </div>
                            <div class="mt-1 flex justify-between text-sm">
                                <span class="theme-text-secondary">Due</span>
                                <span class="{{ $project->due > 0 ? 'text-amber-400' : 'text-emerald-400' }} font-medium">৳ {{ number_format($project->due, 0) }}</span>
                            </div>
                        </a>
                        <div class="mt-4 pt-4 border-t theme-border flex flex-wrap items-center gap-2">
                            <a href="{{ route('projects.show', $project) }}" class="px-3 py-1.5 rounded-lg theme-bg-tertiary/80 hover:theme-bg-tertiary theme-text-secondary theme-hover-primary text-xs font-medium">View</a>
                            <a href="{{ route('projects.edit', $project) }}" class="px-3 py-1.5 rounded-lg theme-bg-tertiary/80 hover:theme-bg-tertiary theme-text-secondary theme-hover-primary text-xs font-medium">Edit</a>
                            <form action="{{ route('projects.destroy', $project) }}" method="POST" class="inline" onsubmit="return confirm('Delete this project? All related payments, expenses, documents, tasks, bugs and notes will be removed.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-500/20 hover:bg-red-500/30 text-red-400 hover:text-red-300 text-xs font-medium">Delete</button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="sm:col-span-2 lg:col-span-3 theme-card-bg-only theme-border border rounded-2xl p-12 text-center theme-text-muted">
                        No projects yet. Add one to get started.
                    </div>
                    @endforelse
                </div>
                <p x-show="projectsData.length && filteredIds.length === 0" x-transition class="py-6 text-center theme-text-muted">No projects match your search or filter.</p>
            </div>
        </div>
    </div>
</x-app-layout>
