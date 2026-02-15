<x-app-layout>
    <x-slot name="title">Projects</x-slot>

    <script type="application/json" id="projects-initial-data">{!! json_encode(['projectsData' => $projectsData]) !!}</script>
    <script>
        function registerProjectsPage() {
            Alpine.data('projectsPage', function () {
                const el = document.getElementById('projects-initial-data');
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
        if (window.Alpine) registerProjectsPage(); else document.addEventListener('alpine:init', registerProjectsPage);
    </script>

    <div class="space-y-6" x-data="{ open: false }">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold text-white">Projects</h1>
            @if(!($isClient ?? false) && !($isDeveloper ?? false) && !($isSales ?? false))
            <button @click="open = true" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium text-sm transition">
                Add Project
            </button>
            @endif
        </div>

        <div class="space-y-6" x-data="projectsPage()">
            <div class="flex flex-wrap items-start gap-4 max-md:flex-col max-md:w-full max-md:gap-3">
                <label for="projects-search" class="sr-only">Search projects</label>
                <input type="text" id="projects-search" x-model="searchText" placeholder="Search by project name, code, client, ID…" class="flex-1 min-w-[200px] max-w-md rounded-lg bg-slate-800 border border-slate-600 text-white px-3 py-1.5 text-sm placeholder-slate-500 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 max-md:w-full max-md:max-w-none">
                <div class="flex flex-col gap-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-slate-400 text-sm">Status:</span>
                        <button type="button" @click="statusFilter = 'all'" :class="statusFilter === 'all' ? 'bg-sky-500/30 text-sky-300 border-sky-500' : 'bg-slate-800 text-slate-400 border-slate-600 hover:text-white'" class="px-3 py-1.5 rounded-lg border text-sm font-medium transition">All <span class="opacity-80 tabular-nums" x-text="'(' + statusCountAll + ')'"></span></button>
                        <button type="button" @click="statusFilter = 'Pending'" :class="statusFilter === 'Pending' ? 'bg-sky-500/30 text-sky-300 border-sky-500' : 'bg-slate-800 text-slate-400 border-slate-600 hover:text-white'" class="px-3 py-1.5 rounded-lg border text-sm font-medium transition">Pending <span class="opacity-80 tabular-nums" x-text="'(' + statusCountPending + ')'"></span></button>
                        <button type="button" @click="statusFilter = 'Running'" :class="statusFilter === 'Running' ? 'bg-sky-500/30 text-sky-300 border-sky-500' : 'bg-slate-800 text-slate-400 border-slate-600 hover:text-white'" class="px-3 py-1.5 rounded-lg border text-sm font-medium transition">Running <span class="opacity-80 tabular-nums" x-text="'(' + statusCountRunning + ')'"></span></button>
                        <button type="button" @click="statusFilter = 'Complete'" :class="statusFilter === 'Complete' ? 'bg-sky-500/30 text-sky-300 border-sky-500' : 'bg-slate-800 text-slate-400 border-slate-600 hover:text-white'" class="px-3 py-1.5 rounded-lg border text-sm font-medium transition">Complete <span class="opacity-80 tabular-nums" x-text="'(' + statusCountComplete + ')'"></span></button>
                        <button type="button" @click="statusFilter = 'On Hold'" :class="statusFilter === 'On Hold' ? 'bg-sky-500/30 text-sky-300 border-sky-500' : 'bg-slate-800 text-slate-400 border-slate-600 hover:text-white'" class="px-3 py-1.5 rounded-lg border text-sm font-medium transition">On Hold <span class="opacity-80 tabular-nums" x-text="'(' + statusCountOnHold + ')'"></span></button>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-slate-400 text-sm">Payment status:</span>
                        <button type="button" @click="paymentStatusFilter = 'all'" :class="paymentStatusFilter === 'all' ? 'bg-sky-500/30 text-sky-300 border-sky-500' : 'bg-slate-800 text-slate-400 border-slate-600 hover:text-white'" class="px-3 py-1.5 rounded-lg border text-sm font-medium transition">All <span class="opacity-80 tabular-nums" x-text="'(' + paymentCountAll + ')'"></span></button>
                        <button type="button" @click="paymentStatusFilter = 'unpaid'" :class="paymentStatusFilter === 'unpaid' ? 'bg-sky-500/30 text-sky-300 border-sky-500' : 'bg-slate-800 text-slate-400 border-slate-600 hover:text-white'" class="px-3 py-1.5 rounded-lg border text-sm font-medium transition">Unpaid <span class="opacity-80 tabular-nums" x-text="'(' + paymentCountUnpaid + ')'"></span></button>
                        <button type="button" @click="paymentStatusFilter = 'partial'" :class="paymentStatusFilter === 'partial' ? 'bg-sky-500/30 text-sky-300 border-sky-500' : 'bg-slate-800 text-slate-400 border-slate-600 hover:text-white'" class="px-3 py-1.5 rounded-lg border text-sm font-medium transition">Partially paid <span class="opacity-80 tabular-nums" x-text="'(' + paymentCountPartial + ')'"></span></button>
                        <button type="button" @click="paymentStatusFilter = 'paid'" :class="paymentStatusFilter === 'paid' ? 'bg-sky-500/30 text-sky-300 border-sky-500' : 'bg-slate-800 text-slate-400 border-slate-600 hover:text-white'" class="px-3 py-1.5 rounded-lg border text-sm font-medium transition">Paid <span class="opacity-80 tabular-nums" x-text="'(' + paymentCountPaid + ')'"></span></button>
                    </div>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 max-md:grid-cols-1 max-md:gap-3">
                @forelse($projects as $project)
                <div x-show="filteredIds.includes({{ $project->id }})" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="group relative bg-slate-800/60 backdrop-blur border border-slate-700/50 rounded-2xl p-5 shadow-lg hover:shadow-xl hover:border-slate-600 transition-all hover:-translate-y-0.5 overflow-visible max-md:p-4 {{ $project->is_pinned ? 'ring-1 ring-amber-500/40' : '' }}">
                    <a href="{{ route('projects.show', $project) }}" class="block">
                        <p class="font-semibold text-white group-hover:text-sky-400 transition">{{ $project->project_name }} <span class="text-slate-500 text-sm font-normal">· {{ $project->project_code ?: $project->formatted_id }}</span></p>
                        @if(!($isDeveloper ?? false) && !($isSales ?? false))
                        <p class="text-slate-400 text-sm mt-1">{{ $project->client->name }}</p>
                        @endif
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            @if($project->project_type)
                                <span class="px-2.5 py-0.5 rounded-lg text-xs font-medium bg-slate-600/80 text-slate-300 border border-slate-500/50">{{ $project->project_type }}</span>
                            @endif
                            <span @class([
                                'px-2.5 py-0.5 rounded-lg text-xs font-medium',
                                'bg-amber-500/20 text-amber-400' => $project->status === 'Pending',
                                'bg-sky-500/20 text-sky-400' => $project->status === 'Running',
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
                                <span class="text-slate-500 font-medium">Project progress</span>
                                <span class="text-sky-400 tabular-nums" x-text="progressFill + '%'">{{ $cardProgressPercent }}%</span>
                            </div>
                            <div class="relative w-full overflow-hidden rounded-full border border-slate-600/50 bg-slate-700/90" style="height: 13px;">
                                <div class="absolute top-0 left-0 bottom-0 rounded-full transition-[width] duration-700 ease-out" style="height: 13px; background: linear-gradient(to right, #0ea5e9, #22d3ee);" :style="{ width: progressFill + '%' }"></div>
                            </div>
                        </div>
                        @if(!($isDeveloper ?? false) && !($isSales ?? false))
                        <div class="mt-3" x-data="{ payFill: 0, payTarget: {{ $paymentProgressPercent }} }" x-init="setTimeout(() => { payFill = payTarget }, 200)">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-slate-500 font-medium">Payment progress</span>
                                <span class="payment-amount text-emerald-400 tabular-nums" x-text="payFill + '%'">{{ $paymentProgressPercent }}%</span>
                            </div>
                            <div class="relative w-full overflow-hidden rounded-full border border-slate-600/50 bg-slate-700/90" style="height: 13px;">
                                <div class="absolute top-0 left-0 bottom-0 rounded-full transition-[width] duration-700 ease-out" style="height: 13px; background: linear-gradient(to right, #10b981, #34d399);" :style="{ width: payFill + '%' }"></div>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-slate-700/50 flex justify-between text-sm">
                            <span class="text-slate-400">Contract</span>
                            <span class="payment-amount text-white font-medium">৳ {{ number_format($project->contract_amount, 0) }}</span>
                        </div>
                        <div class="mt-1 flex justify-between text-sm">
                            <span class="text-slate-400">Due</span>
                            <span class="payment-amount {{ $project->due > 0 ? 'text-amber-400' : 'text-emerald-400' }} font-medium">৳ {{ number_format($project->due, 0) }}</span>
                        </div>
                        @elseif($isDeveloper ?? false)
                        @php $devPayoutStatus = $project->getPayoutFor(\App\Models\ProjectPayout::TYPE_DEVELOPER)?->status ?? 'not_paid'; @endphp
                        <div class="mt-4 pt-4 border-t border-slate-700/50 flex justify-between text-sm">
                            <span class="text-slate-400">Your payout</span>
                            <span @class([
                                'font-medium',
                                'text-emerald-400' => $devPayoutStatus === 'paid',
                                'text-amber-400' => in_array($devPayoutStatus, ['partial', 'due', 'upcoming']),
                                'text-slate-400' => $devPayoutStatus === 'not_paid',
                            ])>{{ $devPayoutStatus === 'paid' ? 'Paid' : ($devPayoutStatus === 'partial' ? 'Partially paid' : 'Not paid') }}</span>
                        </div>
                        @elseif($isSales ?? false)
                        @php $salesPayoutStatus = $project->getPayoutFor(\App\Models\ProjectPayout::TYPE_SALES)?->status ?? 'not_paid'; @endphp
                        <div class="mt-4 pt-4 border-t border-slate-700/50 flex justify-between text-sm">
                            <span class="text-slate-400">Your payout</span>
                            <span @class([
                                'font-medium',
                                'text-emerald-400' => $salesPayoutStatus === 'paid',
                                'text-amber-400' => in_array($salesPayoutStatus, ['partial', 'due', 'upcoming']),
                                'text-slate-400' => $salesPayoutStatus === 'not_paid',
                            ])>{{ $salesPayoutStatus === 'paid' ? 'Paid' : ($salesPayoutStatus === 'partial' ? 'Partially paid' : 'Not paid') }}</span>
                        </div>
                        @endif
                    </a>
                    <div class="mt-4 pt-4 border-t border-slate-700/50 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('projects.show', $project) }}" class="px-3 py-1.5 rounded-lg bg-slate-700/80 hover:bg-slate-600 text-slate-300 hover:text-white text-xs font-medium">View</a>
                            @if(!($isClient ?? false) && !($isDeveloper ?? false) && !($isSales ?? false))
                            <a href="{{ route('projects.edit', $project) }}" class="px-3 py-1.5 rounded-lg bg-slate-700/80 hover:bg-slate-600 text-slate-300 hover:text-white text-xs font-medium">Edit</a>
                            <form action="{{ route('projects.destroy', $project) }}" method="POST" class="inline" onsubmit="return confirm('Delete this project? All related payments, expenses, documents, tasks, bugs and notes will be removed.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-500/20 hover:bg-red-500/30 text-red-400 hover:text-red-300 text-xs font-medium">Delete</button>
                            </form>
                            @endif
                        </div>
                        @if(!($isClient ?? false) && !($isDeveloper ?? false) && !($isSales ?? false))
                        <form action="{{ route('projects.pin.toggle', $project) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" title="{{ $project->is_pinned ? 'Unpin' : 'Pin to top' }}" class="p-1.5 rounded-lg {{ $project->is_pinned ? 'text-amber-400 hover:bg-amber-500/20' : 'text-slate-500 hover:text-amber-400 hover:bg-slate-700/80' }} transition">
                                @if($project->is_pinned)
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2l-2-2z"/></svg>
                                @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2l-2-2z"/></svg>
                                @endif
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="sm:col-span-2 lg:col-span-3 bg-slate-800/40 border border-slate-700/50 rounded-2xl p-12 text-center text-slate-500">
                    No projects yet. Add one to get started.
                </div>
            @endforelse
            </div>
            <p x-show="projectsData.length && filteredIds.length === 0" x-transition class="py-6 text-center text-slate-500">No projects match your search or filter.</p>
        </div>

        @if(!($isClient ?? false) && !($isDeveloper ?? false) && !($isSales ?? false))
        {{-- Add Project Modal --}}
        <div x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex min-h-full items-center justify-center p-4 max-md:p-0 max-md:items-stretch">
                <div x-show="open" x-transition class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="open = false"></div>
                <div x-show="open" x-transition class="relative w-full max-w-lg bg-slate-800 border border-slate-700 rounded-2xl shadow-xl max-h-[90vh] overflow-y-auto max-md:max-w-none max-md:max-h-full max-md:rounded-none max-md:border-0">
                    <div class="p-6" x-data="{
                        distributionSettingsOpen: false,
                        contractAmount: {{ json_encode((float) old('contract_amount', 0)) }},
                        expenseTotal: 0,
                        developerSalesMode: {{ old('developer_sales_mode', false) ? 'true' : 'false' }},
                        salesCommissionEnabled: {{ old('sales_commission_enabled', true) ? 'true' : 'false' }},
                        salesPercentage: {{ json_encode((float) old('sales_percentage', 25)) }},
                        developerPercentage: {{ json_encode((float) old('developer_percentage', 40)) }},
                        get base() { return Math.max(0, (parseFloat(this.contractAmount) || 0) - (parseFloat(this.expenseTotal) || 0)); },
                        get overheadAmount() { return this.developerSalesMode ? 0 : Math.round(this.base * 0.2 * 100) / 100; },
                        get salesAmount() { if (this.developerSalesMode) return Math.round(this.base * 0.25 * 100) / 100; return this.salesCommissionEnabled ? Math.round(this.base * (parseFloat(this.salesPercentage) || 0) / 100 * 100) / 100 : 0; },
                        get developerAmount() { if (this.developerSalesMode) return Math.round(this.base * 0.75 * 100) / 100; return Math.round(this.base * (parseFloat(this.developerPercentage) || 0) / 100 * 100) / 100; },
                        get profitAmount() { if (this.developerSalesMode) return 0; return Math.max(0, Math.round((this.base - this.overheadAmount - this.salesAmount - this.developerAmount) * 100) / 100); },
                        formatNum(n) { const x = Number(n); return isNaN(x) ? '0' : x.toLocaleString('en-BD', { maximumFractionDigits: 0 }); }
                    }">
                        <div class="flex items-center gap-2 mb-4">
                            <h2 class="text-lg font-semibold text-white">New Project</h2>
                            <button type="button" @click="distributionSettingsOpen = !distributionSettingsOpen" class="p-1.5 rounded-lg text-slate-400 hover:text-sky-400 hover:bg-slate-700/50 transition" title="Distribution settings">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </button>
                        </div>
                        <form action="{{ route('projects.store') }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-400 mb-1">Client *</label>
                                    <select name="client_id" required class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                        <option value="">Select client</option>
                                        @foreach(\App\Models\Client::orderBy('name')->get() as $c)
                                            <option value="{{ $c->id }}" {{ old('client_id', request('client_id')) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('client_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-400 mb-1">Project name *</label>
                                    <input type="text" name="project_name" value="{{ old('project_name') }}" required class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                    @error('project_name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-400 mb-1">Project code</label>
                                    <input type="text" name="project_code" value="{{ old('project_code', $nextProjectCode ?? \App\Models\Project::generateNextProjectCode()) }}" readonly class="w-full rounded-xl bg-slate-800/80 border border-slate-600 text-slate-400 px-4 py-2.5 cursor-not-allowed" title="Auto-generated, not editable">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-400 mb-1">Project type</label>
                                    <select name="project_type" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                        <option value="">Select type</option>
                                        @foreach(\App\Models\Project::PROJECT_TYPES as $type)
                                            <option value="{{ $type }}" {{ old('project_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                    @error('project_type')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-400 mb-1">Contract amount (৳) *</label>
                                    <input type="number" name="contract_amount" value="{{ old('contract_amount') }}" step="0.01" min="0" required x-model.number="contractAmount" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                    @error('contract_amount')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-400 mb-1">Contract date</label>
                                        <input type="date" name="contract_date" value="{{ old('contract_date') }}" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-400 mb-1">Delivery date</label>
                                        <input type="date" name="delivery_date" value="{{ old('delivery_date') }}" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-400 mb-1">Status</label>
                                    <select name="status" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                        <option value="Pending" {{ old('status', 'Pending') === 'Pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="Running" {{ old('status') === 'Running' ? 'selected' : '' }}>Running</option>
                                        <option value="Complete" {{ old('status') === 'Complete' ? 'selected' : '' }}>Complete</option>
                                        <option value="On Hold" {{ old('status') === 'On Hold' ? 'selected' : '' }}>On Hold</option>
                                    </select>
                                </div>
                                @if(($developers ?? collect())->isNotEmpty())
                                @php $defaultAddDeveloperIds = $developers->count() === 1 ? [$developers->first()->id] : []; @endphp
                                <div>
                                    <label class="block text-sm font-medium text-slate-400 mb-1">Assign Developers</label>
                                    <select name="developer_ids[]" multiple class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 min-h-[100px]">
                                        @foreach($developers as $dev)
                                            <option value="{{ $dev->id }}" {{ in_array($dev->id, old('developer_ids', $defaultAddDeveloperIds)) ? 'selected' : '' }}>{{ $dev->name }} ({{ $dev->email }})</option>
                                        @endforeach
                                    </select>
                                    <p class="text-slate-500 text-xs mt-1">Hold Ctrl/Cmd to select multiple. Optional. Assigned users receive an email when added.</p>
                                </div>
                                @endif
                                @if(($sales ?? collect())->isNotEmpty())
                                @php $defaultAddSalesId = $sales->count() === 1 ? $sales->first()->id : ''; @endphp
                                <div>
                                    <label class="block text-sm font-medium text-slate-400 mb-1">Assign Sales (one per project)</label>
                                    <select name="sales_id" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                        <option value="">— None —</option>
                                        @foreach($sales as $s)
                                            <option value="{{ $s->id }}" {{ (string) old('sales_id', $defaultAddSalesId) === (string) $s->id ? 'selected' : '' }}>{{ $s->name }} ({{ $s->email }})</option>
                                        @endforeach
                                    </select>
                                    <p class="text-slate-500 text-xs mt-1">Optional. One sales person per project. They receive an email when assigned.</p>
                                </div>
                                @endif

                                {{-- Distribution Settings: hidden by default, toggled by gear icon --}}
                                <div x-show="distributionSettingsOpen" class="pt-4 mt-4 border-t-2 border-slate-600/70 rounded-xl bg-slate-800/40 p-4 -mx-1">
                                    <h3 class="text-base font-semibold text-slate-200 mb-1">Distribution Settings</h3>
                                    <p class="text-slate-500 text-xs mb-3">Configure how Base (Contract − Expenses) is split: Overhead, Sales, Developer, Profit.</p>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="hidden" name="developer_sales_mode" value="0">
                                            <input type="checkbox" name="developer_sales_mode" value="1" x-model="developerSalesMode" {{ old('developer_sales_mode', false) ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-900 text-sky-500 focus:ring-sky-500">
                                            <span class="text-sm font-medium text-slate-400">Developer–Sales (75/25)</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer" :class="{ 'opacity-50 pointer-events-none': developerSalesMode }">
                                            <input type="hidden" name="sales_commission_enabled" value="0">
                                            <input type="checkbox" name="sales_commission_enabled" value="1" x-model="salesCommissionEnabled" {{ old('sales_commission_enabled', true) ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-900 text-sky-500 focus:ring-sky-500">
                                            <span class="text-sm font-medium text-slate-400">Sales Commission Applicable</span>
                                        </label>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3" :class="{ 'opacity-50 pointer-events-none': developerSalesMode }">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-400 mb-1">Sales %</label>
                                            <input type="number" name="sales_percentage" min="0" max="100" step="0.01" x-model.number="salesPercentage" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-400 mb-1">Developer %</label>
                                            <input type="number" name="developer_percentage" min="0" max="100" step="0.01" x-model.number="developerPercentage" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                        </div>
                                    </div>
                                    <div class="mt-3 p-3 rounded-lg bg-slate-900/80 border border-slate-700/50 text-sm">
                                        <p class="text-slate-400 mb-1.5">Preview (Base = Contract amount)</p>
                                        <template x-if="developerSalesMode">
                                            <div class="space-y-1 text-xs">
                                                <p class="flex justify-between"><span class="text-slate-500">Base</span><span class="text-white" x-text="'৳ ' + formatNum(base)"></span></p>
                                                <p class="flex justify-between"><span class="text-slate-500">Developer (75%)</span><span class="text-white" x-text="'৳ ' + formatNum(developerAmount)"></span></p>
                                                <p class="flex justify-between"><span class="text-slate-500">Sales (25%)</span><span class="text-white" x-text="'৳ ' + formatNum(salesAmount)"></span></p>
                                            </div>
                                        </template>
                                        <template x-if="!developerSalesMode">
                                            <div class="space-y-1 text-xs">
                                                <p class="flex justify-between"><span class="text-slate-500">Base</span><span class="text-white" x-text="'৳ ' + formatNum(base)"></span></p>
                                                <p class="flex justify-between"><span class="text-slate-500">Overhead (20%)</span><span class="text-white" x-text="'৳ ' + formatNum(overheadAmount)"></span></p>
                                                <p class="flex justify-between"><span class="text-slate-500">Sales</span><span class="text-white" x-text="'৳ ' + formatNum(salesAmount)"></span></p>
                                                <p class="flex justify-between"><span class="text-slate-500">Developer</span><span class="text-white" x-text="'৳ ' + formatNum(developerAmount)"></span></p>
                                                <p class="flex justify-between"><span class="text-slate-500">Profit</span><span class="text-emerald-400" x-text="'৳ ' + formatNum(profitAmount)"></span></p>
                                            </div>
                                        </template>
                                    </div>
                                    @error('sales_percentage')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                                    @error('developer_percentage')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                                    @error('distribution')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                                    <input type="hidden" name="exclude_from_overhead_profit" :value="developerSalesMode ? '1' : '0'">
                                </div>

                                <div class="pt-2 border-t border-slate-700/50">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="send_email" value="1" {{ old('send_email', true) ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-900 text-sky-500 focus:ring-sky-500">
                                        <span class="text-sm font-medium text-slate-400">Send Email Notification?</span>
                                    </label>
                                    <p class="text-slate-500 text-xs mt-1">Notify client about the new project (if template is enabled).</p>
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end gap-3 max-md:flex-col max-md:[&_button]:w-full">
                                <button type="button" @click="open = false" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700">Cancel</button>
                                <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
