@php
    $today = now()->format('Y-m-d');
@endphp
<x-app-layout>
    <x-slot name="title">Loss / Profit Tracking</x-slot>

    <script type="application/json" id="revenue-initial-data">{!! json_encode(['projectsData' => $projectsData, 'today' => $today]) !!}</script>
    <script>
        function registerRevenuePage() {
            Alpine.data('revenuePage', function () {
                const el = document.getElementById('revenue-initial-data');
                const initial = el ? JSON.parse(el.textContent) : { projectsData: [], today: '' };
                return {
                    projectsData: initial.projectsData || [],
                    searchText: '',
                    dateFilter: 'all',
                    customFrom: '',
                    customTo: initial.today || '',
                    get filteredProjects() {
                        const self = this;
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
                        const now = new Date();
                        const today = now.toISOString().slice(0, 10);
                        const firstDayMonth = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-01';
                        const firstDayYear = now.getFullYear() + '-01-01';
                        if (this.dateFilter === 'today') {
                            list = list.filter(function (p) { return (p.contract_date || p.created_at) === today; });
                        } else if (this.dateFilter === 'month') {
                            list = list.filter(function (p) {
                                const d = p.contract_date || p.created_at;
                                return d && d >= firstDayMonth && d <= today;
                            });
                        } else if (this.dateFilter === 'year') {
                            list = list.filter(function (p) {
                                const d = p.contract_date || p.created_at;
                                return d && d >= firstDayYear && d <= today;
                            });
                        } else if (this.dateFilter === 'custom' && this.customFrom && this.customTo) {
                            list = list.filter(function (p) {
                                const d = p.contract_date || p.created_at;
                                return d && d >= self.customFrom && d <= self.customTo;
                            });
                        }
                        return list;
                    },
                    get filteredIds() {
                        return this.filteredProjects.map(function (p) { return p.id; });
                    },
                    get totals() {
                        const list = this.filteredProjects;
                        return {
                            contract: list.reduce(function (s, p) { return s + p.contract_amount; }, 0),
                            expenses: list.reduce(function (s, p) { return s + p.expense_total; }, 0),
                            net_base: list.reduce(function (s, p) { return s + p.net_base; }, 0),
                            overhead: list.reduce(function (s, p) { return s + p.realized_overhead; }, 0),
                            overhead_total: list.reduce(function (s, p) { return s + p.overhead; }, 0),
                            sales: list.reduce(function (s, p) { return s + p.realized_sales; }, 0),
                            sales_total: list.reduce(function (s, p) { return s + p.sales; }, 0),
                            developer: list.reduce(function (s, p) { return s + p.realized_developer; }, 0),
                            developer_total: list.reduce(function (s, p) { return s + p.developer; }, 0),
                            profit: list.reduce(function (s, p) { return s + p.realized_profit; }, 0),
                            profit_total: list.reduce(function (s, p) { return s + p.profit; }, 0),
                            due: list.reduce(function (s, p) { return s + p.due; }, 0),
                            count: list.length
                        };
                    },
                    formatNum: function (n) { return new Intl.NumberFormat('en-BD').format(Math.round(n)); }
                };
            });
        }
        if (window.Alpine) {
            registerRevenuePage();
        } else {
            document.addEventListener('alpine:init', registerRevenuePage);
        }

        window.updatePayoutSilently = function (form) {
            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            }).then(function (r) {
                if (!r.ok) throw new Error('Update failed');
            }).catch(function () {
                form.reportValidity();
            });
        };
    </script>

    <div class="space-y-6" x-data="revenuePage()">
        {{-- After internal expenses: current overhead & profit available (from Finance → Internal Expenses) --}}
        <div class="rounded-xl bg-slate-800/80 border border-slate-700/50 p-4 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-slate-400 text-sm font-medium">After internal expenses</p>
                <p class="text-slate-500 text-xs mt-0.5">Amount left for overhead and profit from <a href="{{ route('internal-expenses.index') }}" class="text-sky-400 hover:text-sky-300">Finance → Internal Expenses</a></p>
            </div>
            <div class="flex flex-wrap items-center gap-6">
                <div class="text-center">
                    <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Current Overhead</p>
                    <p class="text-lg font-bold text-white mt-0.5">৳ {{ number_format($overheadBalance, 0) }}</p>
                </div>
                <div class="text-center">
                    <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Current Profit</p>
                    <p class="text-lg font-bold text-emerald-400 mt-0.5">৳ {{ number_format($profitBalance, 0) }}</p>
                </div>
            </div>
        </div>

        {{-- KPI Cards: 4 columns × 3 rows --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 max-md:grid-cols-1 max-md:gap-2">
            {{-- Row 1 --}}
            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-xl p-4 max-md:p-3">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Projects</p>
                <p class="text-lg font-bold text-white mt-0.5" x-text="totals.count"></p>
            </div>
            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-xl p-4">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Total Contract</p>
                <p class="text-lg font-bold text-white mt-0.5" x-text="'৳ ' + formatNum(totals.contract)"></p>
            </div>
            <div class="bg-slate-800/80 backdrop-blur border border-sky-500/30 rounded-xl p-4">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Net Base</p>
                <p class="text-lg font-bold text-white mt-0.5" x-text="'৳ ' + formatNum(totals.net_base)"></p>
            </div>
            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-xl p-4">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Total Expenses</p>
                <p class="text-lg font-bold text-white mt-0.5" x-text="'৳ ' + formatNum(totals.expenses)"></p>
            </div>
            {{-- Row 2 --}}
            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-xl p-4">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Due Sales</p>
                <p class="text-lg font-bold text-amber-400 mt-0.5" x-text="'৳ ' + formatNum(totals.sales_total - totals.sales)"></p>
            </div>
            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-xl p-4">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Due Developer</p>
                <p class="text-lg font-bold text-amber-400 mt-0.5" x-text="'৳ ' + formatNum(totals.developer_total - totals.developer)"></p>
            </div>
            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-xl p-4">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Due Overhead</p>
                <p class="text-lg font-bold text-amber-400 mt-0.5" x-text="'৳ ' + formatNum(totals.overhead_total - totals.overhead)"></p>
            </div>
            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-xl p-4">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Total Due</p>
                <p class="text-lg font-bold text-amber-400 mt-0.5" x-text="'৳ ' + formatNum(totals.due)"></p>
            </div>
            {{-- Row 3 --}}
            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-xl p-4">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Paid Sales</p>
                <p class="text-lg font-bold text-emerald-400 mt-0.5" x-text="'৳ ' + formatNum(totals.sales)"></p>
            </div>
            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-xl p-4">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Paid Developer</p>
                <p class="text-lg font-bold text-emerald-400 mt-0.5" x-text="'৳ ' + formatNum(totals.developer)"></p>
            </div>
            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-xl p-4">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Paid Overhead</p>
                <p class="text-lg font-bold text-emerald-400 mt-0.5" x-text="'৳ ' + formatNum(totals.overhead)"></p>
            </div>
            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-xl p-4">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Total Profit</p>
                <p class="text-lg font-bold text-emerald-400 mt-0.5" x-text="'৳ ' + formatNum(totals.profit)"></p>
            </div>
        </div>

        {{-- Search + Date filter (sticky when scrolling) --}}
        <div class="sticky top-0 z-10 -mx-1 px-1 pt-1 pb-3 -mt-1 bg-slate-900/95 backdrop-blur-sm border-b border-slate-700/50 rounded-b-xl shadow-lg">
            <div class="flex flex-wrap items-center gap-4 max-md:flex-col max-md:w-full max-md:gap-3">
                <div class="flex-1 min-w-[200px] max-md:w-full max-md:min-w-0">
                    <label for="revenue-search" class="sr-only">Search projects</label>
                    <input type="text" id="revenue-search" x-model="searchText" placeholder="Search by project name, code, client, ID…" class="w-full rounded-xl bg-slate-800 border border-slate-600 text-white px-4 py-2.5 text-sm placeholder-slate-500 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-slate-400 text-sm">Period:</span>
                    <button type="button" @click="dateFilter = 'all'" :class="dateFilter === 'all' ? 'bg-sky-500/30 text-sky-300 border-sky-500' : 'bg-slate-800 text-slate-400 border-slate-600 hover:text-white'" class="px-3 py-1.5 rounded-lg border text-sm font-medium transition">All</button>
                    <button type="button" @click="dateFilter = 'today'" :class="dateFilter === 'today' ? 'bg-sky-500/30 text-sky-300 border-sky-500' : 'bg-slate-800 text-slate-400 border-slate-600 hover:text-white'" class="px-3 py-1.5 rounded-lg border text-sm font-medium transition">Today</button>
                    <button type="button" @click="dateFilter = 'month'" :class="dateFilter === 'month' ? 'bg-sky-500/30 text-sky-300 border-sky-500' : 'bg-slate-800 text-slate-400 border-slate-600 hover:text-white'" class="px-3 py-1.5 rounded-lg border text-sm font-medium transition">This Month</button>
                    <button type="button" @click="dateFilter = 'year'" :class="dateFilter === 'year' ? 'bg-sky-500/30 text-sky-300 border-sky-500' : 'bg-slate-800 text-slate-400 border-slate-600 hover:text-white'" class="px-3 py-1.5 rounded-lg border text-sm font-medium transition">This Year</button>
                    <button type="button" @click="dateFilter = 'custom'" :class="dateFilter === 'custom' ? 'bg-sky-500/30 text-sky-300 border-sky-500' : 'bg-slate-800 text-slate-400 border-slate-600 hover:text-white'" class="px-3 py-1.5 rounded-lg border text-sm font-medium transition">Custom</button>
                    <template x-if="dateFilter === 'custom'">
                        <div class="flex items-center gap-2">
                            <input type="date" x-model="customFrom" class="rounded-lg bg-slate-800 border border-slate-600 text-white text-sm px-2.5 py-1.5 focus:ring-1 focus:ring-sky-500">
                            <span class="text-slate-500">to</span>
                            <input type="date" x-model="customTo" class="rounded-lg bg-slate-800 border border-slate-600 text-white text-sm px-2.5 py-1.5 focus:ring-1 focus:ring-sky-500">
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <h1 class="text-2xl font-semibold text-white">Loss / Profit Tracking</h1>

        <div class="bg-slate-800/60 backdrop-blur border border-slate-700/50 rounded-2xl overflow-hidden max-md:overflow-x-auto">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] max-md:min-w-[800px]">
                    <thead class="bg-slate-800/80 border-b border-slate-700/50">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Project</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Client</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Contract</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Expenses</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Net base</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Overhead</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Sales</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Developer</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Profit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($projects as $project)
                            <tr class="hover:bg-slate-800/40 transition" data-project-id="{{ $project->id }}" x-show="filteredIds.includes({{ $project->id }})">
                                <td class="px-4 py-3">
                                    <a href="{{ route('projects.show', $project) }}" class="font-medium text-sky-400 hover:text-sky-300">{{ $project->project_name }}</a>
                                    @if($project->project_code)<span class="text-slate-500 text-xs ml-1">{{ $project->project_code }}</span>@endif
                                </td>
                                <td class="px-4 py-3 text-slate-400">{{ $project->client->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-right text-white font-medium">৳ {{ number_format($project->contract_amount, 0) }}</td>
                                <td class="px-4 py-3 text-right text-slate-300">৳ {{ number_format($project->expense_total, 0) }}</td>
                                <td class="px-4 py-3 text-right text-white font-medium">৳ {{ number_format($project->net_base, 0) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-slate-300">৳ {{ number_format($project->realized_overhead, 0) }}</span>
                                    <span class="text-slate-500 text-xs">/ {{ number_format($project->overhead, 0) }}</span>
                                    <form action="{{ route('projects.payouts.update', $project) }}" method="POST" class="inline-block ml-1" onsubmit="event.preventDefault(); return false;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="type" value="overhead">
                                        <select name="status" class="rounded-lg bg-slate-900 border border-slate-600 text-slate-200 text-xs px-2 py-1 focus:ring-1 focus:ring-sky-500 focus:border-sky-500" onchange="window.updatePayoutSilently(this.form)">
                                            @foreach(['not_paid' => 'Not Paid', 'upcoming' => 'Upcoming', 'due' => 'Due', 'partial' => 'Partial', 'paid' => 'Paid'] as $val => $label)
                                                <option value="{{ $val }}" {{ ($project->getPayoutFor('overhead')?->status ?? 'not_paid') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-slate-300">৳ {{ number_format($project->realized_sales, 0) }}</span>
                                    <span class="text-slate-500 text-xs">/ {{ number_format($project->sales, 0) }}</span>
                                    <form action="{{ route('projects.payouts.update', $project) }}" method="POST" class="inline-block ml-1" onsubmit="event.preventDefault(); return false;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="type" value="sales">
                                        <select name="status" class="rounded-lg bg-slate-900 border border-slate-600 text-slate-200 text-xs px-2 py-1 focus:ring-1 focus:ring-sky-500 focus:border-sky-500" onchange="window.updatePayoutSilently(this.form)">
                                            @foreach(['not_paid' => 'Not Paid', 'upcoming' => 'Upcoming', 'due' => 'Due', 'partial' => 'Partial', 'paid' => 'Paid'] as $val => $label)
                                                <option value="{{ $val }}" {{ ($project->getPayoutFor('sales')?->status ?? 'not_paid') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-slate-300">৳ {{ number_format($project->realized_developer, 0) }}</span>
                                    <span class="text-slate-500 text-xs">/ {{ number_format($project->developer, 0) }}</span>
                                    <form action="{{ route('projects.payouts.update', $project) }}" method="POST" class="inline-block ml-1" onsubmit="event.preventDefault(); return false;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="type" value="developer">
                                        <select name="status" class="rounded-lg bg-slate-900 border border-slate-600 text-slate-200 text-xs px-2 py-1 focus:ring-1 focus:ring-sky-500 focus:border-sky-500" onchange="window.updatePayoutSilently(this.form)">
                                            @foreach(['not_paid' => 'Not Paid', 'upcoming' => 'Upcoming', 'due' => 'Due', 'partial' => 'Partial', 'paid' => 'Paid'] as $val => $label)
                                                <option value="{{ $val }}" {{ ($project->getPayoutFor('developer')?->status ?? 'not_paid') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-emerald-400 font-medium">৳ {{ number_format($project->realized_profit, 0) }}</span>
                                    <span class="text-slate-500 text-xs">/ {{ number_format($project->profit, 0) }}</span>
                                    <form action="{{ route('projects.payouts.update', $project) }}" method="POST" class="inline-block ml-1" onsubmit="event.preventDefault(); return false;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="type" value="profit">
                                        <select name="status" class="rounded-lg bg-slate-900 border border-slate-600 text-slate-200 text-xs px-2 py-1 focus:ring-1 focus:ring-sky-500 focus:border-sky-500" onchange="window.updatePayoutSilently(this.form)">
                                            @foreach(['not_paid' => 'Not Paid', 'upcoming' => 'Upcoming', 'due' => 'Due', 'partial' => 'Partial', 'paid' => 'Paid'] as $val => $label)
                                                <option value="{{ $val }}" {{ ($project->getPayoutFor('profit')?->status ?? 'not_paid') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-12 text-center text-slate-500">No projects yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
