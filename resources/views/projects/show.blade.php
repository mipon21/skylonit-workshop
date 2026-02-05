<x-app-layout>
    <x-slot name="title">{{ $project->project_name }}</x-slot>

    <div class="space-y-6" x-data="{
        activeTab: (() => { const h = window.location.hash.slice(1); return ['payments','expenses','client','documents','tasks','bugs','notes','links'].includes(h) ? h : 'payments'; })(),
        setTab(tab) { this.activeTab = tab; window.location.hash = tab; },
        paymentModal: false,
        paymentEditModal: null,
        expenseModal: false,
        expenseEditModal: null,
        documentModal: false,
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
        payoutType: null
    }">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('projects.index') }}" class="text-slate-400 hover:text-white text-sm">← Projects</a>
                <div class="flex flex-wrap items-center gap-2 mt-1">
                    <h1 class="text-2xl font-semibold text-white">{{ $project->project_name }}</h1>
                    @if($project->project_type)
                        <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-600/80 text-slate-300 border border-slate-500/50">{{ $project->project_type }}</span>
                    @endif
                    @if(!($isClient ?? false) && $project->exclude_from_overhead_profit)
                        <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-amber-500/20 text-amber-400 border border-amber-500/40" title="Developer 75% &amp; Sales 25% only; no Overhead/Profit">Dev &amp; Sales only</span>
                    @endif
                </div>
                <p class="text-slate-400 text-sm mt-0.5">{{ $project->client->name }} @if($project->project_code)· {{ $project->project_code }}@endif</p>
            </div>
            <div class="flex gap-2 items-center max-md:flex-wrap max-md:gap-2">
                @if(!($isClient ?? false))
                <a href="{{ route('projects.edit', $project) }}" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700 text-sm font-medium transition">Edit</a>
                <div class="relative" x-data="{ statusOpen: false, statusValue: '{{ $project->status }}' }" @click.outside="statusOpen = false">
                    <form x-ref="statusForm" action="{{ route('projects.status.update', $project) }}" method="post" class="inline">
                        @csrf
                        @method('patch')
                        <input type="hidden" name="status" :value="statusValue">
                        <button type="button" @click="statusOpen = ! statusOpen" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700 text-sm font-medium transition flex items-center gap-1.5">
                            <span x-text="statusValue || '{{ $project->status }}'">{{ $project->status }}</span>
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </form>
                    <div x-show="statusOpen" x-cloak x-transition class="absolute right-0 top-full mt-1 min-w-[10rem] rounded-xl bg-slate-800 border border-slate-600 shadow-xl py-1 z-50">
                        <button type="button" @click="statusValue = 'Pending'; statusOpen = false; $nextTick(() => $refs.statusForm.submit())" class="w-full text-left px-4 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white transition">Pending</button>
                        <button type="button" @click="statusValue = 'Running'; statusOpen = false; $nextTick(() => $refs.statusForm.submit())" class="w-full text-left px-4 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white transition">Running</button>
                        <button type="button" @click="statusValue = 'Complete'; statusOpen = false; $nextTick(() => $refs.statusForm.submit())" class="w-full text-left px-4 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white transition">Complete</button>
                        <button type="button" @click="statusValue = 'On Hold'; statusOpen = false; $nextTick(() => $refs.statusForm.submit())" class="w-full text-left px-4 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white transition">On Hold</button>
                    </div>
                </div>
                @else
                <span class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-400 text-sm">{{ $project->status }}</span>
                @endif
            </div>
        </div>

        @php
            $tasksTotal = $project->tasks_count ?? $project->tasks->count();
            $tasksDone = $project->tasks_done_count ?? $project->tasks->where('status', 'done')->count();
            $projectProgressPercent = $tasksTotal > 0 ? round(($tasksDone / $tasksTotal) * 100) : 0;
            $paymentsPercent = round($project->realized_ratio * 100);
        @endphp
        <style>
            @keyframes project-progress-fill { from { width: 0%; } to { width: {{ $projectProgressPercent }}%; } }
            @keyframes payments-progress-fill { from { width: 0%; } to { width: {{ $paymentsPercent }}%; } }
            .progress-fill-project { width: 0%; height: 13px; animation: project-progress-fill 0.8s ease-out 0.1s forwards; background: linear-gradient(to right, #0ea5e9, #22d3ee); }
            .progress-fill-payments { width: 0%; height: 13px; animation: payments-progress-fill 0.8s ease-out 0.1s forwards; background: linear-gradient(to right, #10b981, #34d399); }
        </style>
        {{-- Project Progress (before payment data) – animates on load --}}
        <div class="rounded-2xl bg-slate-800/60 border border-slate-700/50 p-4 shadow-inner">
            <div class="flex items-center justify-between mb-2.5">
                <span class="text-sm font-semibold text-slate-300 uppercase tracking-wide">Project progress</span>
                <span class="text-sm font-medium text-sky-400 tabular-nums">{{ $projectProgressPercent }}%</span>
            </div>
            <div class="relative w-full overflow-hidden rounded-full border border-slate-600/50 bg-slate-700/90" style="height: 13px;">
                <div class="progress-fill-project absolute top-0 left-0 bottom-0 rounded-full"></div>
            </div>
            <p class="text-slate-500 text-xs mt-1.5">{{ $tasksDone }} / {{ $tasksTotal }} tasks done</p>
        </div>

        {{-- Revenue pipeline: admin sees full; client sees Contract, Expenses, Total Paid, Due only --}}
        @if(!($isClient ?? false))
        @if($project->exclude_from_overhead_profit)
            <div class="mb-4 px-4 py-3 rounded-xl bg-amber-500/15 border border-amber-500/30 text-amber-400/90 text-sm flex items-center gap-2">
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
        <div class="max-md:overflow-x-auto max-md:flex max-md:gap-4 max-md:pb-2 max-md:snap-x max-md:snap-mandatory">
        <div class="grid grid-cols-2 sm:grid-cols-3 {{ ($isClient ?? false) ? 'lg:grid-cols-4' : 'lg:grid-cols-6' }} gap-4 items-start max-md:flex max-md:flex-nowrap max-md:min-w-0 max-md:gap-4">
            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl p-4 max-md:shrink-0 max-md:min-w-[200px] max-md:snap-start">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Contract {{ ($isClient ?? false) ? 'Amount' : '' }}</p>
                <p class="text-lg font-bold text-white mt-0.5">৳ {{ number_format($project->contract_amount, 0) }}</p>
            </div>
            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl p-4 max-md:shrink-0 max-md:min-w-[200px] max-md:snap-start">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Total Expenses</p>
                <p class="text-lg font-bold text-white mt-0.5">৳ {{ number_format(($isClient ?? false) ? $project->public_expense_total : $project->expense_total, 0) }}</p>
            </div>
            @if(!($isClient ?? false))
            <div class="bg-slate-800/80 backdrop-blur border border-sky-500/30 rounded-2xl p-4 max-md:shrink-0 max-md:min-w-[200px] max-md:snap-start">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Net base</p>
                <p class="text-lg font-bold {{ $project->is_net_base_negative ? 'text-amber-400' : 'text-white' }} mt-0.5">৳ {{ number_format($project->net_base, 0) }}</p>
            </div>
            @php
                $payoutStatus = fn($type) => $project->getPayoutFor($type)?->status ?? 'not_paid';
            @endphp
            @php
                $payoutBadgeClass = fn($status) => match($status) {
                    'paid' => 'bg-emerald-500/25 text-emerald-400 border-emerald-500/40',
                    'due', 'partial' => 'bg-amber-500/25 text-amber-400 border-amber-500/40',
                    'upcoming' => 'bg-sky-500/25 text-sky-400 border-sky-500/40',
                    default => 'bg-slate-500/25 text-slate-400 border-slate-500/40',
                };
            @endphp
            <div role="button" tabindex="0" title="Click to edit payout" @click="payoutType = 'overhead'; payoutModal = true" @keydown.enter="payoutType = 'overhead'; payoutModal = true" class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl p-4 cursor-pointer hover:border-slate-600 transition group max-md:shrink-0 max-md:min-w-[200px] max-md:snap-start">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Overhead</p>
                <p class="text-lg font-bold text-white mt-0.5">৳ {{ number_format($project->realized_overhead, 0) }}</p>
                <p class="text-slate-500 text-xs mt-0.5">of ৳ {{ number_format($project->overhead, 0) }}</p>
                <p class="mt-2 pt-2 border-t border-slate-700/50">
                    <span class="inline-block px-3 py-1.5 rounded-md text-base font-medium border whitespace-nowrap {{ $payoutBadgeClass($payoutStatus('overhead')) }}">{{ \App\Models\ProjectPayout::statusLabel($payoutStatus('overhead')) }}</span>
                </p>
            </div>
            <div role="button" tabindex="0" title="Click to edit payout" @click="payoutType = 'sales'; payoutModal = true" @keydown.enter="payoutType = 'sales'; payoutModal = true" class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl p-4 cursor-pointer hover:border-slate-600 transition group max-md:shrink-0 max-md:min-w-[200px] max-md:snap-start">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Sales</p>
                <p class="text-lg font-bold text-white mt-0.5">৳ {{ number_format($project->realized_sales, 0) }}</p>
                <p class="text-slate-500 text-xs mt-0.5">of ৳ {{ number_format($project->sales, 0) }}</p>
                <p class="mt-2 pt-2 border-t border-slate-700/50">
                    <span class="inline-block px-3 py-1.5 rounded-md text-base font-medium border whitespace-nowrap {{ $payoutBadgeClass($payoutStatus('sales')) }}">{{ \App\Models\ProjectPayout::statusLabel($payoutStatus('sales')) }}</span>
                </p>
            </div>
            <div role="button" tabindex="0" title="Click to edit payout" @click="payoutType = 'developer'; payoutModal = true" @keydown.enter="payoutType = 'developer'; payoutModal = true" class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl p-4 cursor-pointer hover:border-slate-600 transition group max-md:shrink-0 max-md:min-w-[200px] max-md:snap-start">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Developer</p>
                <p class="text-lg font-bold text-white mt-0.5">৳ {{ number_format($project->realized_developer, 0) }}</p>
                <p class="text-slate-500 text-xs mt-0.5">of ৳ {{ number_format($project->developer, 0) }}</p>
                <p class="mt-2 pt-2 border-t border-slate-700/50">
                    <span class="inline-block px-3 py-1.5 rounded-md text-base font-medium border whitespace-nowrap {{ $payoutBadgeClass($payoutStatus('developer')) }}">{{ \App\Models\ProjectPayout::statusLabel($payoutStatus('developer')) }}</span>
                </p>
            </div>
            @else
            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl p-4 max-md:shrink-0 max-md:min-w-[200px] max-md:snap-start">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Total Paid</p>
                <p class="text-lg font-bold text-emerald-400 mt-0.5">৳ {{ number_format($project->total_paid, 0) }}</p>
            </div>
            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl p-4 max-md:shrink-0 max-md:min-w-[200px] max-md:snap-start">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Due</p>
                <p class="text-lg font-bold {{ $project->due > 0 ? 'text-amber-400' : 'text-emerald-400' }} mt-0.5">৳ {{ number_format($project->due, 0) }}</p>
            </div>
            @endif
        </div>
        </div>
        @php
            $taskTodo = $project->tasks->where('status', 'todo')->count();
            $taskDoing = $project->tasks->where('status', 'doing')->count();
            $taskDone = $project->tasks->where('status', 'done')->count();
            $taskTotal = $project->tasks->count();
            $openBugs = $project->bugs->whereIn('status', ['open', 'in_progress'])->count();
        @endphp
        {{-- Side-by-side: Work Overview (left) | Profit card (right, admin only) --}}
        <div class="flex flex-row gap-4 mt-2 items-start max-md:flex-col max-md:gap-3 max-md:w-full">
            <div class="flex-1 min-w-0 min-h-[70px] rounded-2xl bg-slate-800/80 backdrop-blur border border-slate-700/50 p-2.5 overflow-visible md:min-w-[280px] md:h-auto flex flex-col max-md:h-auto max-md:min-h-0 max-md:w-full max-md:min-w-full">
                <p class="text-slate-300 text-sm font-semibold uppercase tracking-wide mb-1 shrink-0 text-center">Work overview</p>
                <div class="flex-1 min-h-0 min-w-0 grid grid-cols-2 sm:grid-cols-4 gap-1.5 justify-items-start md:justify-items-stretch max-md:grid-cols-4 max-md:justify-items-stretch max-md:w-full">
                    <div class="w-max max-w-[56px] md:w-full md:max-w-none md:min-w-0 rounded-xl bg-slate-800/80 backdrop-blur border border-slate-700/50 px-2 py-1 flex items-center justify-between gap-1.5 shadow-inner max-md:w-full max-md:min-w-0 max-md:max-w-none">
                        <span class="text-slate-400 text-[10px] font-medium">To do</span>
                        <span class="text-white text-xs font-bold tabular-nums">{{ $taskTodo }}</span>
                    </div>
                    <div class="w-max max-w-[56px] md:w-full md:max-w-none md:min-w-0 rounded-xl bg-slate-800/80 backdrop-blur border border-slate-700/50 px-2 py-1 flex items-center justify-between gap-1.5 shadow-inner max-md:w-full max-md:min-w-0 max-md:max-w-none">
                        <span class="text-slate-400 text-[10px] font-medium">Doing</span>
                        <span class="text-amber-400 text-xs font-bold tabular-nums">{{ $taskDoing }}</span>
                    </div>
                    <div class="w-max max-w-[56px] md:w-full md:max-w-none md:min-w-0 rounded-xl bg-slate-800/80 backdrop-blur border border-slate-700/50 px-2 py-1 flex items-center justify-between gap-1.5 shadow-inner max-md:w-full max-md:min-w-0 max-md:max-w-none">
                        <span class="text-slate-400 text-[10px] font-medium">Done</span>
                        <span class="text-emerald-400 text-xs font-bold tabular-nums">{{ $taskDone }}</span>
                    </div>
                    <div class="w-max max-w-[56px] md:w-full md:max-w-none md:min-w-0 rounded-xl bg-slate-800/80 backdrop-blur border border-slate-700/50 px-2 py-1 flex items-center justify-between gap-1.5 shadow-inner max-md:w-full max-md:min-w-0 max-md:max-w-none">
                        <span class="text-slate-400 text-[10px] font-medium">Total</span>
                        <span class="text-sky-400 text-xs font-bold tabular-nums">{{ $taskTotal }}</span>
                    </div>
                </div>
                <div class="min-w-0 grid grid-cols-2 sm:grid-cols-4 gap-1.5 mt-1 shrink-0 justify-items-start md:justify-items-stretch max-md:grid-cols-4 max-md:justify-items-stretch max-md:w-full">
                    <div class="w-max max-w-[56px] md:w-full md:max-w-none md:min-w-0 rounded-xl bg-slate-800/80 backdrop-blur border border-slate-700/50 px-2 py-1 flex items-center justify-between gap-1.5 shadow-inner max-md:w-full max-md:min-w-0 max-md:max-w-none">
                        <span class="text-slate-400 text-[10px] font-medium">docs</span>
                        <span class="text-white text-xs font-bold tabular-nums">{{ $project->documents->count() }}</span>
                    </div>
                    <div class="w-max max-w-[56px] md:w-full md:max-w-none md:min-w-0 rounded-xl bg-slate-800/80 backdrop-blur border border-slate-700/50 px-2 py-1 flex items-center justify-between gap-1.5 shadow-inner max-md:w-full max-md:min-w-0 max-md:max-w-none">
                        <span class="text-slate-400 text-[10px] font-medium">notes</span>
                        <span class="text-white text-xs font-bold tabular-nums">{{ $project->projectNotes->count() }}</span>
                    </div>
                    <div class="w-max max-w-[56px] md:w-full md:max-w-none md:min-w-0 rounded-xl bg-slate-800/80 backdrop-blur border border-slate-700/50 px-2 py-1 flex items-center justify-between gap-1.5 shadow-inner max-md:w-full max-md:min-w-0 max-md:max-w-none">
                        <span class="text-slate-400 text-[10px] font-medium">bugs</span>
                        <span class="text-white text-xs font-bold tabular-nums">{{ $openBugs }}</span>
                    </div>
                    <div class="w-max max-w-[56px] md:w-full md:max-w-none md:min-w-0 rounded-xl bg-slate-800/80 backdrop-blur border border-slate-700/50 px-2 py-1 flex items-center justify-between gap-1.5 shadow-inner max-md:w-full max-md:min-w-0 max-md:max-w-none">
                        <span class="text-slate-400 text-[10px] font-medium">links</span>
                        <span class="text-white text-xs font-bold tabular-nums">{{ $project->projectLinks->count() }}</span>
                    </div>
                </div>
            </div>
            @if(!($isClient ?? false))
            <div class="flex shrink-0">
                <div role="button" tabindex="0" title="Click to edit payout" @click="payoutType = 'profit'; payoutModal = true" @keydown.enter="payoutType = 'profit'; payoutModal = true" class="bg-slate-800/80 backdrop-blur border border-emerald-500/30 rounded-2xl p-4 shadow-[0_0_20px_-5px_rgba(16,185,129,0.2)] min-w-[180px] text-right cursor-pointer hover:border-emerald-500/50 transition group">
                    <p class="text-emerald-400/90 text-lg font-semibold uppercase tracking-wide">Profit</p>
                    <p class="text-4xl font-bold text-emerald-400 mt-0.5">৳ {{ number_format($project->realized_profit, 0) }}</p>
                    <p class="text-slate-500 text-lg mt-0.5">of ৳ {{ number_format($project->profit, 0) }}</p>
                    <p class="mt-2 pt-2 border-t border-slate-700/50">
                        <span class="inline-block px-3 py-1.5 rounded-md text-base font-medium border whitespace-nowrap {{ $payoutBadgeClass($payoutStatus('profit')) }}">{{ \App\Models\ProjectPayout::statusLabel($payoutStatus('profit')) }}</span>
                    </p>
                </div>
            </div>
            @endif
        </div>
        @if(!($isClient ?? false))
        <p class="text-slate-500 text-xs mt-1">Amounts above fill as completed payments are received ({{ number_format($project->realized_ratio * 100, 0) }}% realized).</p>
        @endif

        {{-- Payments progress (below payment data) – animates on load --}}
        <div class="rounded-2xl bg-slate-800/60 border border-slate-700/50 p-4 shadow-inner">
            <div class="flex items-center justify-between mb-2.5">
                <span class="text-sm font-semibold text-slate-300 uppercase tracking-wide">Payments progress</span>
                <span class="text-sm font-medium text-emerald-400 tabular-nums">{{ $paymentsPercent }}%</span>
            </div>
            <div class="relative w-full overflow-hidden rounded-full border border-slate-600/50 bg-slate-700/90" style="height: 13px;">
                <div class="progress-fill-payments absolute top-0 left-0 bottom-0 rounded-full"></div>
            </div>
            <p class="text-slate-500 text-xs mt-1.5">৳ {{ number_format($project->total_paid, 0) }} / ৳ {{ number_format($project->contract_amount, 0) }} received</p>
        </div>

        {{-- Tabs --}}
        <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl overflow-hidden">
            <div class="flex border-b border-slate-700/50 overflow-x-auto">
                <button @click="setTab('payments')" :class="activeTab === 'payments' ? 'bg-sky-500/20 text-sky-400 border-b-2 border-sky-500' : 'text-slate-400 hover:text-white'" class="px-5 py-4 font-medium text-sm whitespace-nowrap border-b-2 border-transparent">Payments</button>
                <button @click="setTab('expenses')" :class="activeTab === 'expenses' ? 'bg-sky-500/20 text-sky-400 border-b-2 border-sky-500' : 'text-slate-400 hover:text-white'" class="px-5 py-4 font-medium text-sm whitespace-nowrap border-b-2 border-transparent">Expenses</button>
                @if(!($isClient ?? false))
                <button @click="setTab('client')" :class="activeTab === 'client' ? 'bg-sky-500/20 text-sky-400 border-b-2 border-sky-500' : 'text-slate-400 hover:text-white'" class="px-5 py-4 font-medium text-sm whitespace-nowrap border-b-2 border-transparent">Client</button>
                @endif
                <button @click="setTab('documents')" :class="activeTab === 'documents' ? 'bg-sky-500/20 text-sky-400 border-b-2 border-sky-500' : 'text-slate-400 hover:text-white'" class="px-5 py-4 font-medium text-sm whitespace-nowrap border-b-2 border-transparent">Documents</button>
                <button @click="setTab('tasks')" :class="activeTab === 'tasks' ? 'bg-sky-500/20 text-sky-400 border-b-2 border-sky-500' : 'text-slate-400 hover:text-white'" class="px-5 py-4 font-medium text-sm whitespace-nowrap border-b-2 border-transparent">Tasks</button>
                <button @click="setTab('bugs')" :class="activeTab === 'bugs' ? 'bg-sky-500/20 text-sky-400 border-b-2 border-sky-500' : 'text-slate-400 hover:text-white'" class="px-5 py-4 font-medium text-sm whitespace-nowrap border-b-2 border-transparent">Bugs</button>
                <button @click="setTab('notes')" :class="activeTab === 'notes' ? 'bg-sky-500/20 text-sky-400 border-b-2 border-sky-500' : 'text-slate-400 hover:text-white'" class="px-5 py-4 font-medium text-sm whitespace-nowrap border-b-2 border-transparent">Notes</button>
                <button @click="setTab('links')" :class="activeTab === 'links' ? 'bg-sky-500/20 text-sky-400 border-b-2 border-sky-500' : 'text-slate-400 hover:text-white'" class="px-5 py-4 font-medium text-sm whitespace-nowrap border-b-2 border-transparent">Links</button>
            </div>

            {{-- Tab: Payments --}}
            <div x-show="activeTab === 'payments'" class="p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-white">Payments</h2>
                    @if(!($isClient ?? false) && (!config('payment.lock_after_final') || !$project->hasFinalPayment()))
                    <button @click="paymentModal = true" class="px-3 py-1.5 rounded-lg bg-sky-500/20 text-sky-400 hover:bg-sky-500/30 text-sm font-medium">Add</button>
                    @endif
                </div>
                @if(session('warning'))
                    <div class="mb-4 px-4 py-3 rounded-xl bg-amber-500/20 border border-amber-500/30 text-amber-400 text-sm">
                        {{ session('warning') }}
                    </div>
                @endif
                <ul class="space-y-3">
                    @forelse($project->payments as $payment)
                        <li class="flex items-center justify-between py-2 border-b border-slate-700/30 last:border-0 max-md:flex-col max-md:items-stretch max-md:gap-2 max-md:pb-4">
                            <div class="min-w-0">
                                <span class="text-white font-medium">৳ {{ number_format($payment->amount, 0) }}</span>
                                @if($payment->payment_type)
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-indigo-500/20 text-indigo-400 ml-2">{{ ucfirst($payment->payment_type) }}</span>
                                @endif
                                @if($payment->payment_method)<span class="text-slate-400 text-sm ml-2">· {{ $payment->payment_method }}</span>@endif
                                @if($payment->note)<span class="text-slate-500 text-sm ml-2">({{ $payment->note }})</span>@endif
                                <div class="flex items-center gap-2 mt-1 flex-wrap">
                                    @if($payment->payment_date)<span class="text-slate-500 text-xs">{{ $payment->payment_date->format('M d, Y') }}</span>@endif
                                    <span @class([
                                        'px-2 py-0.5 rounded text-xs font-medium',
                                        'bg-amber-500/20 text-amber-400' => $payment->payment_status === 'DUE',
                                        'bg-emerald-500/20 text-emerald-400' => $payment->payment_status === 'PAID',
                                        'bg-sky-500/20 text-sky-400' => $payment->status === 'upcoming' && $payment->payment_status !== 'PAID' && $payment->payment_status !== 'DUE',
                                        'bg-slate-500/20 text-slate-400' => !in_array($payment->payment_status ?? null, ['DUE', 'PAID']),
                                    ])>{{ $payment->payment_status === 'PAID' ? 'Paid' : ($payment->payment_status === 'DUE' ? 'DUE' : ucfirst($payment->status ?? '—')) }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0 flex-wrap justify-end max-md:w-full max-md:flex-col max-md:items-stretch">
                                @if(!($isClient ?? false))
                                    @if($payment->payment_status === 'DUE' && $payment->payment_link)
                                        <button type="button" data-payment-link="{{ $payment->payment_link }}" class="copy-payment-link px-3 py-1.5 rounded-lg bg-sky-500/20 text-sky-400 hover:bg-sky-500/30 text-xs font-medium inline-flex items-center gap-1" title="Copy link">
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
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-sky-500/20 text-sky-400 hover:bg-sky-500/30 text-xs font-medium inline-flex items-center gap-1" title="Create UddoktaPay link for this payment">
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
                                @if($payment->invoice)
                                    <a href="{{ route('invoices.view', $payment->invoice) }}" target="_blank" rel="noopener" class="px-3 py-1.5 rounded-lg bg-sky-500/20 text-sky-400 hover:bg-sky-500/30 text-xs font-medium inline-flex items-center gap-1" title="Preview in browser">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        View
                                    </a>
                                    <a href="{{ route('invoices.download', $payment->invoice) }}" class="px-3 py-1.5 rounded-lg bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30 text-xs font-medium inline-flex items-center gap-1" title="Download PDF">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        Download
                                    </a>
                                @endif
                                @if(!($isClient ?? false) && (!config('payment.lock_after_final') || !$project->hasFinalPayment()))
                                    <button type="button" @click="paymentEditModal = {{ $payment->id }}" class="text-sky-400 hover:text-sky-300 text-sm">Edit</button>
                                    <form action="{{ route('projects.payments.destroy', [$project, $payment]) }}" method="POST" class="inline" onsubmit="return confirm('Remove this payment?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Remove</button>
                                    </form>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="text-slate-500 text-sm">No payments yet.</li>
                    @endforelse
                </ul>
                <div class="mt-4 pt-4 border-t border-slate-700/50 flex justify-between text-sm">
                    <span class="text-slate-400">Total paid</span>
                    <span class="font-semibold text-white">৳ {{ number_format($project->total_paid, 0) }}</span>
                </div>
                <div class="mt-1 flex justify-between text-sm">
                    <span class="text-slate-400">Due</span>
                    <span class="font-semibold {{ $project->due > 0 ? 'text-amber-400' : 'text-emerald-400' }}">৳ {{ number_format($project->due, 0) }}</span>
                </div>
            </div>

            {{-- Tab: Expenses --}}
            <div x-show="activeTab === 'expenses'" class="p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-white">Expenses</h2>
                    @if(!($isClient ?? false))
                    <button @click="expenseModal = true" class="px-3 py-1.5 rounded-lg bg-sky-500/20 text-sky-400 hover:bg-sky-500/30 text-sm font-medium">Add</button>
                    @endif
                </div>
                <ul class="space-y-3">
                    @forelse($project->expenses as $expense)
                        <li class="flex items-center justify-between gap-3 py-2 border-b border-slate-700/30 last:border-0 max-md:flex-col max-md:items-stretch max-md:gap-2 max-md:pb-4">
                            <div class="min-w-0 flex-1">
                                <span class="text-white font-medium">৳ {{ number_format($expense->amount, 0) }}</span>
                                @if($expense->note)<span class="text-slate-500 text-sm ml-2">— {{ Str::limit($expense->note, 30) }}</span>@endif
                            </div>
                            @if(!($isClient ?? false))
                            <div class="flex items-center gap-2 shrink-0">
                                <button type="button" @click="expenseEditModal = {{ $expense->id }}" class="text-sky-400 hover:text-sky-300 text-sm">Edit</button>
                                <form action="{{ route('projects.expenses.update', [$project, $expense]) }}" method="POST" class="inline-flex items-center gap-2" id="expense-visibility-{{ $expense->id }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_public" value="{{ $expense->is_public ? '1' : '0' }}" id="expense-is-public-input-{{ $expense->id }}">
                                    <label class="visibility-toggle-wrap relative inline-flex items-center cursor-pointer gap-2 {{ $expense->is_public ? 'is-checked' : '' }}" title="{{ $expense->is_public ? 'Public – click to make private' : 'Private – click to make public' }}">
                                        <input type="checkbox" {{ $expense->is_public ? 'checked' : '' }} class="sr-only expense-visibility-toggle" data-input-id="expense-is-public-input-{{ $expense->id }}" data-form-id="expense-visibility-{{ $expense->id }}">
                                        <span class="visibility-track relative block h-5 w-9 shrink-0 rounded-full border-2 border-slate-500 bg-slate-600 transition-colors duration-200" aria-hidden="true" style="min-width: 2.25rem; min-height: 1.25rem;"></span>
                                        <span class="visibility-knob absolute z-10 rounded-full border-2 border-slate-400 bg-white shadow-md transition-transform duration-200 ease-out pointer-events-none" aria-hidden="true" style="left: 0.2rem; top: 0.2rem; width: 0.75rem; height: 0.75rem;"></span>
                                        <span class="visibility-label text-xs font-medium whitespace-nowrap {{ $expense->is_public ? 'text-sky-400' : 'text-slate-400' }}">{{ $expense->is_public ? 'Public' : 'Private' }}</span>
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
                        <li class="text-slate-500 text-sm">No expenses yet.</li>
                    @endforelse
                </ul>
                <div class="mt-4 pt-4 border-t border-slate-700/50 flex justify-between text-sm">
                    <span class="text-slate-400">Total expense</span>
                    <span class="font-semibold text-white">৳ {{ number_format(($isClient ?? false) ? $project->public_expense_total : $project->expense_total, 0) }}</span>
                </div>
            </div>

            {{-- Tab: Client (admin only) --}}
            @if(!($isClient ?? false))
            @php $client = $project->client; @endphp
            <div x-show="activeTab === 'client'" class="p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="font-semibold text-white">Client</h2>
                        <p class="text-slate-400 text-sm mt-0.5">{{ $client->name }}</p>
                    </div>
                    <a href="{{ route('clients.show', $client) }}" class="px-3 py-1.5 rounded-lg bg-sky-500/20 text-sky-400 hover:bg-sky-500/30 text-sm font-medium">View client</a>
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-5">
                        <h3 class="text-sm font-medium text-slate-400 mb-3">Contact</h3>
                        <p class="text-slate-300">Phone: {{ $client->phone ?? '—' }}</p>
                        <p class="text-slate-300">Email: {{ $client->email ?? '—' }}</p>
                        @if($client->fb_link)<p class="text-sky-400 mt-1"><a href="{{ $client->fb_link }}" target="_blank" rel="noopener">Facebook</a></p>@endif
                    </div>
                    @if($client->address || $client->kyc)
                    <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-5">
                        <h3 class="text-sm font-medium text-slate-400 mb-3">Details</h3>
                        @if($client->address)<p class="text-slate-300">{{ $client->address }}</p>@endif
                        @if($client->kyc)<p class="text-slate-300 mt-1">KYC: {{ $client->kyc }}</p>@endif
                    </div>
                    @endif
                </div>
                @if(!$client->phone && !$client->email && !$client->fb_link && !$client->address && !$client->kyc)
                    <p class="text-slate-500 text-sm mt-4">No contact or details recorded for this client.</p>
                @endif
            </div>
            @endif

            {{-- Tab: Documents --}}
            <div x-show="activeTab === 'documents'" class="p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-white">Documents</h2>
                    <button @click="documentModal = true" class="px-3 py-1.5 rounded-lg bg-sky-500/20 text-sky-400 hover:bg-sky-500/30 text-sm font-medium">Upload</button>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 max-md:gap-3">
                    @forelse($project->documents as $doc)
                        <div class="bg-slate-800/80 border border-slate-700/50 rounded-xl p-4 grid gap-4" style="grid-template-columns: minmax(0, 1fr) auto;">
                            <div class="min-w-0 space-y-1 overflow-hidden">
                                <a href="{{ route('projects.documents.view', [$project, $doc]) }}" target="_blank" rel="noopener noreferrer" class="font-medium text-white hover:text-sky-400 transition block overflow-hidden max-w-full break-all line-clamp-2" title="{{ $doc->title }}">{{ $doc->title }}</a>
                                <p class="text-slate-500 text-xs flex flex-wrap items-center gap-x-1 gap-y-0.5">
                                    <span class="text-slate-400 font-medium uppercase">{{ pathinfo($doc->file_path, PATHINFO_EXTENSION) }}</span>
                                    <span class="text-slate-600">·</span>
                                    <span class="whitespace-nowrap">{{ $doc->uploaded_at?->format('M j, Y') }}</span>
                                    <span class="text-slate-600">·</span>
                                    <span class="text-slate-400 whitespace-nowrap">Uploaded by: {{ $doc->uploadedBy ? ucfirst($doc->uploadedBy->role) : '—' }}</span>
                                </p>
                            </div>
                            @if(!($isClient ?? false))
                            <div class="flex flex-col items-end gap-2 shrink-0 pl-3 border-l border-slate-600/80">
                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                    <a href="{{ route('projects.documents.download', [$project, $doc]) }}" class="px-3 py-1.5 rounded-lg bg-slate-700/80 hover:bg-slate-600 text-slate-200 text-sm font-medium text-center whitespace-nowrap transition">Download</a>
                                    <form action="{{ route('projects.documents.destroy', [$project, $doc]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this document?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full sm:w-auto px-3 py-1.5 rounded-lg bg-red-900/40 hover:bg-red-800/50 text-red-400 text-sm font-medium whitespace-nowrap transition border border-red-700/50">Delete</button>
                                    </form>
                                </div>
                                <form action="{{ route('projects.documents.update', [$project, $doc]) }}" method="POST" class="inline-flex items-center" id="document-visibility-{{ $doc->id }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_public" value="{{ $doc->is_public ? '1' : '0' }}" id="document-is-public-input-{{ $doc->id }}">
                                    <label class="visibility-toggle-wrap relative inline-flex items-center cursor-pointer gap-1.5 {{ $doc->is_public ? 'is-checked' : '' }}" title="{{ $doc->is_public ? 'Public – click to make private' : 'Private – click to make public' }}">
                                        <input type="checkbox" {{ $doc->is_public ? 'checked' : '' }} class="sr-only document-visibility-toggle" data-input-id="document-is-public-input-{{ $doc->id }}" data-form-id="document-visibility-{{ $doc->id }}">
                                        <span class="visibility-track relative block h-5 w-9 shrink-0 rounded-full border-2 border-slate-500 bg-slate-600 transition-colors duration-200" aria-hidden="true" style="min-width: 2.25rem; min-height: 1.25rem;"></span>
                                        <span class="visibility-knob absolute z-10 rounded-full border-2 border-slate-400 bg-white shadow-md transition-transform duration-200 ease-out pointer-events-none" aria-hidden="true" style="left: 0.2rem; top: 0.2rem; width: 0.75rem; height: 0.75rem;"></span>
                                        <span class="visibility-label text-xs font-medium whitespace-nowrap {{ $doc->is_public ? 'text-sky-400' : 'text-slate-400' }}">{{ $doc->is_public ? 'Public' : 'Private' }}</span>
                                    </label>
                                </form>
                            </div>
                            @else
                            <div class="shrink-0 pl-3 border-l border-slate-600/80">
                                <a href="{{ route('projects.documents.download', [$project, $doc]) }}" class="inline-block px-3 py-1.5 rounded-lg bg-slate-700/80 hover:bg-slate-600 text-slate-200 text-sm font-medium whitespace-nowrap transition">Download</a>
                            </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm col-span-full">No documents yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Tab: Tasks --}}
            <div x-show="activeTab === 'tasks'" class="p-5">
                @php
                    $taskTotal = $project->tasks->count();
                    $taskDone = $project->tasks->where('status', 'done')->count();
                    $taskPct = $taskTotal > 0 ? round(($taskDone / $taskTotal) * 100) : 0;
                @endphp
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-4">
                        <h2 class="font-semibold text-white">Tasks</h2>
                        <span class="text-slate-400 text-sm">{{ $taskDone }}/{{ $taskTotal }} done ({{ $taskPct }}%)</span>
                    </div>
                    @if(!($isClient ?? false))
                    <button @click="taskModal = true" class="px-3 py-1.5 rounded-lg bg-sky-500/20 text-sky-400 hover:bg-sky-500/30 text-sm font-medium">Add Task</button>
                    @endif
                </div>
                <div class="h-2 bg-slate-700 rounded-full overflow-hidden mb-6">
                    <div class="h-full bg-emerald-500 rounded-full transition-all" style="width: {{ $taskPct }}%"></div>
                </div>
                <div class="grid md:grid-cols-3 gap-4 max-md:grid-cols-1 max-md:gap-3">
                    <div class="bg-slate-800/40 border border-slate-700/50 rounded-xl p-4">
                        <h3 class="text-amber-400 font-medium text-sm mb-3">To Do</h3>
                        <div class="space-y-3">
                            @foreach($project->tasks->where('status', 'todo') as $task)
                                @include('projects.partials.task-card', ['task' => $task, 'project' => $project, 'isClient' => $isClient ?? false])
                            @endforeach
                            @if($project->tasks->where('status', 'todo')->isEmpty())
                                <p class="text-slate-500 text-sm">No tasks</p>
                            @endif
                        </div>
                    </div>
                    <div class="bg-slate-800/40 border border-slate-700/50 rounded-xl p-4">
                        <h3 class="text-amber-400 font-medium text-sm mb-3">Doing</h3>
                        <div class="space-y-3">
                            @foreach($project->tasks->where('status', 'doing') as $task)
                                @include('projects.partials.task-card', ['task' => $task, 'project' => $project, 'isClient' => $isClient ?? false])
                            @endforeach
                            @if($project->tasks->where('status', 'doing')->isEmpty())
                                <p class="text-slate-500 text-sm">No tasks</p>
                            @endif
                        </div>
                    </div>
                    <div class="bg-slate-800/40 border border-slate-700/50 rounded-xl p-4">
                        <h3 class="text-emerald-400 font-medium text-sm mb-3">Done</h3>
                        <div class="space-y-3">
                            @foreach($project->tasks->where('status', 'done') as $task)
                                @include('projects.partials.task-card', ['task' => $task, 'project' => $project, 'isClient' => $isClient ?? false])
                            @endforeach
                            @if($project->tasks->where('status', 'done')->isEmpty())
                                <p class="text-slate-500 text-sm">No tasks</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab: Bugs --}}
            <div x-show="activeTab === 'bugs'" class="p-5" x-data="{ bugFilter: 'all' }">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <h2 class="font-semibold text-white">Bugs</h2>
                        <button @click="bugFilter = 'all'" :class="bugFilter === 'all' ? 'bg-slate-600 text-white' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-lg text-sm">All</button>
                        <button @click="bugFilter = 'open'" :class="bugFilter === 'open' ? 'bg-red-500/20 text-red-400' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-lg text-sm">Open</button>
                        <button @click="bugFilter = 'in_progress'" :class="bugFilter === 'in_progress' ? 'bg-amber-500/20 text-amber-400' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-lg text-sm">In Progress</button>
                        <button @click="bugFilter = 'resolved'" :class="bugFilter === 'resolved' ? 'bg-emerald-500/20 text-emerald-400' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-lg text-sm">Resolved</button>
                    </div>
                    <button @click="bugModal = true" class="px-3 py-1.5 rounded-lg bg-sky-500/20 text-sky-400 hover:bg-sky-500/30 text-sm font-medium">Report Bug</button>
                </div>
                <div class="space-y-3">
                    @foreach($project->bugs as $bug)
                        <div class="bg-slate-800/80 border border-slate-700/50 rounded-xl overflow-hidden transition-all duration-200 hover:border-slate-600"
                             x-show="bugFilter === 'all' || bugFilter === '{{ $bug->status }}'"
                             :class="{ 'ring-1 ring-sky-500/30': expandedBugId == {{ $bug->id }} }">
                            <button type="button" @click="expandedBugId = expandedBugId == {{ $bug->id }} ? null : {{ $bug->id }}" class="w-full text-left p-4">
                                <p class="font-medium text-white">{{ $bug->title }}</p>
                                @if($bug->description)<p class="text-slate-500 text-sm mt-1 line-clamp-2">{{ Str::limit($bug->description, 80) }}</p>@endif
                                <div class="flex flex-wrap gap-2 mt-2 items-center">
                                    <span @class([
                                        'px-2 py-0.5 rounded text-xs font-medium',
                                        'bg-red-500/20 text-red-400' => $bug->severity === 'critical',
                                        'bg-amber-500/20 text-amber-400' => $bug->severity === 'major',
                                        'bg-slate-500/20 text-slate-400' => $bug->severity === 'minor',
                                    ])>{{ ucfirst($bug->severity) }}</span>
                                    @if($bug->attachment_path)
                                        <span class="text-sky-400 text-xs flex items-center gap-1">Attachment</span>
                                    @endif
                                </div>
                            </button>
                            <div x-show="expandedBugId == {{ $bug->id }}" x-transition class="px-4 pb-4 border-t border-slate-700/50">
                                <div class="pt-3 text-slate-300 text-sm whitespace-pre-wrap">{{ $bug->description ?: '—' }}</div>
                                @if($bug->attachment_path)
                                    <p class="mt-3">
                                        <a href="{{ route('projects.bugs.attachment', [$project, $bug]) }}" class="inline-flex items-center gap-1.5 text-sky-400 hover:text-sky-300 text-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                            Download attachment
                                        </a>
                                    </p>
                                @endif
                                @if(!($isClient ?? false))
                                <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
                                    <form action="{{ route('projects.bugs.update', [$project, $bug]) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="title" value="{{ $bug->title }}">
                                        <input type="hidden" name="description" value="{{ $bug->description }}">
                                        <input type="hidden" name="severity" value="{{ $bug->severity }}">
                                        <select name="status" onchange="this.form.submit()" class="rounded-lg bg-slate-900 border border-slate-600 text-white text-sm px-3 py-1.5 focus:ring-sky-500">
                                            <option value="open" {{ $bug->status === 'open' ? 'selected' : '' }}>Open</option>
                                            <option value="in_progress" {{ $bug->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                            <option value="resolved" {{ $bug->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                        </select>
                                    </form>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="bugEditModal = {{ $bug->id }}" class="text-sky-400 hover:text-sky-300 text-sm">Edit</button>
                                        <form action="{{ route('projects.bugs.destroy', [$project, $bug]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this bug?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
                                        </form>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                    @if($project->bugs->isEmpty())
                        <p class="text-slate-500 text-sm">No bugs reported.</p>
                    @endif
                </div>
            </div>

            {{-- Tab: Notes --}}
            <div x-show="activeTab === 'notes'" class="p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-white">Notes</h2>
                    @if(!($isClient ?? false))
                    <button @click="noteModal = true" class="px-3 py-1.5 rounded-lg bg-sky-500/20 text-sky-400 hover:bg-sky-500/30 text-sm font-medium">New Note</button>
                    @endif
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 max-md:grid-cols-1 max-md:gap-3">
                    @forelse($project->projectNotes as $note)
                        <div class="bg-slate-800/80 border border-slate-700/50 rounded-2xl overflow-hidden hover:border-slate-600 hover:shadow-lg transition-all duration-200"
                             :class="{ 'ring-1 ring-sky-500/30': expandedNoteId == {{ $note->id }} }">
                            <button type="button" @click="expandedNoteId = expandedNoteId == {{ $note->id }} ? null : {{ $note->id }}" class="w-full text-left p-4">
                                <p class="font-semibold text-white">{{ $note->title }}</p>
                                <p class="text-slate-500 text-xs mt-1">{{ $note->created_at->format('M d, Y') }}</p>
                                <p class="text-slate-400 text-sm mt-2 line-clamp-2">{{ Str::limit(strip_tags($note->body), 120) ?: 'No content' }}</p>
                                <span class="inline-flex items-center gap-1 mt-2 px-2 py-0.5 rounded text-xs {{ $note->visibility === 'client' ? 'bg-sky-500/20 text-sky-400' : 'bg-slate-600/50 text-slate-400' }}">{{ ucfirst($note->visibility) }}</span>
                            </button>
                            <div x-show="expandedNoteId == {{ $note->id }}" x-transition class="px-4 pb-4">
                                <div class="pt-2 border-t border-slate-700/50 text-slate-300 text-sm whitespace-pre-wrap">{{ $note->body ?: '—' }}</div>
                                @if(!($isClient ?? false))
                                <div class="px-4 py-3 border-t border-slate-700/50 flex flex-wrap items-center justify-between gap-2">
                                    <form action="{{ route('projects.notes.update', [$project, $note]) }}" method="POST" class="inline-flex items-center gap-2" id="note-visibility-form-{{ $note->id }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="title" value="{{ old('title', $note->title) }}">
                                        <input type="hidden" name="body" value="{{ old('body', $note->body ?? '') }}">
                                        <input type="hidden" name="visibility" id="note-visibility-value-{{ $note->id }}" value="{{ $note->visibility }}">
                                        <label class="visibility-toggle-wrap relative inline-flex items-center cursor-pointer gap-2 {{ $note->visibility === 'client' ? 'is-checked' : '' }}" title="{{ $note->visibility === 'client' ? 'Public – click to make private' : 'Private – click to make public' }}">
                                            <input type="checkbox" {{ $note->visibility === 'client' ? 'checked' : '' }} class="sr-only note-visibility-toggle" data-value-input-id="note-visibility-value-{{ $note->id }}" data-form-id="note-visibility-form-{{ $note->id }}">
                                            <span class="visibility-track relative block h-5 w-9 shrink-0 rounded-full border-2 border-slate-500 bg-slate-600 transition-colors duration-200" aria-hidden="true" style="min-width: 2.25rem; min-height: 1.25rem;"></span>
                                            <span class="visibility-knob absolute z-10 rounded-full border-2 border-slate-400 bg-white shadow-md transition-transform duration-200 ease-out pointer-events-none" aria-hidden="true" style="left: 0.2rem; top: 0.2rem; width: 0.75rem; height: 0.75rem;"></span>
                                            <span class="visibility-label text-xs font-medium whitespace-nowrap {{ $note->visibility === 'client' ? 'text-sky-400' : 'text-slate-400' }}">{{ $note->visibility === 'client' ? 'Public' : 'Private' }}</span>
                                        </label>
                                    </form>
                                    <div class="flex gap-2">
                                        <button type="button" @click="noteEditModal = {{ $note->id }}" class="text-sky-400 hover:text-sky-300 text-sm">Edit</button>
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
                        <p class="text-slate-500 text-sm col-span-full">No notes yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Tab: Links --}}
            <div x-show="activeTab === 'links'" class="p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-white">Links</h2>
                    @if(!($isClient ?? false))
                    <button @click="linkModal = true" class="px-3 py-1.5 rounded-lg bg-sky-500/20 text-sky-400 hover:bg-sky-500/30 text-sm font-medium">Add Link</button>
                    @endif
                </div>
                <p class="text-slate-500 text-sm mb-4">Store project URLs with optional login credentials.</p>
                <ul class="space-y-4">
                    @forelse($project->projectLinks as $link)
                        <li class="bg-slate-800/80 border border-slate-700/50 rounded-xl p-4 flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-white">{{ $link->label }}</p>
                                <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="text-sky-400 hover:text-sky-300 text-sm mt-1 break-all">{{ $link->url }}</a>
                                @if($link->login_username || $link->login_password)
                                    <div class="mt-2 pt-2 border-t border-slate-700/50 text-slate-400 text-sm space-y-1">
                                        @if($link->login_username)
                                            <p>Username: <span class="text-slate-300">{{ $link->login_username }}</span></p>
                                        @endif
                                        @if($link->login_password)
                                            <p>Password: <span class="text-slate-300">{{ $link->login_password }}</span></p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            @if(!($isClient ?? false))
                            <div class="flex items-center gap-2 shrink-0">
                                <form action="{{ route('projects.links.update', [$project, $link]) }}" method="POST" class="inline-flex items-center gap-2" id="link-visibility-{{ $link->id }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="label" value="{{ old('label', $link->label) }}">
                                    <input type="hidden" name="url" value="{{ old('url', $link->url) }}">
                                    <input type="hidden" name="login_username" value="{{ old('login_username', $link->login_username ?? '') }}">
                                    <input type="hidden" name="login_password" value="{{ old('login_password', $link->login_password ?? '') }}">
                                    <input type="hidden" name="is_public" value="{{ $link->is_public ? '1' : '0' }}" id="link-is-public-input-{{ $link->id }}">
                                    <label class="visibility-toggle-wrap relative inline-flex items-center cursor-pointer gap-2 {{ $link->is_public ? 'is-checked' : '' }}" title="{{ $link->is_public ? 'Public – click to make private' : 'Private – click to make public' }}">
                                        <input type="checkbox" {{ $link->is_public ? 'checked' : '' }} class="sr-only link-visibility-toggle" data-input-id="link-is-public-input-{{ $link->id }}" data-form-id="link-visibility-{{ $link->id }}">
                                        <span class="visibility-track relative block h-5 w-9 shrink-0 rounded-full border-2 border-slate-500 bg-slate-600 transition-colors duration-200" aria-hidden="true" style="min-width: 2.25rem; min-height: 1.25rem;"></span>
                                        <span class="visibility-knob absolute z-10 rounded-full border-2 border-slate-400 bg-white shadow-md transition-transform duration-200 ease-out pointer-events-none" aria-hidden="true" style="left: 0.2rem; top: 0.2rem; width: 0.75rem; height: 0.75rem;"></span>
                                        <span class="visibility-label text-xs font-medium whitespace-nowrap {{ $link->is_public ? 'text-sky-400' : 'text-slate-400' }}">{{ $link->is_public ? 'Public' : 'Private' }}</span>
                                    </label>
                                </form>
                                <button type="button" @click="linkEditModal = {{ $link->id }}" class="text-sky-400 hover:text-sky-300 text-sm">Edit</button>
                                <form action="{{ route('projects.links.destroy', [$project, $link]) }}" method="POST" class="inline" onsubmit="return confirm('Remove this link?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Remove</button>
                                </form>
                            </div>
                            @endif
                        </li>
                    @empty
                        <li class="text-slate-500 text-sm">No links yet. Add one to store URLs and optional login credentials.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <style>
        .visibility-toggle-wrap.is-checked .visibility-track { background-color: rgb(14 165 233); border-color: rgb(14 165 233); }
        .visibility-toggle-wrap.is-checked .visibility-knob { transform: translateX(0.875rem); }
        .visibility-toggle-wrap.is-checked .visibility-label { color: rgb(56 189 248); }
        /* Add-modal visibility toggle: explicit unchecked so it never sticks on "active" */
        .js-visibility-toggle-label .visibility-toggle-track { background-color: rgb(51 65 85); border-color: rgb(100 116 139); transition: background-color 0.2s, border-color 0.2s; }
        .js-visibility-toggle-label .visibility-toggle-knob { transform: translateX(0); transition: transform 0.2s ease-out; }
        .js-visibility-toggle-label.is-checked .visibility-toggle-track { background-color: rgb(14 165 233); border-color: rgb(14 165 233); }
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
