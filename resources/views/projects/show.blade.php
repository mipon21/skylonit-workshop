<x-app-layout>
    <x-slot name="title">{{ $project->project_name }}</x-slot>

    @php
        $tabCountPayments = $project->payments->count();
        $tabCountExpenses = $project->expenses->count();
        $tabCountSupport = $project->supportPackages->count();
        $tabCountDocuments = $project->documents->count();
        $tabCountContracts = $project->contracts->count();
        $tabCountTasks = $project->tasks->count();
        $tabCountBugs = $project->bugs->count();
        $tabCountNotes = $project->projectNotes->count();
        $tabCountLinks = $project->projectLinks->count();
    @endphp
    @php
        $isInternal = ($isDeveloper ?? false) || ($isSales ?? false);
        $defaultTab = ($isSales ?? false) ? 'payments' : ($isInternal ? 'documents' : 'payments');
        $firstFilesTab = ($isDeveloper ?? false) ? 'documents' : 'contracts';
    @endphp
    <div class="space-y-6" x-data="{
        activeTab: (() => { const h = window.location.hash.slice(1); return ['payments','expenses','support','client','documents','contracts','tasks','bugs','notes','links','activity'].includes(h) ? h : '{{ $defaultTab }}'; })(),
        firstTabByParent: { finance: 'payments', files: '{{ $firstFilesTab }}', works: 'tasks', activity: 'activity' },
        activeParent() {
            if (['payments','expenses','support','client'].includes(this.activeTab)) return 'finance';
            if (['contracts','documents','notes','links'].includes(this.activeTab)) return 'files';
            if (['tasks','bugs'].includes(this.activeTab)) return 'works';
            return 'activity';
        },
        setParentTab(parent) {
            this.activeTab = this.firstTabByParent[parent];
            window.location.hash = this.activeTab;
            if (this.activeTab === 'activity') this.markActivityViewed();
        },
        unviewedActivityCount: {{ (int) ($unviewedActivityCount ?? 0) }},
        markActivityViewed() {
            if (this.unviewedActivityCount > 0) {
                fetch('{{ route('projects.activity.viewed', $project) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' }).then(function() {});
                this.unviewedActivityCount = 0;
            }
        },
        setTab(tab) {
            this.activeTab = tab;
            window.location.hash = tab;
            if (tab === 'activity') this.markActivityViewed();
        },
        init() {
            if (this.activeTab === 'activity') this.markActivityViewed();
        },
        paymentModal: false,
        paymentEditModal: null,
        expenseModal: false,
        expenseEditModal: null,
        documentModal: false,
        contractModal: false,
        taskModal: false,
        bugModal: false,
        noteModal: false,
        linkModal: false,
        linkEditModal: null,
        noteEditModal: null,
        expandedNoteId: null,
        expandedTaskId: null,
        expandedBugId: null,
        taskEditModal: null,
        bugEditModal: null,
        payoutModal: false,
        payoutType: null,
        clearShareModal: false
    }" x-init="init()">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('projects.index') }}" class="theme-text-secondary theme-hover-primary text-sm">← Projects</a>
                <div class="flex flex-wrap items-center gap-2 mt-1">
                    <h1 class="text-2xl font-semibold theme-text-primary">{{ $project->project_name }}</h1>
                    @if($project->project_type)
                        <span class="px-2.5 py-1 rounded-lg text-xs font-medium theme-bg-tertiary theme-text-secondary theme-border border">{{ $project->project_type }}</span>
                    @endif
                    @if(!($isClient ?? false) && $project->is_developer_sales_mode)
                        <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-amber-500/20 text-amber-400 border border-amber-500/40" title="Developer 75% &amp; Sales 25% only; no Overhead/Profit">Dev &amp; Sales only</span>
                    @endif
                </div>
                <p class="theme-text-secondary text-sm mt-0.5">
                    @if($isInternal){{ $project->project_code ?: $project->formatted_id }}@else{{ $project->client->name }}@endif
                    @if($project->project_code && !$isInternal)· {{ $project->project_code }}@endif
                    @if($project->contract_date)· Contract: {{ $project->contract_date->format('M j, Y') }}@endif
                    @if($project->delivery_date)· Delivery: {{ $project->delivery_date->format('M j, Y') }}@endif
                </p>
            </div>
            <div class="flex gap-2 items-center max-md:flex-wrap max-md:gap-2">
                @if(!($isClient ?? false) && !($isDeveloper ?? false) && !($isSales ?? false))
                <a href="{{ route('projects.edit', $project) }}" class="px-4 py-2.5 rounded-xl theme-btn-primary text-sm font-medium transition">Edit</a>
                <div class="relative" x-data="{ statusOpen: false, statusValue: '{{ $project->status }}' }" @click.outside="statusOpen = false">
                    <form x-ref="statusForm" action="{{ route('projects.status.update', $project) }}" method="post" class="inline">
                        @csrf
                        @method('patch')
                        <input type="hidden" name="status" :value="statusValue">
                        <button type="button" @click="statusOpen = ! statusOpen" class="px-4 py-2.5 rounded-xl theme-btn-primary text-sm font-medium transition flex items-center gap-1.5">
                            <span x-text="statusValue || '{{ $project->status }}'">{{ $project->status }}</span>
                            <svg class="w-4 h-4 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </form>
                    <div x-show="statusOpen" x-cloak x-transition class="absolute right-0 top-full mt-1 min-w-[10rem] rounded-xl theme-bg-tertiary border theme-border shadow-xl py-1 z-50">
                        <button type="button" @click="statusValue = 'Pending'; statusOpen = false; $nextTick(() => $refs.statusForm.submit())" class="w-full text-left px-4 py-2 text-sm theme-text-secondary theme-sidebar-link-hover theme-hover-primary transition">Pending</button>
                        <button type="button" @click="statusValue = 'Running'; statusOpen = false; $nextTick(() => $refs.statusForm.submit())" class="w-full text-left px-4 py-2 text-sm theme-text-secondary theme-sidebar-link-hover theme-hover-primary transition">Running</button>
                        <button type="button" @click="statusValue = 'Complete'; statusOpen = false; $nextTick(() => $refs.statusForm.submit())" class="w-full text-left px-4 py-2 text-sm theme-text-secondary theme-sidebar-link-hover theme-hover-primary transition">Complete</button>
                        <button type="button" @click="statusValue = 'On Hold'; statusOpen = false; $nextTick(() => $refs.statusForm.submit())" class="w-full text-left px-4 py-2 text-sm theme-text-secondary theme-sidebar-link-hover theme-hover-primary transition">On Hold</button>
                        <button type="button" @click="statusValue = 'On Support'; statusOpen = false; $nextTick(() => $refs.statusForm.submit())" class="w-full text-left px-4 py-2 text-sm theme-text-secondary theme-sidebar-link-hover theme-hover-primary transition">On Support</button>
                    </div>
                </div>
                @else
                <span class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary text-sm">{{ $project->status }}</span>
                @endif
            </div>
        </div>

        @php
            $tasksTotal = $project->tasks_count ?? $project->tasks->count();
            $tasksDone = $project->tasks_done_count ?? $project->tasks->where('status', 'done')->count();
            $projectProgressPercent = $tasksTotal > 0 ? round(($tasksDone / $tasksTotal) * 100) : 0;
            $paymentsPercent = $project->contract_amount > 0 ? min(100, round(($project->total_paid / $project->contract_amount) * 100)) : 0;
        @endphp
        <style>
            @keyframes project-progress-fill { from { width: 0%; } to { width: {{ $projectProgressPercent }}%; } }
            @keyframes payments-progress-fill { from { width: 0%; } to { width: {{ $paymentsPercent }}%; } }
            .progress-fill-project { width: 0%; height: 13px; animation: project-progress-fill 0.8s ease-out 0.1s forwards; background: linear-gradient(to right, #EF8121, #EF8121); }
            .progress-fill-payments { width: 0%; height: 13px; animation: payments-progress-fill 0.8s ease-out 0.1s forwards; background: linear-gradient(to right, #10b981, #34d399); }
        </style>
        {{-- Project Progress (before payment data) – animates on load --}}
        <div class="rounded-2xl theme-card-bg-only theme-border border p-4 shadow-inner">
            <div class="flex items-center justify-between mb-2.5">
                <span class="text-sm font-semibold theme-text-secondary uppercase tracking-wide">Project progress</span>
                <span class="text-sm font-medium text-orange-400 tabular-nums">{{ $projectProgressPercent }}%</span>
            </div>
            <div class="relative w-full overflow-hidden rounded-full border theme-border theme-bg-tertiary" style="height: 13px;">
                <div class="progress-fill-project absolute top-0 left-0 bottom-0 rounded-full"></div>
            </div>
            <p class="theme-text-muted text-xs mt-1.5">{{ $tasksDone }} / {{ $tasksTotal }} tasks done</p>
        </div>

        {{-- Revenue pipeline: admin sees full; client sees Contract, Expenses, Total Paid, Due only; developer/sales see nothing --}}
        @if(!($isClient ?? false) && !($isDeveloper ?? false) && !($isSales ?? false))
        @if($project->is_developer_sales_mode)
            <div class="payment-amount mb-4 px-4 py-3 rounded-xl bg-amber-500/15 border border-amber-500/30 text-amber-400/90 text-sm flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                This project is <strong>Developer &amp; Sales only</strong>: contract amount minus expenses, then 75% Developer and 25% Sales. Overhead and Profit are ৳0 and not included in Loss/Profit totals.
            </div>
        @endif
        @if($project->is_net_base_negative)
            <div class="mb-4 px-4 py-3 rounded-xl bg-amber-500/20 border border-amber-500/40 text-amber-400 text-sm flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Net base is negative (expenses exceed contract). Profit shown as ৳0.
            </div>
        @endif
        @endif
        @if(!($isDeveloper ?? false) && !($isSales ?? false))
        <div class="max-md:overflow-x-auto max-md:flex max-md:gap-4 max-md:pb-2 max-md:snap-x max-md:snap-mandatory">
        <div class="grid grid-cols-2 sm:grid-cols-3 {{ ($isClient ?? false) ? 'lg:grid-cols-4' : 'lg:grid-cols-6' }} gap-4 items-stretch max-md:flex max-md:flex-nowrap max-md:min-w-0 max-md:gap-4 max-md:items-start">
            <div class="theme-card-bg theme-border border rounded-2xl p-4 flex flex-col justify-between max-md:shrink-0 max-md:min-w-[200px] max-md:snap-start">
                <div>
                    <p class="theme-text-secondary text-xs font-medium uppercase tracking-wide">Contract {{ ($isClient ?? false) ? 'Amount' : '' }}</p>
                    <p class="text-lg font-bold theme-text-primary mt-0.5"><span class="payment-amount">৳ {{ number_format($project->contract_amount, 0) }}</span></p>
                </div>
                <div class="mt-2 pt-2 border-t theme-border min-h-[2.5rem] md:block hidden" aria-hidden="true"></div>
            </div>
            <div class="theme-card-bg theme-border border rounded-2xl p-4 flex flex-col justify-between max-md:shrink-0 max-md:min-w-[200px] max-md:snap-start">
                <div>
                    <p class="theme-text-secondary text-xs font-medium uppercase tracking-wide">Total Expenses</p>
                    <p class="text-lg font-bold theme-text-primary mt-0.5"><span class="payment-amount">৳ {{ number_format(($isClient ?? false) ? $project->public_expense_total : $project->expense_total, 0) }}</span></p>
                </div>
                <div class="mt-2 pt-2 border-t theme-border min-h-[2.5rem] md:block hidden" aria-hidden="true"></div>
            </div>
            @if(!($isClient ?? false))
            <div class="theme-card-bg theme-border border rounded-2xl p-4 flex flex-col justify-between max-md:shrink-0 max-md:min-w-[200px] max-md:snap-start">
                <div>
                    <p class="theme-text-secondary text-xs font-medium uppercase tracking-wide">Net base</p>
                    <p class="text-lg font-bold {{ $project->is_net_base_negative ? 'text-amber-400' : 'theme-text-primary' }} mt-0.5"><span class="payment-amount">৳ {{ number_format($project->net_base, 0) }}</span></p>
                </div>
                <div class="mt-2 pt-2 border-t theme-border min-h-[2.5rem] md:block hidden" aria-hidden="true"></div>
            </div>
            @php
                $payoutStatus = fn($type) => $project->getPayoutFor($type)?->status ?? 'not_paid';
            @endphp
            @php
                $payoutBadgeClass = fn($status) => match($status) {
                    'paid' => 'bg-emerald-500/25 text-emerald-400 border-emerald-500/40',
                    'due', 'partial' => 'bg-amber-500/25 text-amber-400 border-amber-500/40',
                    'upcoming' => 'bg-orange-500/25 text-orange-400 border-orange-500/40',
                    default => 'theme-bg-tertiary theme-text-secondary theme-border',
                };
            @endphp
            <div role="button" tabindex="0" title="Click to edit payout" @click="payoutType = 'overhead'; payoutModal = true" @keydown.enter="payoutType = 'overhead'; payoutModal = true" class="theme-card-bg theme-border border rounded-2xl p-4 cursor-pointer hover:theme-border transition group max-md:shrink-0 max-md:min-w-[200px] max-md:snap-start">
                <p class="theme-text-secondary text-xs font-medium uppercase tracking-wide">Overhead</p>
                <p class="text-lg font-bold theme-text-primary mt-0.5"><span class="payment-amount">৳ {{ number_format($project->realized_overhead, 0) }}</span></p>
                <p class="theme-text-muted text-xs mt-0.5">of <span class="payment-amount">৳ {{ number_format($project->overhead, 0) }}</span></p>
                <p class="mt-2 pt-2 border-t theme-border">
                    <span class="inline-block px-3 py-1.5 rounded-md text-base font-medium border whitespace-nowrap {{ $payoutBadgeClass($payoutStatus('overhead')) }}">{{ \App\Models\ProjectPayout::statusLabel($payoutStatus('overhead')) }}</span>
                </p>
            </div>
            <div role="button" tabindex="0" title="Click to edit payout" @click="payoutType = 'sales'; payoutModal = true" @keydown.enter="payoutType = 'sales'; payoutModal = true" class="theme-card-bg theme-border border rounded-2xl p-4 cursor-pointer hover:theme-border transition group max-md:shrink-0 max-md:min-w-[200px] max-md:snap-start">
                <p class="theme-text-secondary text-xs font-medium uppercase tracking-wide">Sales</p>
                <p class="text-lg font-bold theme-text-primary mt-0.5"><span class="payment-amount">৳ {{ number_format($project->realized_sales, 0) }}</span></p>
                <p class="theme-text-muted text-xs mt-0.5">of <span class="payment-amount">৳ {{ number_format($project->sales, 0) }}</span></p>
                <p class="mt-2 pt-2 border-t theme-border">
                    <span class="inline-block px-3 py-1.5 rounded-md text-base font-medium border whitespace-nowrap {{ $payoutBadgeClass($payoutStatus('sales')) }}">{{ \App\Models\ProjectPayout::statusLabel($payoutStatus('sales')) }}</span>
                </p>
            </div>
            <div role="button" tabindex="0" title="Click to edit payout" @click="payoutType = 'developer'; payoutModal = true" @keydown.enter="payoutType = 'developer'; payoutModal = true" class="theme-card-bg theme-border border rounded-2xl p-4 cursor-pointer hover:theme-border transition group max-md:shrink-0 max-md:min-w-[200px] max-md:snap-start">
                <p class="theme-text-secondary text-xs font-medium uppercase tracking-wide">Developer</p>
                <p class="text-lg font-bold theme-text-primary mt-0.5"><span class="payment-amount">৳ {{ number_format($project->realized_developer, 0) }}</span></p>
                <p class="theme-text-muted text-xs mt-0.5">of <span class="payment-amount">৳ {{ number_format($project->developer, 0) }}</span></p>
                <p class="mt-2 pt-2 border-t theme-border">
                    <span class="inline-block px-3 py-1.5 rounded-md text-base font-medium border whitespace-nowrap {{ $payoutBadgeClass($payoutStatus('developer')) }}">{{ \App\Models\ProjectPayout::statusLabel($payoutStatus('developer')) }}</span>
                </p>
            </div>
            @else
            <div class="theme-card-bg theme-border border rounded-2xl p-4 max-md:shrink-0 max-md:min-w-[200px] max-md:snap-start">
                <p class="theme-text-secondary text-xs font-medium uppercase tracking-wide">Total Paid</p>
                <p class="text-lg font-bold text-emerald-400 mt-0.5"><span class="payment-amount">৳ {{ number_format($project->total_paid, 0) }}</span></p>
            </div>
            <div class="theme-card-bg theme-border border rounded-2xl p-4 max-md:shrink-0 max-md:min-w-[200px] max-md:snap-start">
                <p class="theme-text-secondary text-xs font-medium uppercase tracking-wide">Due</p>
                <p class="text-lg font-bold {{ $project->due > 0 ? 'text-amber-400' : 'text-emerald-400' }} mt-0.5"><span class="payment-amount">৳ {{ number_format($project->due, 0) }}</span></p>
            </div>
            @endif
        </div>
        </div>
        @endif

        {{-- Developer: their payment for this project and status (read-only) --}}
        @if($isDeveloper ?? false)
        @php
            $devPayout = $project->getPayoutFor(\App\Models\ProjectPayout::TYPE_DEVELOPER);
            $devPayoutStatus = $devPayout?->status ?? 'not_paid';
            $devStatusBadge = match($devPayoutStatus) {
                'paid' => 'bg-emerald-500/25 text-emerald-400 border-emerald-500/40',
                'due', 'partial' => 'bg-amber-500/25 text-amber-400 border-amber-500/40',
                'upcoming' => 'bg-orange-500/25 text-orange-400 border-orange-500/40',
                default => 'theme-bg-tertiary theme-text-secondary theme-border',
            };
        @endphp
        <div class="rounded-2xl theme-bg-tertiary/80 border border-orange-500/30 p-4">
            <h3 class="text-sm font-semibold theme-text-secondary uppercase tracking-wide mb-3">Your payment (this project)</h3>
            <div class="flex flex-wrap items-center gap-6">
                <div>
                    <p class="theme-text-secondary text-xs">Amount (realized)</p>
                    <p class="text-xl font-bold theme-text-primary">৳ {{ number_format($project->realized_developer, 0) }}</p>
                    <p class="theme-text-muted text-xs mt-0.5">of ৳ {{ number_format($project->developer, 0) }} total pool</p>
                </div>
                <div>
                    <p class="theme-text-secondary text-xs mb-1">Payment status</p>
                    <span class="inline-block px-3 py-1.5 rounded-md text-sm font-medium border {{ $devStatusBadge }}">{{ $devPayoutStatus === 'paid' ? 'Paid' : ($devPayoutStatus === 'partial' ? 'Partially paid' : \App\Models\ProjectPayout::statusLabel($devPayoutStatus)) }}</span>
                    @if($devPayoutStatus === 'partial' && $devPayout && $devPayout->amount_paid !== null)
                        <p class="theme-text-muted text-xs mt-1">Paid so far: ৳ {{ number_format($devPayout->amount_paid, 0) }}</p>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Sales: their payment for this project and status (read-only) --}}
        @if($isSales ?? false)
        @php
            $salesPayout = $project->getPayoutFor(\App\Models\ProjectPayout::TYPE_SALES);
            $salesPayoutStatus = $salesPayout?->status ?? 'not_paid';
            $salesStatusBadge = match($salesPayoutStatus) {
                'paid' => 'bg-emerald-500/25 text-emerald-400 border-emerald-500/40',
                'due', 'partial' => 'bg-amber-500/25 text-amber-400 border-amber-500/40',
                'upcoming' => 'bg-orange-500/25 text-orange-400 border-orange-500/40',
                default => 'theme-bg-tertiary theme-text-secondary theme-border',
            };
        @endphp
        <div class="rounded-2xl theme-bg-tertiary/80 border border-orange-500/30 p-4">
            <h3 class="text-sm font-semibold theme-text-secondary uppercase tracking-wide mb-3">Your payment (this project)</h3>
            <div class="flex flex-wrap items-center gap-6">
                <div>
                    <p class="theme-text-secondary text-xs">Amount (realized)</p>
                    <p class="text-xl font-bold theme-text-primary">৳ {{ number_format($project->realized_sales, 0) }}</p>
                    <p class="theme-text-muted text-xs mt-0.5">of ৳ {{ number_format($project->sales, 0) }} total pool</p>
                </div>
                <div>
                    <p class="theme-text-secondary text-xs mb-1">Payment status</p>
                    <span class="inline-block px-3 py-1.5 rounded-md text-sm font-medium border {{ $salesStatusBadge }}">{{ $salesPayoutStatus === 'paid' ? 'Paid' : ($salesPayoutStatus === 'partial' ? 'Partially paid' : \App\Models\ProjectPayout::statusLabel($salesPayoutStatus)) }}</span>
                    @if($salesPayoutStatus === 'partial' && $salesPayout && $salesPayout->amount_paid !== null)
                        <p class="theme-text-muted text-xs mt-1">Paid so far: ৳ {{ number_format($salesPayout->amount_paid, 0) }}</p>
                    @endif
                </div>
            </div>
        </div>
        @endif

        @php
            $taskTodo = $project->tasks->where('status', 'todo')->count();
            $taskDoing = $project->tasks->where('status', 'doing')->count();
            $taskDone = $project->tasks->where('status', 'done')->count();
            $taskTotal = $project->tasks->count();
            $openBugs = $project->bugs->whereIn('status', ['open', 'in_progress'])->count();
        @endphp
        {{-- Side-by-side: Work Overview (left) | Profit card (right, admin only). --}}
        <div class="flex flex-row gap-4 mt-2 items-stretch max-md:flex-col max-md:gap-3 max-md:w-full max-md:items-start">
            <div class="flex-1 min-w-0 min-h-[70px] rounded-2xl theme-card-bg theme-border border p-2.5 overflow-visible md:min-w-[280px] flex flex-col max-md:h-auto max-md:min-h-0 max-md:w-full max-md:min-w-full">
                <p class="theme-text-secondary text-sm font-semibold uppercase tracking-wide mb-1.5 shrink-0 text-center">Work overview</p>
                <div class="flex-1 min-h-0 flex flex-col gap-1.5">
                    <div class="flex-1 min-h-0 min-w-0 grid grid-cols-2 sm:grid-cols-4 gap-1.5 justify-items-stretch content-stretch max-md:grid-cols-4 max-md:w-full">
                        <div class="rounded-xl theme-card-bg theme-border border px-2 py-1.5 flex items-center justify-between gap-1.5 shadow-inner min-h-0">
                            <span class="theme-text-secondary text-[10px] font-medium">To do</span>
                            <span class="theme-text-primary text-xs font-bold tabular-nums">{{ $taskTodo }}</span>
                        </div>
                        <div class="rounded-xl theme-card-bg theme-border border px-2 py-1.5 flex items-center justify-between gap-1.5 shadow-inner min-h-0">
                            <span class="theme-text-secondary text-[10px] font-medium">Doing</span>
                            <span class="text-amber-400 text-xs font-bold tabular-nums">{{ $taskDoing }}</span>
                        </div>
                        <div class="rounded-xl theme-card-bg theme-border border px-2 py-1.5 flex items-center justify-between gap-1.5 shadow-inner min-h-0">
                            <span class="theme-text-secondary text-[10px] font-medium">Done</span>
                            <span class="text-emerald-400 text-xs font-bold tabular-nums">{{ $taskDone }}</span>
                        </div>
                        <div class="rounded-xl theme-card-bg theme-border border px-2 py-1.5 flex items-center justify-between gap-1.5 shadow-inner min-h-0">
                            <span class="theme-text-secondary text-[10px] font-medium">Total</span>
                            <span class="text-orange-400 text-xs font-bold tabular-nums">{{ $taskTotal }}</span>
                        </div>
                    </div>
                    <div class="flex-1 min-h-0 min-w-0 grid grid-cols-2 sm:grid-cols-4 gap-1.5 justify-items-stretch content-stretch max-md:grid-cols-4 max-md:w-full">
                        <div class="rounded-xl theme-card-bg theme-border border px-2 py-1.5 flex items-center justify-between gap-1.5 shadow-inner min-h-0">
                            <span class="theme-text-secondary text-[10px] font-medium">docs</span>
                            <span class="theme-text-primary text-xs font-bold tabular-nums">{{ $project->documents->count() }}</span>
                        </div>
                        <div class="rounded-xl theme-card-bg theme-border border px-2 py-1.5 flex items-center justify-between gap-1.5 shadow-inner min-h-0">
                            <span class="theme-text-secondary text-[10px] font-medium">notes</span>
                            <span class="theme-text-primary text-xs font-bold tabular-nums">{{ $project->projectNotes->count() }}</span>
                        </div>
                        <div class="rounded-xl theme-card-bg theme-border border px-2 py-1.5 flex items-center justify-between gap-1.5 shadow-inner min-h-0">
                            <span class="theme-text-secondary text-[10px] font-medium">bugs</span>
                            <span class="theme-text-primary text-xs font-bold tabular-nums">{{ $openBugs }}</span>
                        </div>
                        <div class="rounded-xl theme-card-bg theme-border border px-2 py-1.5 flex items-center justify-between gap-1.5 shadow-inner min-h-0">
                            <span class="theme-text-secondary text-[10px] font-medium">links</span>
                            <span class="theme-text-primary text-xs font-bold tabular-nums">{{ $project->projectLinks->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @if(!($isClient ?? false) && !($isDeveloper ?? false) && !($isSales ?? false))
            <div class="flex shrink-0 md:self-stretch">
                <div role="button" tabindex="0" title="Click to edit payout" @click="payoutType = 'profit'; payoutModal = true" @keydown.enter="payoutType = 'profit'; payoutModal = true" class="h-full theme-bg-tertiary/80 backdrop-blur border border-emerald-500/30 rounded-2xl p-4 shadow-[0_0_20px_-5px_rgba(16,185,129,0.2)] min-w-[180px] text-right cursor-pointer hover:border-emerald-500/50 transition group flex flex-col justify-between">
                    <p class="text-emerald-400/90 text-lg font-semibold uppercase tracking-wide">Profit</p>
                    <p class="text-4xl font-bold text-emerald-400 mt-0.5"><span class="payment-amount">৳ {{ number_format($project->realized_profit, 0) }}</span></p>
                    <p class="theme-text-muted text-lg mt-0.5">of <span class="payment-amount">৳ {{ number_format($project->profit, 0) }}</span></p>
                    <p class="mt-2 pt-2 border-t theme-border">
                        <span class="inline-block px-3 py-1.5 rounded-md text-base font-medium border whitespace-nowrap {{ $payoutBadgeClass($payoutStatus('profit')) }}">{{ \App\Models\ProjectPayout::statusLabel($payoutStatus('profit')) }}</span>
                    </p>
                </div>
            </div>
            @endif
        </div>
        @if(!($isClient ?? false) && !($isDeveloper ?? false) && !($isSales ?? false))
        <p class="theme-text-muted text-xs mt-1">Amounts above based on cash received (paid minus expenses).</p>
        @endif

        @if(($isClient ?? false) || (!($isClient ?? false) && !($isDeveloper ?? false) && !($isSales ?? false)))
        {{-- Payments progress (below payment data) – for client and admin --}}
        <div class="rounded-2xl theme-card-bg-only theme-border border p-4 shadow-inner">
            <div class="flex items-center justify-between mb-2.5">
                <span class="text-sm font-semibold theme-text-secondary uppercase tracking-wide">Payments progress</span>
                <span class="payment-amount text-sm font-medium text-emerald-400 tabular-nums">{{ $paymentsPercent }}%</span>
            </div>
            <div class="relative w-full overflow-hidden rounded-full border theme-border theme-bg-tertiary" style="height: 13px;">
                <div class="progress-fill-payments absolute top-0 left-0 bottom-0 rounded-full"></div>
            </div>
            <p class="theme-text-muted text-xs mt-1.5"><span class="payment-amount">৳ {{ number_format($project->total_paid, 0) }}</span> / <span class="payment-amount">৳ {{ number_format($project->contract_amount, 0) }}</span> received</p>
        </div>
        @endif

        {{-- Tabs: hidden for Sales (they only see "Your payment" above) --}}
        @if(!($isSales ?? false))
        <style>
            /* Project tabs: 15px radius + padding - inline so it always applies */
            .project-tabs-wrap { border-radius: 15px !important; }
            .project-tabs-wrap .project-tabs-parent-row button,
            .project-tabs-wrap .project-tabs-sub-row button { border-radius: 15px !important; }
            .project-tabs-wrap .project-tabs-parent-row button {
                padding: 0.5rem 1.5rem !important;
            }
            @media (min-width: 640px) {
                .project-tabs-wrap .project-tabs-sub-row button {
                    padding: 0.375rem 1rem !important;
                }
            }
        </style>
        <div class="project-tabs-wrap theme-card-bg-only theme-border border overflow-hidden shadow-sm" style="border-radius: 15px;">
            {{-- Parent tab row: 2x2 grid on mobile, flex row on sm+ --}}
            <div class="project-tabs-parent-row grid grid-cols-2 auto-rows-fr gap-2 min-h-[7.5rem] px-3 py-3 border-b theme-border theme-bg-tertiary sm:min-h-[3.5rem] sm:flex sm:flex-wrap sm:justify-center sm:items-center">
                @if(!($isDeveloper ?? false))
                <button type="button" @click="setParentTab('finance')" :class="activeParent() === 'finance' ? 'project-tab-active shadow-md' : 'theme-bg-secondary theme-border theme-text-primary border hover:bg-orange-500/15 hover:border-orange-500/40'" :style="activeParent() === 'finance' ? { backgroundColor: '#EF8121', color: '#ffffff', borderColor: '#EF8121' } : {}" class="w-full h-full min-h-[2.75rem] flex items-center justify-center gap-1.5 rounded-[15px] text-sm font-semibold border-2 transition-all duration-200 px-2 py-2 sm:w-auto sm:h-auto sm:min-h-[2.5rem] sm:px-6 sm:py-2">
                    <span>Finance</span>
                </button>
                @endif
                <button type="button" @click="setParentTab('files')" :class="activeParent() === 'files' ? 'project-tab-active shadow-md' : 'theme-bg-secondary theme-border theme-text-primary border hover:bg-orange-500/15 hover:border-orange-500/40'" :style="activeParent() === 'files' ? { backgroundColor: '#EF8121', color: '#ffffff', borderColor: '#EF8121' } : {}" class="w-full h-full min-h-[2.75rem] flex items-center justify-center gap-1.5 rounded-[15px] text-sm font-semibold border-2 transition-all duration-200 px-2 py-2 sm:w-auto sm:h-auto sm:min-h-[2.5rem] sm:px-6 sm:py-2">
                    <span>Files &amp; Links</span>
                </button>
                <button type="button" @click="setParentTab('works')" :class="activeParent() === 'works' ? 'project-tab-active shadow-md' : 'theme-bg-secondary theme-border theme-text-primary border hover:bg-orange-500/15 hover:border-orange-500/40'" :style="activeParent() === 'works' ? { backgroundColor: '#EF8121', color: '#ffffff', borderColor: '#EF8121' } : {}" class="w-full h-full min-h-[2.75rem] flex items-center justify-center gap-1.5 rounded-[15px] text-sm font-semibold border-2 transition-all duration-200 px-2 py-2 sm:w-auto sm:h-auto sm:min-h-[2.5rem] sm:px-6 sm:py-2">
                    <span>Works</span>
                </button>
                <button type="button" @click="setParentTab('activity')" :class="activeParent() === 'activity' ? 'project-tab-active shadow-md' : 'theme-bg-secondary theme-border theme-text-primary border hover:bg-orange-500/15 hover:border-orange-500/40'" :style="activeParent() === 'activity' ? { backgroundColor: '#EF8121', color: '#ffffff', borderColor: '#EF8121' } : {}" class="w-full h-full min-h-[2.75rem] flex items-center justify-center gap-1.5 rounded-[15px] text-sm font-semibold border-2 transition-all duration-200 px-2 py-2 sm:w-auto sm:h-auto sm:min-h-[2.5rem] sm:px-6 sm:py-2">
                    <span>Activity</span>
                    <span x-show="unviewedActivityCount > 0" x-transition class="min-w-[1.25rem] h-5 px-1.5 rounded-md bg-amber-400/90 text-white text-xs font-semibold tabular-nums flex items-center justify-center shrink-0" x-text="unviewedActivityCount"></span>
                </button>
            </div>
            {{-- Sub-tab row: 2x2 grid on mobile, horizontal scroll on sm+ --}}
            <div x-show="activeParent() === 'finance'" x-cloak class="project-tabs-sub-row grid grid-cols-2 auto-rows-fr gap-2 min-h-[5.5rem] px-3 py-2.5 border-b theme-border bg-black/5 dark:bg-white/5 sm:flex sm:flex-nowrap sm:justify-center sm:overflow-x-auto sm:min-h-[2.75rem]">
                @if(!($isDeveloper ?? false))
                <button type="button" @click="setTab('payments')" :class="activeTab === 'payments' ? 'project-tab-active' : 'theme-bg-secondary theme-border theme-text-secondary border hover:bg-orange-500/10'" :style="activeTab === 'payments' ? { backgroundColor: '#EF8121', color: '#ffffff', borderColor: '#EF8121' } : {}" class="w-full h-full min-h-[2.5rem] flex items-center justify-center gap-1.5 rounded-[15px] text-sm font-medium border-2 border-transparent transition-all duration-200 px-2 py-2 sm:w-auto sm:h-auto sm:min-h-[2rem] sm:px-4 sm:py-1.5 sm:flex-shrink-0">
                    <span>Payments</span>
                    <span class="min-w-[1.25rem] h-5 px-1.5 rounded-md theme-bg-tertiary theme-text-secondary text-xs font-semibold tabular-nums flex items-center justify-center shrink-0">{{ $tabCountPayments }}</span>
                </button>
                <button type="button" @click="setTab('expenses')" :class="activeTab === 'expenses' ? 'project-tab-active' : 'theme-bg-secondary theme-border theme-text-secondary border hover:bg-orange-500/10'" :style="activeTab === 'expenses' ? { backgroundColor: '#EF8121', color: '#ffffff', borderColor: '#EF8121' } : {}" class="w-full h-full min-h-[2.5rem] flex items-center justify-center gap-1.5 rounded-[15px] text-sm font-medium border-2 border-transparent transition-all duration-200 px-2 py-2 sm:w-auto sm:h-auto sm:min-h-[2rem] sm:px-4 sm:py-1.5 sm:flex-shrink-0">
                    <span>Expenses</span>
                    <span class="min-w-[1.25rem] h-5 px-1.5 rounded-md theme-bg-tertiary theme-text-secondary text-xs font-semibold tabular-nums flex items-center justify-center shrink-0">{{ $tabCountExpenses }}</span>
                </button>
                <button type="button" @click="setTab('support')" :class="activeTab === 'support' ? 'project-tab-active' : 'theme-bg-secondary theme-border theme-text-secondary border hover:bg-orange-500/10'" :style="activeTab === 'support' ? { backgroundColor: '#EF8121', color: '#ffffff', borderColor: '#EF8121' } : {}" class="w-full h-full min-h-[2.5rem] flex items-center justify-center gap-1.5 rounded-[15px] text-sm font-medium border-2 border-transparent transition-all duration-200 px-2 py-2 sm:w-auto sm:h-auto sm:min-h-[2rem] sm:px-4 sm:py-1.5 sm:flex-shrink-0">
                    <span>Support</span>
                    <span class="min-w-[1.25rem] h-5 px-1.5 rounded-md theme-bg-tertiary theme-text-secondary text-xs font-semibold tabular-nums flex items-center justify-center shrink-0">{{ $tabCountSupport }}</span>
                </button>
                @if(!($isClient ?? false))
                <button type="button" @click="setTab('client')" :class="activeTab === 'client' ? 'project-tab-active' : 'theme-bg-secondary theme-border theme-text-secondary border hover:bg-orange-500/10'" :style="activeTab === 'client' ? { backgroundColor: '#EF8121', color: '#ffffff', borderColor: '#EF8121' } : {}" class="w-full h-full min-h-[2.5rem] flex items-center justify-center gap-1.5 rounded-[15px] text-sm font-medium border-2 border-transparent transition-all duration-200 px-2 py-2 sm:w-auto sm:h-auto sm:min-h-[2rem] sm:px-4 sm:py-1.5 sm:flex-shrink-0">
                    <span>Client</span>
                    <span class="min-w-[1.25rem] h-5 px-1.5 rounded-md theme-bg-tertiary theme-text-secondary text-xs font-semibold tabular-nums flex items-center justify-center shrink-0">1</span>
                </button>
                @endif
                @endif
            </div>
            <div x-show="activeParent() === 'files'" x-cloak class="project-tabs-sub-row grid grid-cols-2 auto-rows-fr gap-2 min-h-[5.5rem] px-3 py-2.5 border-b theme-border bg-black/5 dark:bg-white/5 sm:flex sm:flex-nowrap sm:justify-center sm:overflow-x-auto sm:min-h-[2.75rem]">
                @if(!($isDeveloper ?? false))
                <button type="button" @click="setTab('contracts')" :class="activeTab === 'contracts' ? 'project-tab-active' : 'theme-bg-secondary theme-border theme-text-secondary border hover:bg-orange-500/10'" :style="activeTab === 'contracts' ? { backgroundColor: '#EF8121', color: '#ffffff', borderColor: '#EF8121' } : {}" class="w-full h-full min-h-[2.5rem] flex items-center justify-center gap-1.5 rounded-[15px] text-sm font-medium border-2 border-transparent transition-all duration-200 px-2 py-2 sm:w-auto sm:h-auto sm:min-h-[2rem] sm:px-4 sm:py-1.5 sm:flex-shrink-0">
                    <span>Contracts</span>
                    <span class="min-w-[1.25rem] h-5 px-1.5 rounded-md theme-bg-tertiary theme-text-secondary text-xs font-semibold tabular-nums flex items-center justify-center shrink-0">{{ $tabCountContracts }}</span>
                </button>
                @endif
                <button type="button" @click="setTab('documents')" :class="activeTab === 'documents' ? 'project-tab-active' : 'theme-bg-secondary theme-border theme-text-secondary border hover:bg-orange-500/10'" :style="activeTab === 'documents' ? { backgroundColor: '#EF8121', color: '#ffffff', borderColor: '#EF8121' } : {}" class="w-full h-full min-h-[2.5rem] flex items-center justify-center gap-1.5 rounded-[15px] text-sm font-medium border-2 border-transparent transition-all duration-200 px-2 py-2 sm:w-auto sm:h-auto sm:min-h-[2rem] sm:px-4 sm:py-1.5 sm:flex-shrink-0">
                    <span>Documents</span>
                    <span class="min-w-[1.25rem] h-5 px-1.5 rounded-md theme-bg-tertiary theme-text-secondary text-xs font-semibold tabular-nums flex items-center justify-center shrink-0">{{ $tabCountDocuments }}</span>
                </button>
                <button type="button" @click="setTab('notes')" :class="activeTab === 'notes' ? 'project-tab-active' : 'theme-bg-secondary theme-border theme-text-secondary border hover:bg-orange-500/10'" :style="activeTab === 'notes' ? { backgroundColor: '#EF8121', color: '#ffffff', borderColor: '#EF8121' } : {}" class="w-full h-full min-h-[2.5rem] flex items-center justify-center gap-1.5 rounded-[15px] text-sm font-medium border-2 border-transparent transition-all duration-200 px-2 py-2 sm:w-auto sm:h-auto sm:min-h-[2rem] sm:px-4 sm:py-1.5 sm:flex-shrink-0">
                    <span>Notes</span>
                    <span class="min-w-[1.25rem] h-5 px-1.5 rounded-md theme-bg-tertiary theme-text-secondary text-xs font-semibold tabular-nums flex items-center justify-center shrink-0">{{ $tabCountNotes }}</span>
                </button>
                <button type="button" @click="setTab('links')" :class="activeTab === 'links' ? 'project-tab-active' : 'theme-bg-secondary theme-border theme-text-secondary border hover:bg-orange-500/10'" :style="activeTab === 'links' ? { backgroundColor: '#EF8121', color: '#ffffff', borderColor: '#EF8121' } : {}" class="w-full h-full min-h-[2.5rem] flex items-center justify-center gap-1.5 rounded-[15px] text-sm font-medium border-2 border-transparent transition-all duration-200 px-2 py-2 sm:w-auto sm:h-auto sm:min-h-[2rem] sm:px-4 sm:py-1.5 sm:flex-shrink-0">
                    <span>Links</span>
                    <span class="min-w-[1.25rem] h-5 px-1.5 rounded-md theme-bg-tertiary theme-text-secondary text-xs font-semibold tabular-nums flex items-center justify-center shrink-0">{{ $tabCountLinks }}</span>
                </button>
            </div>
            <div x-show="activeParent() === 'works'" x-cloak class="project-tabs-sub-row grid grid-cols-2 auto-rows-fr gap-2 min-h-[5.5rem] px-3 py-2.5 border-b theme-border bg-black/5 dark:bg-white/5 sm:flex sm:flex-nowrap sm:justify-center sm:overflow-x-auto sm:min-h-[2.75rem]">
                <button type="button" @click="setTab('tasks')" :class="activeTab === 'tasks' ? 'project-tab-active' : 'theme-bg-secondary theme-border theme-text-secondary border hover:bg-orange-500/10'" :style="activeTab === 'tasks' ? { backgroundColor: '#EF8121', color: '#ffffff', borderColor: '#EF8121' } : {}" class="w-full h-full min-h-[2.5rem] flex items-center justify-center gap-1.5 rounded-[15px] text-sm font-medium border-2 border-transparent transition-all duration-200 px-2 py-2 sm:w-auto sm:h-auto sm:min-h-[2rem] sm:px-4 sm:py-1.5 sm:flex-shrink-0">
                    <span>Tasks</span>
                    <span class="min-w-[1.25rem] h-5 px-1.5 rounded-md theme-bg-tertiary theme-text-secondary text-xs font-semibold tabular-nums flex items-center justify-center shrink-0">{{ $tabCountTasks }}</span>
                </button>
                <button type="button" @click="setTab('bugs')" :class="activeTab === 'bugs' ? 'project-tab-active' : 'theme-bg-secondary theme-border theme-text-secondary border hover:bg-orange-500/10'" :style="activeTab === 'bugs' ? { backgroundColor: '#EF8121', color: '#ffffff', borderColor: '#EF8121' } : {}" class="w-full h-full min-h-[2.5rem] flex items-center justify-center gap-1.5 rounded-[15px] text-sm font-medium border-2 border-transparent transition-all duration-200 px-2 py-2 sm:w-auto sm:h-auto sm:min-h-[2rem] sm:px-4 sm:py-1.5 sm:flex-shrink-0">
                    <span>Bugs</span>
                    <span class="min-w-[1.25rem] h-5 px-1.5 rounded-md theme-bg-tertiary theme-text-secondary text-xs font-semibold tabular-nums flex items-center justify-center shrink-0">{{ $tabCountBugs }}</span>
                </button>
            </div>

            @if(!($isDeveloper ?? false))
            {{-- Tab: Payments --}}
            <div x-show="activeTab === 'payments'" class="p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold theme-text-primary">Payments</h2>
                    @if(!($isClient ?? false) && (!config('payment.lock_after_final') || !$project->hasFinalPayment()))
                    <button @click="paymentModal = true" class="px-3 py-1.5 rounded-lg theme-btn-primary text-sm font-medium">Add</button>
                    @endif
                </div>
                @if(session('warning'))
                    <div class="mb-4 px-4 py-3 rounded-xl bg-amber-500/20 border border-amber-500/30 text-amber-400 text-sm">
                        {{ session('warning') }}
                    </div>
                @endif
                <ul class="space-y-3">
                    @forelse($project->payments as $payment)
                        <li class="flex items-center justify-between py-2 border-b theme-border/30 last:border-0 max-md:flex-col max-md:items-stretch max-md:gap-2 max-md:pb-4">
                            <div class="min-w-0">
                                <span class="payment-amount theme-text-primary font-medium">৳ {{ number_format($payment->amount, 0) }}</span>
                                @if($payment->payment_type)
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-indigo-500/20 text-indigo-400 ml-2">{{ ucfirst($payment->payment_type) }}</span>
                                @endif
                                @if($payment->payment_method)<span class="theme-text-secondary text-sm ml-2">· {{ $payment->payment_method }}</span>@endif
                                @if($payment->note)<span class="theme-text-muted text-sm ml-2">({{ $payment->note }})</span>@endif
                                <div class="flex items-center gap-2 mt-1 flex-wrap">
                                    @if($payment->payment_date)<span class="theme-text-muted text-xs">{{ $payment->payment_date->format('M d, Y') }}</span>@endif
                                    <span @class([
                                        'px-2 py-0.5 rounded text-xs font-medium',
                                        'bg-amber-500/20 text-amber-400' => $payment->payment_status === 'DUE',
                                        'bg-emerald-500/20 text-emerald-400' => $payment->payment_status === 'PAID',
                                        'bg-orange-500/20 text-orange-400' => $payment->status === 'upcoming' && $payment->payment_status !== 'PAID' && $payment->payment_status !== 'DUE',
                                        'theme-bg-tertiary theme-text-secondary' => !in_array($payment->payment_status ?? null, ['DUE', 'PAID']),
                                    ])>{{ $payment->payment_status === 'PAID' ? 'Paid' : ($payment->payment_status === 'DUE' ? 'DUE' : ucfirst($payment->status ?? '—')) }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0 flex-wrap justify-end max-md:w-full max-md:flex-col max-md:items-stretch">
                                @if(!($isClient ?? false))
                                    @if($payment->payment_status === 'DUE' && $payment->payment_link)
                                        <button type="button" data-payment-link="{{ $payment->payment_link }}" class="copy-payment-link px-3 py-1.5 rounded-lg bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 text-xs font-medium inline-flex items-center gap-1" title="Copy link">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                            Copy Payment Link
                                        </button>
                                        <form action="{{ route('projects.payments.send-payment-link-email', [$project, $payment]) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30 text-xs font-medium inline-flex items-center gap-1" title="Send payment link email to client (can be sent multiple times)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                Send Email
                                            </button>
                                        </form>
                                    @endif
                                    @if($payment->payment_status === 'DUE' && !$payment->payment_link)
                                        <form action="{{ route('projects.payments.generate-link', [$project, $payment]) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 text-xs font-medium inline-flex items-center gap-1" title="Create UddoktaPay link for this payment">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                                Generate Payment Link
                                            </button>
                                        </form>
                                    @endif
                                    @if($payment->payment_status === 'DUE')
                                        <form action="{{ route('projects.payments.mark-paid-cash', [$project, $payment]) }}" method="POST" class="inline" onsubmit="return confirm('Mark this payment as paid (cash/offline)? Invoice will be generated.');">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-amber-500/20 text-amber-400 hover:bg-amber-500/30 text-xs font-medium inline-flex items-center gap-1">Mark as Paid (Cash)</button>
                                        </form>
                                    @endif
                                @endif
                                @if($payment->invoice && (Auth::user()->isAdmin() || Auth::user()->isClient()))
                                    <a href="{{ route('invoices.view', $payment->invoice) }}" target="_blank" rel="noopener" class="px-3 py-1.5 rounded-lg bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 text-xs font-medium inline-flex items-center gap-1" title="Preview in browser">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        View
                                    </a>
                                    <a href="{{ route('invoices.download', $payment->invoice) }}" class="px-3 py-1.5 rounded-lg bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30 text-xs font-medium inline-flex items-center gap-1" title="Download PDF">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        Download
                                    </a>
                                @endif
                                @if(!($isClient ?? false) && (!config('payment.lock_after_final') || !$project->hasFinalPayment()))
                                    <button type="button" @click="paymentEditModal = {{ $payment->id }}" class="text-orange-400 hover:text-orange-300 text-sm">Edit</button>
                                    <form action="{{ route('projects.payments.destroy', [$project, $payment]) }}" method="POST" class="inline" onsubmit="return confirm('Remove this payment?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Remove</button>
                                    </form>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="theme-text-muted text-sm">No payments yet.</li>
                    @endforelse
                </ul>
                <div class="mt-4 pt-4 border-t theme-border flex justify-between text-sm">
                    <span class="theme-text-secondary">Total paid</span>
                    <span class="payment-amount font-semibold theme-text-primary">৳ {{ number_format($project->total_paid, 0) }}</span>
                </div>
                <div class="mt-1 flex justify-between text-sm">
                    <span class="theme-text-secondary">Due</span>
                    <span class="payment-amount font-semibold {{ $project->due > 0 ? 'text-amber-400' : 'text-emerald-400' }}">৳ {{ number_format($project->due, 0) }}</span>
                </div>
            </div>

            {{-- Tab: Expenses --}}
            <div x-show="activeTab === 'expenses'" class="p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold theme-text-primary">Expenses</h2>
                    @if(!($isClient ?? false))
                    <button @click="expenseModal = true" class="px-3 py-1.5 rounded-lg theme-btn-primary text-sm font-medium">Add</button>
                    @endif
                </div>
                <ul class="space-y-3">
                    @forelse($project->expenses as $expense)
                        <li class="flex items-center justify-between gap-3 py-2 border-b theme-border/30 last:border-0 max-md:flex-col max-md:items-stretch max-md:gap-2 max-md:pb-4">
                            <div class="min-w-0 flex-1">
                                <span class="payment-amount theme-text-primary font-medium">৳ {{ number_format($expense->amount, 0) }}</span>
                                @if($expense->note)<span class="theme-text-muted text-sm ml-2">— {{ Str::limit($expense->note, 30) }}</span>@endif
                            </div>
                            @if(!($isClient ?? false))
                            <div class="flex items-center gap-2 shrink-0">
                                <button type="button" @click="expenseEditModal = {{ $expense->id }}" class="text-orange-400 hover:text-orange-300 text-sm">Edit</button>
                                <form action="{{ route('projects.expenses.update', [$project, $expense]) }}" method="POST" class="inline-flex items-center gap-2" id="expense-visibility-{{ $expense->id }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_public" value="{{ $expense->is_public ? '1' : '0' }}" id="expense-is-public-input-{{ $expense->id }}">
                                    <label class="visibility-toggle-wrap relative inline-flex items-center cursor-pointer gap-2 {{ $expense->is_public ? 'is-checked' : '' }}" title="{{ $expense->is_public ? 'Public – click to make private' : 'Private – click to make public' }}">
                                        <input type="checkbox" {{ $expense->is_public ? 'checked' : '' }} class="sr-only expense-visibility-toggle" data-input-id="expense-is-public-input-{{ $expense->id }}" data-form-id="expense-visibility-{{ $expense->id }}">
                                        <span class="visibility-track relative block h-5 w-9 shrink-0 rounded-full border-2 theme-border theme-bg-tertiary transition-colors duration-200" aria-hidden="true" style="min-width: 2.25rem; min-height: 1.25rem;"></span>
                                        <span class="visibility-knob absolute z-10 rounded-full border-2 theme-border theme-bg-secondary shadow-md transition-transform duration-200 ease-out pointer-events-none" aria-hidden="true" style="left: 0.2rem; top: 0.2rem; width: 0.75rem; height: 0.75rem;"></span>
                                        <span class="visibility-label text-xs font-medium whitespace-nowrap {{ $expense->is_public ? 'text-orange-400' : 'theme-text-secondary' }}">{{ $expense->is_public ? 'Public' : 'Private' }}</span>
                                    </label>
                                </form>
                                <form action="{{ route('projects.expenses.destroy', [$project, $expense]) }}" method="POST" class="inline" onsubmit="return confirm('Remove this expense?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Remove</button>
                                </form>
                            </div>
                            @endif
                        </li>
                    @empty
                        <li class="theme-text-muted text-sm">No expenses yet.</li>
                    @endforelse
                </ul>
                <div class="mt-4 pt-4 border-t theme-border flex justify-between text-sm">
                    <span class="theme-text-secondary">Total expense</span>
                    <span class="payment-amount font-semibold theme-text-primary">৳ {{ number_format(($isClient ?? false) ? $project->public_expense_total : $project->expense_total, 0) }}</span>
                </div>
            </div>

            {{-- Tab: Support (Admin & Client only; Developer/Sales have NO visibility) --}}
            <div x-show="activeTab === 'support'" class="p-5">
                @if(session('success'))<p class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</p>@endif
                @if(session('error'))<p class="mb-4 px-4 py-3 rounded-xl bg-red-500/20 border border-red-500/30 text-red-400 text-sm">{{ session('error') }}</p>@endif
                @if(session('info'))<p class="mb-4 px-4 py-3 rounded-xl bg-orange-500/20 border border-orange-500/30 text-orange-400 text-sm">{{ session('info') }}</p>@endif
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold theme-text-primary">Support Packages</h2>
                    @if(!($isClient ?? false))
                    @php
                        $eligibleForClearShare = $project->supportPackages->filter(fn ($s) => $s->isPaid() && !$s->isShareCleared());
                    @endphp
                    <div class="flex items-center gap-2">
                        @if($eligibleForClearShare->isNotEmpty())
                        <button type="button" @click="clearShareModal = true" class="px-3 py-1.5 rounded-lg bg-violet-500/20 text-violet-400 hover:bg-violet-500/30 text-sm font-medium">Clear Share</button>
                        @endif
                        <a href="{{ route('support-packages.create', $project) }}" class="px-3 py-1.5 rounded-lg theme-btn-primary text-sm font-medium">Create Package</a>
                    </div>
                    @endif
                </div>
                {{-- Clear Share modal (admin only) --}}
                @if(!($isClient ?? false) && $eligibleForClearShare->isNotEmpty())
                <div x-show="clearShareModal" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" @click.self="clearShareModal = false">
                    <div class="theme-bg-tertiary border theme-border rounded-2xl shadow-xl max-w-md w-full p-5" @click.stop>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold theme-text-primary">Clear Share</h3>
                            <button type="button" @click="clearShareModal = false" class="theme-text-secondary theme-hover-primary">&times;</button>
                        </div>
                        <p class="theme-text-secondary text-sm mb-4">Select a paid support package to mark its share as cleared (internal use only).</p>
                        <ul class="space-y-2">
                            @foreach($eligibleForClearShare as $sp)
                            <li class="flex items-center justify-between py-2 px-3 rounded-xl theme-card-bg-only theme-border border">
                                <div>
                                    <span class="theme-text-primary font-medium">{{ $sp->package_label }}</span>
                                    <span class="theme-text-secondary text-sm ml-2">· ৳ {{ number_format($sp->amount, 0) }}</span>
                                </div>
                                <form action="{{ route('support-packages.mark-share-cleared', [$project, $sp]) }}" method="POST" class="inline" onsubmit="return confirm('Mark share as cleared for this package?');">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 rounded-lg bg-violet-500/20 text-violet-400 hover:bg-violet-500/30 text-xs font-medium">Share Clear</button>
                                </form>
                            </li>
                            @endforeach
                        </ul>
                        <button type="button" @click="clearShareModal = false" class="mt-4 w-full px-4 py-2 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover text-sm font-medium">Close</button>
                    </div>
                </div>
                @endif
                @php
                    $activeSupport = $project->supportPackages->filter(fn ($s) => $s->support_status === 'active');
                    $expiredSupport = $project->supportPackages->filter(fn ($s) => $s->support_status === 'expired');
                    $upcomingSupport = $project->supportPackages->filter(fn ($s) => $s->support_status === 'upcoming');
                    $dueSupport = $project->supportPackages->filter(fn ($s) => $s->payment_status === 'due');
                @endphp
                @if($activeSupport->isNotEmpty())
                <h3 class="text-sm font-medium text-emerald-400 mb-2">Active Support</h3>
                <ul class="space-y-2 mb-4">
                    @foreach($activeSupport as $sp)
                    <li class="flex items-center justify-between py-2 px-3 rounded-xl theme-card-bg-only theme-border border">
                        <div>
                            <span class="theme-text-primary font-medium">{{ $sp->package_label }}</span>
                            <span class="theme-text-secondary text-sm ml-2">· Expires {{ $sp->end_date->format('M j, Y') }}</span>
                            @if(!($isClient ?? false) && $sp->isShareCleared())
                            <span class="ml-2 px-2 py-0.5 rounded text-xs font-medium bg-violet-500/20 text-violet-400 border border-violet-500/40">Share cleared</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @if($sp->invoice_path && (Auth::user()->isAdmin() || Auth::user()->isClient()))
                            <a href="{{ route('support-packages.view-invoice', [$project, $sp]) }}" target="_blank" rel="noopener" class="px-2 py-1 rounded-lg bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 text-xs font-medium">View</a>
                            <a href="{{ route('support-packages.download-invoice', [$project, $sp]) }}" class="px-2 py-1 rounded-lg bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30 text-xs font-medium">Download Invoice</a>
                            @endif
                            @if(!($isClient ?? false))
                            <form action="{{ route('support-packages.destroy', [$project, $sp]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this support package?');">@csrf @method('DELETE')<button type="submit" class="px-2 py-1 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 text-xs font-medium">Delete</button></form>
                            @endif
                        </div>
                    </li>
                    @endforeach
                </ul>
                @endif
                @if($upcomingSupport->isNotEmpty())
                <h3 class="text-sm font-medium text-orange-400 mb-2">Upcoming</h3>
                <ul class="space-y-2 mb-4">
                    @foreach($upcomingSupport as $sp)
                    <li class="flex items-center justify-between py-2 px-3 rounded-xl theme-card-bg-only theme-border border">
                        <div class="flex items-center gap-2">
                            <span class="theme-text-primary">{{ $sp->package_label }}</span>
                            @if(!($isClient ?? false) && $sp->isShareCleared())
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-violet-500/20 text-violet-400 border border-violet-500/40">Share cleared</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="theme-text-secondary text-sm">Starts {{ $sp->start_date->format('M j, Y') }}</span>
                            @if(!($isClient ?? false))
                            <form action="{{ route('support-packages.destroy', [$project, $sp]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this support package?');">@csrf @method('DELETE')<button type="submit" class="px-2 py-1 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 text-xs font-medium">Delete</button></form>
                            @endif
                        </div>
                    </li>
                    @endforeach
                </ul>
                @endif
                @if($dueSupport->isNotEmpty())
                <h3 class="text-sm font-medium text-amber-400 mb-2">Due Payments</h3>
                <ul class="space-y-3">
                    @foreach($dueSupport as $sp)
                    <li class="flex items-center justify-between py-2 border-b theme-border/30 last:border-0 max-md:flex-col max-md:items-stretch max-md:gap-2">
                        <div>
                            <span class="payment-amount theme-text-primary font-medium">৳ {{ number_format($sp->amount, 0) }}</span>
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-amber-500/20 text-amber-400 ml-2">DUE</span>
                            <div class="theme-text-muted text-sm mt-0.5">{{ $sp->package_label }}</div>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            @if($sp->payment_link)
                                @if(Auth::user()->isClient())
                                <a href="{{ $sp->payment_link }}" target="_blank" rel="noopener" class="px-3 py-1.5 rounded-lg bg-orange-500 hover:bg-orange-600 theme-text-primary text-xs font-medium">Pay Now</a>
                                @else
                                <button type="button" data-payment-link="{{ $sp->payment_link }}" class="copy-payment-link px-3 py-1.5 rounded-lg bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 text-xs font-medium">Copy Link</button>
                                <form action="{{ route('support-packages.send-email', [$project, $sp]) }}" method="POST" class="inline">@csrf<button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30 text-xs font-medium">Send Email</button></form>
                                @endif
                            @else
                                @if(!($isClient ?? false))
                                <form action="{{ route('support-packages.generate-link', [$project, $sp]) }}" method="POST" class="inline">@csrf<button type="submit" class="px-3 py-1.5 rounded-lg bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 text-xs font-medium">Generate Payment Link</button></form>
                                @endif
                            @endif
                            @if(!($isClient ?? false))
                            <form action="{{ route('support-packages.mark-paid', [$project, $sp]) }}" method="POST" class="inline" onsubmit="return confirm('Mark as paid (cash/offline)?');">@csrf<button type="submit" class="px-3 py-1.5 rounded-lg bg-amber-500/20 text-amber-400 hover:bg-amber-500/30 text-xs font-medium">Mark as Paid</button></form>
                            <form action="{{ route('support-packages.destroy', [$project, $sp]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this support package?');">@csrf @method('DELETE')<button type="submit" class="px-2 py-1 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 text-xs font-medium">Delete</button></form>
                            @endif
                        </div>
                    </li>
                    @endforeach
                </ul>
                @endif
                @if($expiredSupport->isNotEmpty())
                <h3 class="text-sm font-medium theme-text-secondary mb-2 mt-4">Expired Support</h3>
                <ul class="space-y-2">
                    @foreach($expiredSupport as $sp)
                    <li class="flex items-center justify-between py-2 px-3 rounded-xl theme-card-bg-only theme-border border/30">
                        <div class="flex items-center gap-2">
                            <span class="theme-text-secondary">{{ $sp->package_label }}</span>
                            @if(!($isClient ?? false) && $sp->isShareCleared())
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-violet-500/20 text-violet-400 border border-violet-500/40">Share cleared</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="theme-text-muted text-sm">Ended {{ $sp->end_date->format('M j, Y') }}</span>
                            @if($sp->invoice_path && (Auth::user()->isAdmin() || Auth::user()->isClient()))
                            <a href="{{ route('support-packages.view-invoice', [$project, $sp]) }}" target="_blank" rel="noopener" class="px-2 py-1 rounded-lg bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 text-xs font-medium">View</a>
                            <a href="{{ route('support-packages.download-invoice', [$project, $sp]) }}" class="px-2 py-1 rounded-lg theme-btn-secondary text-xs font-medium">Download Invoice</a>
                            @endif
                            @if(!($isClient ?? false))
                            <form action="{{ route('support-packages.destroy', [$project, $sp]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this support package?');">@csrf @method('DELETE')<button type="submit" class="px-2 py-1 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 text-xs font-medium">Delete</button></form>
                            @endif
                        </div>
                    </li>
                    @endforeach
                </ul>
                @endif
                @if($project->supportPackages->isEmpty())
                <p class="theme-text-muted text-sm">No support packages yet. @if(!($isClient ?? false))<a href="{{ route('support-packages.create', $project) }}" class="text-orange-400 hover:text-orange-300">Create one</a>@endif</p>
                @endif
            </div>

            {{-- Tab: Client (admin only) --}}
            @if(!($isClient ?? false))
            @php $client = $project->client; @endphp
            <div x-show="activeTab === 'client'" class="p-5">
                @if(session('success'))<p class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</p>@endif
                @if(session('info'))<p class="mb-4 px-4 py-3 rounded-xl bg-orange-500/20 border border-orange-500/30 text-orange-400 text-sm">{{ session('info') }}</p>@endif
                @if(session('error'))<p class="mb-4 px-4 py-3 rounded-xl bg-red-500/20 border border-red-500/30 text-red-400 text-sm">{{ session('error') }}</p>@endif

                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold theme-text-primary">Client</h2>
                </div>

                {{-- Primary client: change dropdown --}}
                <div class="theme-card-bg-only theme-border border rounded-2xl p-5 mb-4">
                    <h3 class="text-sm font-medium theme-text-secondary mb-3">Primary client</h3>
                    <form action="{{ route('projects.client.update', $project) }}" method="POST" class="flex flex-wrap items-center gap-3">
                        @csrf
                        @method('PATCH')
                        <select name="client_id" class="rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 focus:ring-2 focus:ring-orange-500 min-w-[200px]" required>
                            @foreach($clientsForDropdown as $c)
                                <option value="{{ $c->id }}" {{ $c->id === $project->client_id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-3 py-2 rounded-lg theme-btn-primary text-sm font-medium">Change primary client</button>
                        <a href="{{ route('clients.show', $client) }}" class="px-3 py-2 rounded-lg theme-bg-tertiary/80 hover:theme-bg-tertiary theme-text-secondary text-sm font-medium">View client</a>
                    </form>
                </div>

                {{-- Primary client contact details --}}
                <div class="grid md:grid-cols-2 gap-4 mb-6">
                    <div class="theme-card-bg-only theme-border border rounded-2xl p-5">
                        <h3 class="text-sm font-medium theme-text-secondary mb-3">Contact</h3>
                        <p class="theme-text-secondary">Phone: {{ $client->phone ?? '—' }}</p>
                        <p class="theme-text-secondary">Email: {{ $client->email ?? '—' }}</p>
                        @if($client->fb_link)<p class="text-orange-400 mt-1"><a href="{{ $client->fb_link }}" target="_blank" rel="noopener">Facebook</a></p>@endif
                        @if($client->whatsapp_link)<p class="text-orange-400 mt-1"><a href="{{ $client->whatsapp_link }}" target="_blank" rel="noopener">WhatsApp</a></p>@endif
                    </div>
                    @if($client->address || $client->kyc)
                    <div class="theme-card-bg-only theme-border border rounded-2xl p-5">
                        <h3 class="text-sm font-medium theme-text-secondary mb-3">Details</h3>
                        @if($client->address)<p class="theme-text-secondary">{{ $client->address }}</p>@endif
                        @if($client->kyc)<p class="theme-text-secondary mt-1">KYC: {{ $client->kyc }}</p>@endif
                    </div>
                    @endif
                </div>
                @if(!$client->phone && !$client->email && !$client->fb_link && !$client->whatsapp_number && !$client->address && !$client->kyc)
                    <p class="theme-text-muted text-sm mb-6">No contact or details recorded for this client.</p>
                @endif

                {{-- Additional clients --}}
                <div class="theme-card-bg-only theme-border border rounded-2xl p-5">
                    <h3 class="text-sm font-medium theme-text-secondary mb-3">Additional clients (same project access)</h3>
                    <ul class="space-y-2 mb-4">
                        @forelse($project->additionalClients as $addClient)
                            <li class="flex items-center justify-between gap-3 py-2 border-b theme-border/30 last:border-0">
                                <span class="theme-text-primary">{{ $addClient->name }}</span>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('clients.show', $addClient) }}" class="text-orange-400 hover:text-orange-300 text-sm">View</a>
                                    <form action="{{ route('projects.additional-clients.destroy', [$project, $addClient]) }}" method="POST" class="inline" onsubmit="return confirm('Remove this client from the project?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Remove</button>
                                    </form>
                                </div>
                            </li>
                        @empty
                            <li class="theme-text-muted text-sm">No additional clients linked.</li>
                        @endforelse
                    </ul>
                    <form action="{{ route('projects.additional-clients.store', $project) }}" method="POST" class="flex flex-wrap items-center gap-3">
                        @csrf
                        <select name="client_id" class="rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 focus:ring-2 focus:ring-orange-500 min-w-[200px]" required>
                            <option value="">Select client to add…</option>
                            @foreach($clientsForDropdown as $c)
                                @if($c->id !== $project->client_id && !$project->additionalClients->contains('id', $c->id))
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <button type="submit" class="px-3 py-2 rounded-lg theme-btn-primary text-sm font-medium">Add client</button>
                    </form>
                </div>
            </div>
            @endif

            @endif
            {{-- Documents and below are visible to developers; only Payments/Expenses/Client are admin-only --}}
            @if(!($isSales ?? false))
            {{-- Tab: Documents --}}
            <div x-show="activeTab === 'documents'" class="p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold theme-text-primary">Documents</h2>
                    <button @click="documentModal = true" class="px-3 py-1.5 rounded-lg theme-btn-primary text-sm font-medium">Upload</button>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 max-md:gap-3">
                    @forelse($project->documents as $doc)
                        <div class="theme-bg-tertiary/80 border theme-border rounded-xl p-4 flex flex-col gap-3">
                            <a href="{{ route('projects.documents.view', [$project, $doc]) }}" target="_blank" rel="noopener noreferrer" class="font-medium theme-text-primary hover:text-orange-400 transition block min-w-0 break-words line-clamp-2" title="{{ $doc->title }}">{{ $doc->title }}</a>
                            <div class="flex flex-wrap items-center justify-between gap-2 mt-auto">
                                <p class="theme-text-muted text-xs flex flex-wrap items-center gap-x-1 gap-y-0.5">
                                    @php $ext = pathinfo($doc->file_path, PATHINFO_EXTENSION); @endphp
                                    @if($ext)<span class="theme-text-secondary font-medium uppercase">{{ $ext }}</span><span class="theme-text-muted">·</span>@endif
                                    <span class="whitespace-nowrap">{{ $doc->uploaded_at?->format('M j, Y') }}</span>
                                    <span class="theme-text-muted">·</span>
                                    <span class="theme-text-secondary whitespace-nowrap">Uploaded by: {{ $doc->uploadedBy ? ucfirst($doc->uploadedBy->role) : '—' }}</span>
                                </p>
                                @php
                                    $canEditDocument = !($isClient ?? false) && (Auth::user()->isAdmin() || (($isDeveloper ?? false) && $doc->uploaded_by_user_id === Auth::id()) || (($isSales ?? false) && $doc->uploaded_by_user_id === Auth::id()));
                                    $canChangeDocumentVisibility = !($isClient ?? false) && (Auth::user()->isAdmin() || (($isSales ?? false) && $doc->uploaded_by_user_id === Auth::id()));
                                @endphp
                                @if(!($isClient ?? false))
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('projects.documents.download', [$project, $doc]) }}" class="px-3 py-1.5 rounded-lg theme-bg-tertiary/80 hover:theme-bg-tertiary theme-text-secondary text-sm font-medium whitespace-nowrap transition">Download</a>
                                    @if($canEditDocument)
                                    <form action="{{ route('projects.documents.destroy', [$project, $doc]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this document?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-900/40 hover:bg-red-800/50 text-red-400 text-sm font-medium whitespace-nowrap transition border border-red-700/50">Delete</button>
                                    </form>
                                    @endif
                                    @if($canChangeDocumentVisibility)
                                    <form action="{{ route('projects.documents.update', [$project, $doc]) }}" method="POST" class="inline-flex items-center" id="document-visibility-{{ $doc->id }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="is_public" value="{{ $doc->is_public ? '1' : '0' }}" id="document-is-public-input-{{ $doc->id }}">
                                        <label class="visibility-toggle-wrap relative inline-flex items-center cursor-pointer gap-1.5 {{ $doc->is_public ? 'is-checked' : '' }}" title="{{ $doc->is_public ? 'Public – click to make private' : 'Private – click to make public' }}">
                                            <input type="checkbox" {{ $doc->is_public ? 'checked' : '' }} class="sr-only document-visibility-toggle" data-input-id="document-is-public-input-{{ $doc->id }}" data-form-id="document-visibility-{{ $doc->id }}">
                                            <span class="visibility-track relative block h-5 w-9 shrink-0 rounded-full border-2 theme-border theme-bg-tertiary transition-colors duration-200" aria-hidden="true" style="min-width: 2.25rem; min-height: 1.25rem;"></span>
                                            <span class="visibility-knob absolute z-10 rounded-full border-2 theme-border theme-bg-secondary shadow-md transition-transform duration-200 ease-out pointer-events-none" aria-hidden="true" style="left: 0.2rem; top: 0.2rem; width: 0.75rem; height: 0.75rem;"></span>
                                            <span class="visibility-label text-xs font-medium whitespace-nowrap {{ $doc->is_public ? 'text-orange-400' : 'theme-text-secondary' }}">{{ $doc->is_public ? 'Public' : 'Private' }}</span>
                                        </label>
                                    </form>
                                    @endif
                                </div>
                                @else
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('projects.documents.download', [$project, $doc]) }}" class="px-3 py-1.5 rounded-lg theme-bg-tertiary/80 hover:theme-bg-tertiary theme-text-secondary text-sm font-medium whitespace-nowrap transition">Download</a>
                                    @if($doc->uploaded_by_user_id && $doc->uploaded_by_user_id == auth()->id())
                                    <form action="{{ route('projects.documents.destroy', [$project, $doc]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this document?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-900/40 hover:bg-red-800/50 text-red-400 text-sm font-medium whitespace-nowrap transition border border-red-700/50">Delete</button>
                                    </form>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="theme-text-muted text-sm col-span-full">No documents yet.</p>
                    @endforelse
                </div>
            </div>
            @endif

            {{-- Tab: Contracts --}}
            <div x-show="activeTab === 'contracts'" class="p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold theme-text-primary">Contracts</h2>
                    @if(!($isClient ?? false))
                    <button @click="contractModal = true" class="px-3 py-1.5 rounded-lg theme-btn-primary text-sm font-medium">Upload Contract</button>
                    @endif
                </div>
                <ul class="space-y-4">
                    @forelse($project->contracts as $contract)
                        <li class="theme-bg-tertiary/80 border theme-border rounded-xl p-4 flex flex-wrap items-center justify-between gap-3">
                            <div class="min-w-0">
                                <span class="font-medium theme-text-primary">Contract #{{ $contract->id }}</span>
                                <span @class([
                                    'ml-2 px-2 py-0.5 rounded text-xs font-medium',
                                    'bg-amber-500/20 text-amber-400' => $contract->status === 'pending',
                                    'bg-emerald-500/20 text-emerald-400' => $contract->status === 'signed',
                                ])>{{ $contract->status === 'signed' ? 'Signed' : 'Pending' }}</span>
                                <p class="theme-text-muted text-xs mt-1">
                                    Uploaded {{ $contract->created_at->format('M j, Y') }}@if($contract->signed_at) · Signed {{ $contract->signed_at->format('M j, Y') }}@endif
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('projects.contracts.view', [$project, $contract]) }}" target="_blank" rel="noopener" class="px-3 py-1.5 rounded-lg theme-bg-tertiary/80 hover:theme-bg-tertiary theme-text-secondary text-sm font-medium">View</a>
                                @if($contract->status === 'signed')
                                    <a href="{{ route('projects.contracts.download', [$project, $contract]) }}" class="px-3 py-1.5 rounded-lg bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30 text-sm font-medium">Download Signed</a>
                                @elseif($isClient ?? false)
                                    <a href="{{ route('projects.contracts.sign-form', [$project, $contract]) }}" class="px-3 py-1.5 rounded-lg bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 text-sm font-medium">Sign Contract</a>
                                @endif
                                @if(!($isClient ?? false))
                                    @if($contract->status === 'pending')
                                        <form action="{{ route('projects.contracts.send-email', [$project, $contract]) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-amber-500/20 text-amber-400 hover:bg-amber-500/30 text-sm font-medium">Send for Signature</button>
                                        </form>
                                    @endif
                                    <a href="{{ route('projects.contracts.download', [$project, $contract]) }}" class="px-3 py-1.5 rounded-lg theme-bg-tertiary/80 hover:theme-bg-tertiary theme-text-secondary text-sm font-medium">Download</a>
                                    <form action="{{ route('projects.contracts.destroy', [$project, $contract]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this contract? Original and signed files will be removed.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-900/40 hover:bg-red-800/50 text-red-400 text-sm font-medium border border-red-700/50">Delete</button>
                                    </form>
                                @endif
                            </div>
                            <div class="w-full mt-2 pt-2 border-t theme-border">
                                <p class="theme-text-secondary text-xs font-medium mb-1">Audit trail</p>
                                <ul class="theme-text-muted text-xs space-y-0.5">
                                    @foreach($contract->audits->take(5) as $audit)
                                        <li>{{ $audit->action }} · {{ $audit->user?->name ?? 'System' }} · {{ $audit->created_at->format('M j, Y g:i A') }}</li>
                                    @endforeach
                                    @if($contract->audits->isEmpty())
                                        <li>No activity yet.</li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    @empty
                        <li class="theme-text-muted text-sm">No contracts yet. @if(!($isClient ?? false)) Upload a contract (PDF preferred) to send for signature. @endif</li>
                    @endforelse
                </ul>
            </div>

            @if(!($isSales ?? false))
            {{-- Tab: Tasks --}}
            <div x-show="activeTab === 'tasks'" class="p-5" x-data="{ milestoneModal: false, milestoneEditId: null }">
                @php
                    $taskTotal = $project->tasks->count();
                    $taskDone = $project->tasks->where('status', 'done')->count();
                    $taskPct = $taskTotal > 0 ? round(($taskDone / $taskTotal) * 100) : 0;
                @endphp
                <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                    <div class="flex items-center gap-4">
                        <h2 class="font-semibold theme-text-primary">Tasks</h2>
                        <span class="theme-text-secondary text-sm">{{ $taskDone }}/{{ $taskTotal }} done ({{ $taskPct }}%)</span>
                    </div>
                    @if(!($isClient ?? false) && !($isDeveloper ?? false) && !($isSales ?? false))
                    <div class="flex items-center gap-2">
                        <button @click="milestoneModal = true; milestoneEditId = null" class="px-3 py-1.5 rounded-lg bg-violet-500/20 text-violet-400 hover:bg-violet-500/30 text-sm font-medium">Add Milestone</button>
                        <button @click="taskModal = true" class="px-3 py-1.5 rounded-lg theme-btn-primary text-sm font-medium">Add Task</button>
                    </div>
                    @endif
                </div>
                <div class="h-2 theme-bg-tertiary rounded-full overflow-hidden mb-6">
                    <div class="h-full bg-emerald-500 rounded-full transition-all" style="width: {{ $taskPct }}%"></div>
                </div>
                <div class="space-y-8">
                    @foreach($project->milestones as $milestone)
                    @php
                        $mTasks = $project->tasks->where('milestone_id', $milestone->id);
                        $mDone = $mTasks->where('status', 'done')->count();
                        $mTotal = $mTasks->count();
                        $mPct = $mTotal > 0 ? round(($mDone / $mTotal) * 100) : 0;
                    @endphp
                    <div class="rounded-xl border theme-border overflow-hidden">
                        <div class="flex items-center justify-between theme-bg-tertiary/60 px-4 py-3 border-b theme-border">
                            <div class="flex items-center gap-3">
                                <h3 class="font-medium theme-text-primary">{{ $milestone->name }}</h3>
                                <span class="theme-text-secondary text-sm">{{ $mDone }}/{{ $mTotal }} done</span>
                                @if($milestone->completed_at)
                                <span class="px-2 py-0.5 rounded-lg text-xs font-medium bg-emerald-500/20 text-emerald-400">Completed</span>
                                @endif
                            </div>
                            @if(!($isClient ?? false) && !($isDeveloper ?? false) && !($isSales ?? false))
                            <div class="flex items-center gap-2">
                                <form action="{{ route('projects.milestones.destroy', [$project, $milestone]) }}" method="POST" class="inline" onsubmit="return confirm('Remove this milestone? Tasks will be moved to &quot;No milestone&quot;.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="theme-text-muted hover:text-red-400 text-xs">Remove</button>
                                </form>
                            </div>
                            @endif
                        </div>
                        <div class="grid md:grid-cols-3 gap-4 max-md:grid-cols-1 max-md:gap-3 p-4 theme-bg-tertiary/20">
                            <div class="theme-card-bg-only theme-border border rounded-xl p-4">
                                <h4 class="text-amber-400 font-medium text-xs mb-3">To Do</h4>
                                <div class="space-y-3">
                                    @foreach($mTasks->where('status', 'todo') as $task)
                                        @include('projects.partials.task-card', ['task' => $task, 'project' => $project, 'isClient' => $isClient ?? false, 'isDeveloper' => $isDeveloper ?? false, 'isSales' => $isSales ?? false])
                                    @endforeach
                                    @if($mTasks->where('status', 'todo')->isEmpty())
                                        <p class="theme-text-muted text-sm">No tasks</p>
                                    @endif
                                </div>
                            </div>
                            <div class="theme-card-bg-only theme-border border rounded-xl p-4">
                                <h4 class="text-amber-400 font-medium text-xs mb-3">Doing</h4>
                                <div class="space-y-3">
                                    @foreach($mTasks->where('status', 'doing') as $task)
                                        @include('projects.partials.task-card', ['task' => $task, 'project' => $project, 'isClient' => $isClient ?? false, 'isDeveloper' => $isDeveloper ?? false, 'isSales' => $isSales ?? false])
                                    @endforeach
                                    @if($mTasks->where('status', 'doing')->isEmpty())
                                        <p class="theme-text-muted text-sm">No tasks</p>
                                    @endif
                                </div>
                            </div>
                            <div class="theme-card-bg-only theme-border border rounded-xl p-4">
                                <h4 class="text-emerald-400 font-medium text-xs mb-3">Done</h4>
                                <div class="space-y-3">
                                    @foreach($mTasks->where('status', 'done') as $task)
                                        @include('projects.partials.task-card', ['task' => $task, 'project' => $project, 'isClient' => $isClient ?? false, 'isDeveloper' => $isDeveloper ?? false, 'isSales' => $isSales ?? false])
                                    @endforeach
                                    @if($mTasks->where('status', 'done')->isEmpty())
                                        <p class="theme-text-muted text-sm">No tasks</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @php $unassignedTasks = $project->tasks->whereNull('milestone_id'); @endphp
                    <div class="rounded-xl border theme-border overflow-hidden">
                        <div class="flex items-center justify-between theme-bg-tertiary/40 px-4 py-3 border-b theme-border">
                            <h3 class="font-medium theme-text-secondary">No milestone</h3>
                            <span class="theme-text-muted text-sm">{{ $unassignedTasks->where('status', 'done')->count() }}/{{ $unassignedTasks->count() }} done</span>
                        </div>
                        <div class="grid md:grid-cols-3 gap-4 max-md:grid-cols-1 max-md:gap-3 p-4 theme-bg-tertiary/20">
                            <div class="theme-card-bg-only theme-border border rounded-xl p-4">
                                <h4 class="text-amber-400 font-medium text-xs mb-3">To Do</h4>
                                <div class="space-y-3">
                                    @foreach($unassignedTasks->where('status', 'todo') as $task)
                                        @include('projects.partials.task-card', ['task' => $task, 'project' => $project, 'isClient' => $isClient ?? false, 'isDeveloper' => $isDeveloper ?? false, 'isSales' => $isSales ?? false])
                                    @endforeach
                                    @if($unassignedTasks->where('status', 'todo')->isEmpty())
                                        <p class="theme-text-muted text-sm">No tasks</p>
                                    @endif
                                </div>
                            </div>
                            <div class="theme-card-bg-only theme-border border rounded-xl p-4">
                                <h4 class="text-amber-400 font-medium text-xs mb-3">Doing</h4>
                                <div class="space-y-3">
                                    @foreach($unassignedTasks->where('status', 'doing') as $task)
                                        @include('projects.partials.task-card', ['task' => $task, 'project' => $project, 'isClient' => $isClient ?? false, 'isDeveloper' => $isDeveloper ?? false, 'isSales' => $isSales ?? false])
                                    @endforeach
                                    @if($unassignedTasks->where('status', 'doing')->isEmpty())
                                        <p class="theme-text-muted text-sm">No tasks</p>
                                    @endif
                                </div>
                            </div>
                            <div class="theme-card-bg-only theme-border border rounded-xl p-4">
                                <h4 class="text-emerald-400 font-medium text-xs mb-3">Done</h4>
                                <div class="space-y-3">
                                    @foreach($unassignedTasks->where('status', 'done') as $task)
                                        @include('projects.partials.task-card', ['task' => $task, 'project' => $project, 'isClient' => $isClient ?? false, 'isDeveloper' => $isDeveloper ?? false, 'isSales' => $isSales ?? false])
                                    @endforeach
                                    @if($unassignedTasks->where('status', 'done')->isEmpty())
                                        <p class="theme-text-muted text-sm">No tasks</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Add Milestone Modal --}}
                @if(!($isClient ?? false) && !($isDeveloper ?? false) && !($isSales ?? false))
                <div x-show="milestoneModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div x-show="milestoneModal" x-transition class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="milestoneModal = false"></div>
                        <div x-show="milestoneModal" x-transition class="relative w-full max-w-md theme-bg-tertiary border theme-border rounded-2xl shadow-xl p-6">
                            <h2 class="text-lg font-semibold theme-text-primary mb-4">Add Milestone</h2>
                            <form action="{{ route('projects.milestones.store', $project) }}" method="POST">
                                @csrf
                                <div>
                                    <label class="block text-sm font-medium theme-text-secondary mb-1">Name *</label>
                                    <input type="text" name="name" required placeholder="e.g. Milestone 1, Phase 1" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                                </div>
                                <div class="mt-6 flex justify-end gap-3">
                                    <button type="button" @click="milestoneModal = false" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover">Cancel</button>
                                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-violet-500 hover:bg-violet-600 theme-text-primary font-medium">Add Milestone</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Tab: Bugs --}}
            <div x-show="activeTab === 'bugs'" class="p-5" x-data="{ bugFilter: 'all' }">
                <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="font-semibold theme-text-primary">Bugs</h2>
                        <button @click="bugFilter = 'all'" :class="bugFilter === 'all' ? 'theme-bg-tertiary theme-text-primary' : 'theme-text-secondary theme-hover-primary'" class="px-2.5 py-1 rounded-lg text-sm">All</button>
                        <button @click="bugFilter = 'open'" :class="bugFilter === 'open' ? 'bg-red-500/20 text-red-400' : 'theme-text-secondary theme-hover-primary'" class="px-2.5 py-1 rounded-lg text-sm">Open</button>
                        <button @click="bugFilter = 'in_progress'" :class="bugFilter === 'in_progress' ? 'bg-amber-500/20 text-amber-400' : 'theme-text-secondary theme-hover-primary'" class="px-2.5 py-1 rounded-lg text-sm">In Progress</button>
                        <button @click="bugFilter = 'resolved'" :class="bugFilter === 'resolved' ? 'bg-emerald-500/20 text-emerald-400' : 'theme-text-secondary theme-hover-primary'" class="px-2.5 py-1 rounded-lg text-sm">Resolved</button>
                    </div>
                    @if(!($isDeveloper ?? false) && !($isSales ?? false))
                    <button @click="bugModal = true" class="w-full shrink-0 rounded-lg theme-btn-primary text-sm font-medium px-3 py-1.5 sm:w-auto">Report Bug</button>
                    @endif
                </div>
                <div class="space-y-3">
                    @foreach($project->bugs as $bug)
                        <div class="theme-bg-tertiary/80 border theme-border rounded-xl overflow-hidden transition-all duration-200 hover:theme-border"
                             x-show="bugFilter === 'all' || bugFilter === '{{ $bug->status }}'"
                             :class="{ 'ring-1 ring-orange-500/30': expandedBugId == {{ $bug->id }} }">
                            <button type="button" @click="expandedBugId = expandedBugId == {{ $bug->id }} ? null : {{ $bug->id }}" class="w-full text-left p-4">
                                <p class="font-medium theme-text-primary">{{ $bug->title }}</p>
                                <p class="theme-text-muted text-xs mt-1">Reported {{ $bug->created_at->format('d M Y, h:i A') }}</p>
                                @if($bug->status === 'in_progress' && ($bug->status_updated_at ?? $bug->updated_at))
                                    <p class="text-amber-400/90 text-xs mt-0.5">In progress since {{ ($bug->status_updated_at ?? $bug->updated_at)->format('d M Y, h:i A') }}</p>
                                @elseif($bug->status === 'resolved' && ($bug->status_updated_at ?? $bug->updated_at))
                                    <p class="text-emerald-400/90 text-xs mt-0.5">Resolved at {{ ($bug->status_updated_at ?? $bug->updated_at)->format('d M Y, h:i A') }}</p>
                                @endif
                                @if($bug->description)<p class="theme-text-muted text-sm mt-1 line-clamp-2">{{ Str::limit($bug->description, 80) }}</p>@endif
                                <div class="flex flex-wrap gap-2 mt-2 items-center">
                                    <span @class([
                                        'px-2 py-0.5 rounded text-xs font-medium',
                                        'bg-red-500/20 text-red-400' => $bug->severity === 'critical',
                                        'bg-amber-500/20 text-amber-400' => $bug->severity === 'major',
                                        'theme-bg-tertiary theme-text-secondary' => $bug->severity === 'minor',
                                    ])>{{ ucfirst($bug->severity) }}</span>
                                    @if($bug->attachment_path)
                                        <span class="text-orange-400 text-xs flex items-center gap-1">Attachment</span>
                                    @endif
                                </div>
                            </button>
                            <div x-show="expandedBugId == {{ $bug->id }}" x-transition class="px-4 pb-4 border-t theme-border">
                                <div class="pt-3 theme-text-secondary text-sm whitespace-pre-wrap">{{ $bug->description ?: '—' }}</div>
                                @if($bug->attachment_path)
                                    <p class="mt-3 flex flex-wrap items-center gap-3">
                                        <a href="{{ route('projects.bugs.view-attachment', [$project, $bug]) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-orange-400 hover:text-orange-300 text-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            View
                                        </a>
                                        <a href="{{ route('projects.bugs.attachment', [$project, $bug]) }}" class="inline-flex items-center gap-1.5 text-orange-400 hover:text-orange-300 text-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                            Download attachment
                                        </a>
                                    </p>
                                @endif
                                @php $canChangeBugStatus = !($isClient ?? false) && ((($isDeveloper ?? false) && $bug->assigned_to_user_id === Auth::id()) || (!($isDeveloper ?? false) && !($isSales ?? false))); @endphp
                                @if(!($isClient ?? false))
                                <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
                                    @if($canChangeBugStatus)
                                    <form action="{{ route('projects.bugs.update', [$project, $bug]) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="title" value="{{ $bug->title }}">
                                        <input type="hidden" name="description" value="{{ $bug->description }}">
                                        <input type="hidden" name="severity" value="{{ $bug->severity }}">
                                        <input type="hidden" name="is_public" value="{{ $bug->is_public ? '1' : '0' }}">
                                        <select name="status" onchange="this.form.submit()" class="rounded-lg theme-input-bg border theme-border theme-text-primary text-sm px-3 py-1.5 focus:ring-orange-500">
                                            <option value="open" {{ $bug->status === 'open' ? 'selected' : '' }}>Open</option>
                                            <option value="in_progress" {{ $bug->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                            <option value="resolved" {{ $bug->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                        </select>
                                    </form>
                                    @endif
                                    @if(!($isDeveloper ?? false) && !($isSales ?? false))
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="bugEditModal = {{ $bug->id }}" class="text-orange-400 hover:text-orange-300 text-sm">Edit</button>
                                        <form action="{{ route('projects.bugs.destroy', [$project, $bug]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this bug?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
                                        </form>
                                    </div>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                    @if($project->bugs->isEmpty())
                        <p class="theme-text-muted text-sm">No bugs reported.</p>
                    @endif
                </div>
            </div>

            {{-- Tab: Notes --}}
            <div x-show="activeTab === 'notes'" class="p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold theme-text-primary">Notes</h2>
                    @if(!($isClient ?? false))
                    <button @click="noteModal = true" class="px-3 py-1.5 rounded-lg theme-btn-primary text-sm font-medium">New Note</button>
                    @endif
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 max-md:grid-cols-1 max-md:gap-3">
                    @forelse($project->projectNotes as $note)
                        <div class="theme-bg-tertiary/80 border theme-border rounded-2xl overflow-hidden hover:theme-border hover:shadow-lg transition-all duration-200"
                             :class="{ 'ring-1 ring-orange-500/30': expandedNoteId == {{ $note->id }} }">
                            <button type="button" @click="expandedNoteId = expandedNoteId == {{ $note->id }} ? null : {{ $note->id }}" class="w-full text-left p-4">
                                <p class="font-semibold theme-text-primary">{{ $note->title }}</p>
                                <p class="theme-text-muted text-xs mt-1">{{ $note->created_at->format('M d, Y') }} · Added by {{ $note->creator ? ucfirst($note->creator->role) : '—' }}</p>
                                <p class="theme-text-secondary text-sm mt-2 line-clamp-2">{{ Str::limit(strip_tags($note->body), 120) ?: 'No content' }}</p>
                                <span class="inline-flex items-center gap-1 mt-2 px-2 py-0.5 rounded text-xs {{ $note->visibility === 'client' ? 'bg-orange-500/20 text-orange-400' : 'theme-bg-tertiary/50 theme-text-secondary' }}" title="{{ $note->visibility === 'client' ? 'Visible to client' : 'Admin only' }}">{{ $note->visibility === 'client' ? 'Public' : 'Private' }}</span>
                            </button>
                            <div x-show="expandedNoteId == {{ $note->id }}" x-transition class="px-4 pb-4">
                                <div class="pt-2 border-t theme-border theme-text-secondary text-sm whitespace-pre-wrap">{{ $note->body ?: '—' }}</div>
                                @php
                                    $canEditNote = !($isClient ?? false) && (Auth::user()->isAdmin() || (($isDeveloper ?? false) && $note->created_by === Auth::id()) || (($isSales ?? false) && $note->created_by === Auth::id()));
                                    $canChangeNoteVisibility = !($isClient ?? false) && (Auth::user()->isAdmin() || (($isSales ?? false) && $note->created_by === Auth::id()));
                                @endphp
                                @if($canEditNote)
                                <div class="px-4 py-3 border-t theme-border flex flex-wrap items-center justify-between gap-2">
                                    @if($canChangeNoteVisibility)
                                    <form action="{{ route('projects.notes.update', [$project, $note]) }}" method="POST" class="inline-flex items-center gap-2" id="note-visibility-form-{{ $note->id }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="title" value="{{ old('title', $note->title) }}">
                                        <input type="hidden" name="body" value="{{ old('body', $note->body ?? '') }}">
                                        <input type="hidden" name="visibility" id="note-visibility-value-{{ $note->id }}" value="{{ $note->visibility }}">
                                        <label class="visibility-toggle-wrap relative inline-flex items-center cursor-pointer gap-2 {{ $note->visibility === 'client' ? 'is-checked' : '' }}" title="{{ $note->visibility === 'client' ? 'Public – click to make private' : 'Private – click to make public' }}">
                                            <input type="checkbox" {{ $note->visibility === 'client' ? 'checked' : '' }} class="sr-only note-visibility-toggle" data-value-input-id="note-visibility-value-{{ $note->id }}" data-form-id="note-visibility-form-{{ $note->id }}">
                                            <span class="visibility-track relative block h-5 w-9 shrink-0 rounded-full border-2 theme-border theme-bg-tertiary transition-colors duration-200" aria-hidden="true" style="min-width: 2.25rem; min-height: 1.25rem;"></span>
                                            <span class="visibility-knob absolute z-10 rounded-full border-2 theme-border theme-bg-secondary shadow-md transition-transform duration-200 ease-out pointer-events-none" aria-hidden="true" style="left: 0.2rem; top: 0.2rem; width: 0.75rem; height: 0.75rem;"></span>
                                            <span class="visibility-label text-xs font-medium whitespace-nowrap {{ $note->visibility === 'client' ? 'text-orange-400' : 'theme-text-secondary' }}">{{ $note->visibility === 'client' ? 'Public' : 'Private' }}</span>
                                        </label>
                                    </form>
                                    @endif
                                    <div class="flex gap-2">
                                        <button type="button" @click="noteEditModal = {{ $note->id }}" class="text-orange-400 hover:text-orange-300 text-sm">Edit</button>
                                        <form action="{{ route('projects.notes.destroy', [$project, $note]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this note?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
                                        </form>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="theme-text-muted text-sm col-span-full">No notes yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Tab: Links --}}
            <div x-show="activeTab === 'links'" class="p-5">
                <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                    <div>
                        <h2 class="font-semibold theme-text-primary">Live Links &amp; APK Downloads</h2>
                        @if(!($isClient ?? false))
                        <p class="theme-text-muted text-sm mt-0.5">Add multiple live URLs and/or APK files. Each entry can be shown or hidden on the public portal.</p>
                        @endif
                    </div>
                    @if(!($isClient ?? false))
                    <button @click="linkModal = true" class="px-4 py-2 rounded-lg theme-btn-primary text-sm font-medium shrink-0">+ Add Live Link or APK</button>
                    @endif
                </div>
                <ul class="space-y-4">
                    @forelse($project->projectLinks as $link)
                        <li class="theme-bg-tertiary/80 border theme-border rounded-xl p-4 flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <p class="font-medium theme-text-primary">{{ $link->label }}</p>
                                @if($link->isApk() && $link->file_path)
                                    <a href="{{ route('projects.links.download', [$project, $link]) }}" class="inline-flex items-center gap-1.5 mt-1 text-orange-400 hover:text-orange-300 text-sm">Download APK</a>
                                @else
                                    <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="text-orange-400 hover:text-orange-300 text-sm mt-1 break-all">{{ $link->url }}</a>
                                @endif
                                @if(!$link->isApk() && ($link->login_username || $link->login_password))
                                    <div class="mt-2 pt-2 border-t theme-border theme-text-secondary text-sm space-y-1">
                                        @if($link->login_username)
                                            <p>Username: <span class="theme-text-secondary">{{ $link->login_username }}</span></p>
                                        @endif
                                        @if($link->login_password)
                                            <p>Password: <span class="theme-text-secondary">{{ $link->login_password }}</span></p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            @php $canEditLink = !($isClient ?? false) && (Auth::user()->isAdmin() || (($isDeveloper ?? false) && $link->created_by === Auth::id()) || (($isSales ?? false) && $link->created_by === Auth::id())); @endphp
                            @if($canEditLink)
                            <div class="flex items-center gap-2 shrink-0 flex-wrap">
                                <form action="{{ route('projects.links.update', [$project, $link]) }}" method="POST" class="inline-flex items-center gap-2" id="link-visibility-{{ $link->id }}" onchange="this.submit()">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="link_type" value="{{ $link->link_type ?? 'url' }}">
                                    <input type="hidden" name="label" value="{{ old('label', $link->label) }}">
                                    <input type="hidden" name="url" value="{{ old('url', $link->url ?? '#') }}">
                                    <input type="hidden" name="login_username" value="{{ old('login_username', $link->login_username ?? '') }}">
                                    <input type="hidden" name="login_password" value="{{ old('login_password', $link->login_password ?? '') }}">
                                    <label class="theme-text-secondary text-xs font-medium">Who can see:</label>
                                    <select name="visibility" class="rounded-lg theme-bg-tertiary border theme-border theme-text-primary text-xs px-2.5 py-1.5 focus:ring-1 focus:ring-orange-500 min-w-[10rem]">
                                        @foreach(\App\Models\ProjectLink::visibilityLabels() as $value => $label)
                                            <option value="{{ $value }}" {{ ($link->visibility ?? 'all') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </form>
                                <button type="button" @click="linkEditModal = {{ $link->id }}" class="text-orange-400 hover:text-orange-300 text-sm">Edit</button>
                                <form action="{{ route('projects.links.destroy', [$project, $link]) }}" method="POST" class="inline" onsubmit="return confirm('Remove this link?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Remove</button>
                                </form>
                            </div>
                            @endif
                        </li>
                    @empty
                        <li class="theme-text-muted text-sm py-4">
                            No live links or APK downloads yet.
                            @if(!($isClient ?? false))
                            Click <strong class="text-orange-400">+ Add Live Link or APK</strong> above to add a URL (e.g. staging site) or upload an APK file. You can add as many as you need.
                            @endif
                        </li>
                    @endforelse
                </ul>
            </div>

            {{-- Tab: Activity — same height/scroll pattern as dashboard Recent Activity --}}
            <div x-show="activeTab === 'activity'" class="p-5 flex flex-col overflow-hidden" style="height: 268px;">
                <h2 class="font-semibold theme-text-primary shrink-0 mb-2">Activity</h2>
                <div class="flex-1 min-h-0 overflow-y-auto relative pl-1 pr-2 -mr-2">
                    {{-- Vertical line through node centers (w-8 column center = 4px + 16px) --}}
                    <div class="absolute left-5 top-2 bottom-2 w-px bg-gradient-to-b from-orange-500/50 via-slate-500/50 to-transparent rounded-full" aria-hidden="true"></div>
                    <ul class="relative space-y-0">
                        @forelse($activities as $activity)
                            <li class="flex gap-4 py-2 first:pt-0 group">
                                <div class="shrink-0 w-8 flex justify-center pt-0.5">
                                    <div class="w-7 h-7 rounded-full border-2 theme-border theme-bg-tertiary flex items-center justify-center ring-2 theme-border group-hover:ring-orange-500/50 transition-all z-10
                                        @switch(\App\Models\ProjectActivity::iconFor($activity->action_type))
                                            @case('payment') text-emerald-400 @break
                                            @case('task') text-orange-400 @break
                                            @case('bug') text-red-400 @break
                                            @case('document') text-amber-400 @break
                                            @case('note') text-violet-400 @break
                                            @case('link') text-orange-400 @break
                                            @case('expense') text-orange-400 @break
                                            @case('project') theme-text-secondary @break
                                            @case('invoice') text-indigo-400 @break
                                            @default theme-text-secondary
                                        @endswitch">
                                        @switch(\App\Models\ProjectActivity::iconFor($activity->action_type))
                                            @case('payment') <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2h-2m-4-1V7a2 2 0 012-2h2a2 2 0 012 2v1"/></svg> @break
                                            @case('task') <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg> @break
                                            @case('bug') <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> @break
                                            @case('document') <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> @break
                                            @case('note') <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg> @break
                                            @case('link') <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg> @break
                                            @case('expense') <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> @break
                                            @case('invoice') <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> @break
                                            @default <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        @endswitch
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1 pb-1 border-b theme-border/30 last:border-0">
                                    <p class="theme-text-secondary text-sm">@if(in_array($activity->action_type, ['payment_created', 'payment_marked_paid', 'expense_created', 'invoice_generated']))<span class="payment-amount">{{ $activity->description }}</span>@else{{ $activity->description }}@endif</p>
                                    <p class="theme-text-muted text-xs mt-1">{{ $activity->actor_name }} · {{ $activity->created_at->format('M j, Y g:i A') }}</p>
                                </div>
                            </li>
                        @empty
                            <p class="theme-text-muted text-sm py-4 pl-2">No activity yet.</p>
                        @endforelse
                    </ul>
                </div>
            </div>
            @endif

        </div>
        @endif

        <style>
        .visibility-toggle-wrap.is-checked .visibility-track { background-color: #EF8121; border-color: #EF8121; }
        .visibility-toggle-wrap.is-checked .visibility-knob { transform: translateX(0.875rem); }
        .visibility-toggle-wrap.is-checked .visibility-label { color: rgb(56 189 248); }
        /* Add-modal visibility toggle: explicit unchecked so it never sticks on "active" */
        .js-visibility-toggle-label .visibility-toggle-track { background-color: rgb(51 65 85); border-color: rgb(100 116 139); transition: background-color 0.2s, border-color 0.2s; }
        .js-visibility-toggle-label .visibility-toggle-knob { transform: translateX(0); transition: transform 0.2s ease-out; }
        .js-visibility-toggle-label.is-checked .visibility-toggle-track { background-color: #EF8121; border-color: #EF8121; }
        .js-visibility-toggle-label.is-checked .visibility-toggle-knob { transform: translateX(1.25rem); }
        </style>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            function syncVisibilityWrap(label, checked) {
                if (!label) return;
                if (checked) label.classList.add('is-checked'); else label.classList.remove('is-checked');
                var lbl = label.querySelector('.visibility-label');
                if (lbl) lbl.textContent = checked ? 'Public' : 'Private';
            }
            // Add modals: when label (track/text) is clicked, toggle checkbox and sync visual immediately
            document.querySelectorAll('.js-visibility-toggle-label').forEach(function(label) {
                label.addEventListener('click', function(e) {
                    var cb = label.querySelector('.expense-visibility-toggle[data-hidden-id], .note-visibility-toggle[data-hidden-id]');
                    if (!cb) return;
                    if (e.target === cb) return;
                    e.preventDefault();
                    cb.checked = !cb.checked;
                    cb.dispatchEvent(new Event('change', { bubbles: true }));
                    syncAddModalVisibility(cb);
                });
            });
            // Add modals: sync hidden input, label text, and visual state (is-checked) when toggle changes
            function syncAddModalVisibility(checkbox) {
                var labelWrap = checkbox.closest('.js-visibility-toggle-label');
                if (labelWrap) {
                    if (checkbox.checked) labelWrap.classList.add('is-checked'); else labelWrap.classList.remove('is-checked');
                }
            }
            document.querySelectorAll('.expense-visibility-toggle[data-hidden-id]').forEach(function(cb) {
                cb.addEventListener('change', function() {
                    var hiddenId = this.getAttribute('data-hidden-id');
                    var labelId = this.getAttribute('data-label-id');
                    var hidden = hiddenId ? document.getElementById(hiddenId) : null;
                    var labelEl = labelId ? document.getElementById(labelId) : null;
                    if (hidden) hidden.value = this.checked ? '1' : '0';
                    if (labelEl) labelEl.textContent = this.checked ? 'Public (anyone can see)' : 'Private (admin only)';
                    syncAddModalVisibility(this);
                });
            });
            document.querySelectorAll('.note-visibility-toggle[data-hidden-id]').forEach(function(cb) {
                cb.addEventListener('change', function() {
                    var hiddenId = this.getAttribute('data-hidden-id');
                    var labelId = this.getAttribute('data-label-id');
                    var pubVal = this.getAttribute('data-value-public') || 'client';
                    var privVal = this.getAttribute('data-value-private') || 'internal';
                    var hidden = hiddenId ? document.getElementById(hiddenId) : null;
                    var labelEl = labelId ? document.getElementById(labelId) : null;
                    if (hidden) hidden.value = this.checked ? pubVal : privVal;
                    if (labelEl) labelEl.textContent = this.checked ? 'Public (anyone can see)' : 'Private (admin only)';
                    syncAddModalVisibility(this);
                });
            });
            // Edit forms (list row): update hidden and submit
            document.querySelectorAll('.expense-visibility-toggle[data-form-id], .document-visibility-toggle[data-form-id], .link-visibility-toggle[data-form-id]').forEach(function(cb) {
                cb.addEventListener('change', function() {
                    var wrap = this.closest('.visibility-toggle-wrap');
                    var inputId = this.getAttribute('data-input-id');
                    var formId = this.getAttribute('data-form-id');
                    if (inputId && formId) {
                        var input = document.getElementById(inputId);
                        if (input) input.value = this.checked ? '1' : '0';
                        syncVisibilityWrap(wrap, this.checked);
                        setTimeout(function() { document.getElementById(formId).submit(); }, 120);
                    }
                });
            });
            document.querySelectorAll('.note-visibility-toggle[data-form-id]').forEach(function(cb) {
                cb.addEventListener('change', function() {
                    var wrap = this.closest('.visibility-toggle-wrap');
                    var valueInputId = this.getAttribute('data-value-input-id');
                    var formId = this.getAttribute('data-form-id');
                    if (valueInputId && formId) {
                        var input = document.getElementById(valueInputId);
                        if (input) input.value = this.checked ? 'client' : 'internal';
                        syncVisibilityWrap(wrap, this.checked);
                        setTimeout(function() { document.getElementById(formId).submit(); }, 120);
                    }
                });
            });
            document.querySelectorAll('.copy-payment-link').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var link = this.getAttribute('data-payment-link');
                    if (link && navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(link).then(function() {
                            var t = btn.textContent; btn.textContent = 'Copied!'; setTimeout(function() { btn.textContent = t; }, 1500);
                        });
                    }
                });
            });
        });
        </script>

        @if(!($isClient ?? false))
        @include('projects.partials.modal-payout')
        @endif
        @include('projects.partials.modal-payment')
        @if(!($isClient ?? false))
        @include('projects.partials.modal-payment-edit')
        @endif
        @include('projects.partials.modal-expense')
        @if(!($isClient ?? false))
        @include('projects.partials.modal-expense-edit')
        @endif
        @include('projects.partials.modal-document')
        @if(!($isClient ?? false))
        @include('projects.partials.modal-contract')
        @endif
        @include('projects.partials.modal-task')
        @include('projects.partials.modal-bug')
        @include('projects.partials.modal-task-edit')
        @include('projects.partials.modal-bug-edit')
        @include('projects.partials.modal-note')
        @include('projects.partials.modal-note-edit')
        @include('projects.partials.modal-link')
        @include('projects.partials.modal-link-edit')
    </div>
</x-app-layout>
