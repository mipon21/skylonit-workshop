<x-app-layout>
    <x-slot name="title">{{ $project->project_name }}</x-slot>

    <div class="space-y-6" x-data="{
        activeTab: (() => { const h = window.location.hash.slice(1); return ['payments','expenses','client','documents','tasks','bugs','notes','links'].includes(h) ? h : 'payments'; })(),
        setTab(tab) { this.activeTab = tab; window.location.hash = tab; },
        paymentModal: false,
        expenseModal: false,
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
                    @if($project->exclude_from_overhead_profit)
                        <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-amber-500/20 text-amber-400 border border-amber-500/40" title="Developer 75% &amp; Sales 25% only; no Overhead/Profit">Dev &amp; Sales only</span>
                    @endif
                </div>
                <p class="text-slate-400 text-sm mt-0.5">{{ $project->client->name }} @if($project->project_code)· {{ $project->project_code }}@endif</p>
            </div>
            <div class="flex gap-2 items-center">
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

        {{-- Revenue pipeline: Contract → Expenses → Overhead → Sales → Developer → Profit --}}
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
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 items-start">
            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl p-4">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Contract</p>
                <p class="text-lg font-bold text-white mt-0.5">৳ {{ number_format($project->contract_amount, 0) }}</p>
            </div>
            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl p-4">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Expenses</p>
                <p class="text-lg font-bold text-white mt-0.5">− ৳ {{ number_format($project->expense_total, 0) }}</p>
            </div>
            <div class="bg-slate-800/80 backdrop-blur border border-sky-500/30 rounded-2xl p-4">
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
            <div role="button" tabindex="0" title="Click to edit payout" @click="payoutType = 'overhead'; payoutModal = true" @keydown.enter="payoutType = 'overhead'; payoutModal = true" class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl p-4 cursor-pointer hover:border-slate-600 transition group">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Overhead</p>
                <p class="text-lg font-bold text-white mt-0.5">৳ {{ number_format($project->realized_overhead, 0) }}</p>
                <p class="text-slate-500 text-xs mt-0.5">of ৳ {{ number_format($project->overhead, 0) }}</p>
                <p class="mt-2 pt-2 border-t border-slate-700/50">
                    <span class="inline-block px-3 py-1.5 rounded-md text-base font-medium border whitespace-nowrap {{ $payoutBadgeClass($payoutStatus('overhead')) }}">{{ \App\Models\ProjectPayout::statusLabel($payoutStatus('overhead')) }}</span>
                </p>
            </div>
            <div role="button" tabindex="0" title="Click to edit payout" @click="payoutType = 'sales'; payoutModal = true" @keydown.enter="payoutType = 'sales'; payoutModal = true" class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl p-4 cursor-pointer hover:border-slate-600 transition group">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Sales</p>
                <p class="text-lg font-bold text-white mt-0.5">৳ {{ number_format($project->realized_sales, 0) }}</p>
                <p class="text-slate-500 text-xs mt-0.5">of ৳ {{ number_format($project->sales, 0) }}</p>
                <p class="mt-2 pt-2 border-t border-slate-700/50">
                    <span class="inline-block px-3 py-1.5 rounded-md text-base font-medium border whitespace-nowrap {{ $payoutBadgeClass($payoutStatus('sales')) }}">{{ \App\Models\ProjectPayout::statusLabel($payoutStatus('sales')) }}</span>
                </p>
            </div>
            <div role="button" tabindex="0" title="Click to edit payout" @click="payoutType = 'developer'; payoutModal = true" @keydown.enter="payoutType = 'developer'; payoutModal = true" class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl p-4 cursor-pointer hover:border-slate-600 transition group">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Developer</p>
                <p class="text-lg font-bold text-white mt-0.5">৳ {{ number_format($project->realized_developer, 0) }}</p>
                <p class="text-slate-500 text-xs mt-0.5">of ৳ {{ number_format($project->developer, 0) }}</p>
                <p class="mt-2 pt-2 border-t border-slate-700/50">
                    <span class="inline-block px-3 py-1.5 rounded-md text-base font-medium border whitespace-nowrap {{ $payoutBadgeClass($payoutStatus('developer')) }}">{{ \App\Models\ProjectPayout::statusLabel($payoutStatus('developer')) }}</span>
                </p>
            </div>
        </div>
        @php
            $taskTodo = $project->tasks->where('status', 'todo')->count();
            $taskDoing = $project->tasks->where('status', 'doing')->count();
            $taskDone = $project->tasks->where('status', 'done')->count();
            $taskTotal = $project->tasks->count();
            $openBugs = $project->bugs->whereIn('status', ['open', 'in_progress'])->count();
        @endphp
        {{-- Side-by-side: Work Overview (left, fills space) | Profit card (right, fixed) --}}
        <div class="flex flex-row gap-4 mt-2 items-start">
            <div class="flex-1 min-w-0 h-[70px] rounded-2xl bg-slate-800/80 backdrop-blur border border-slate-700/50 p-2.5 overflow-hidden flex flex-col">
                <p class="text-slate-300 text-sm font-semibold uppercase tracking-wide mb-1 shrink-0 text-center">Work overview</p>
                <div class="flex-1 min-h-0 grid grid-cols-2 sm:grid-cols-4 gap-1.5 justify-items-start">
                    <div class="w-max max-w-[56px] rounded-xl bg-slate-800/80 backdrop-blur border border-slate-700/50 px-2 py-1 flex items-center justify-between gap-1.5 shadow-inner">
                        <span class="text-slate-400 text-[10px] font-medium">To do</span>
                        <span class="text-white text-xs font-bold tabular-nums">{{ $taskTodo }}</span>
                    </div>
                    <div class="w-max max-w-[56px] rounded-xl bg-slate-800/80 backdrop-blur border border-slate-700/50 px-2 py-1 flex items-center justify-between gap-1.5 shadow-inner">
                        <span class="text-slate-400 text-[10px] font-medium">Doing</span>
                        <span class="text-amber-400 text-xs font-bold tabular-nums">{{ $taskDoing }}</span>
                    </div>
                    <div class="w-max max-w-[56px] rounded-xl bg-slate-800/80 backdrop-blur border border-slate-700/50 px-2 py-1 flex items-center justify-between gap-1.5 shadow-inner">
                        <span class="text-slate-400 text-[10px] font-medium">Done</span>
                        <span class="text-emerald-400 text-xs font-bold tabular-nums">{{ $taskDone }}</span>
                    </div>
                    <div class="w-max max-w-[56px] rounded-xl bg-slate-800/80 backdrop-blur border border-slate-700/50 px-2 py-1 flex items-center justify-between gap-1.5 shadow-inner">
                        <span class="text-slate-400 text-[10px] font-medium">Total</span>
                        <span class="text-sky-400 text-xs font-bold tabular-nums">{{ $taskTotal }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5 mt-1 shrink-0 justify-items-start">
                    <div class="w-max max-w-[56px] rounded-xl bg-slate-800/80 backdrop-blur border border-slate-700/50 px-2 py-1 flex items-center justify-between gap-1.5 shadow-inner">
                        <span class="text-slate-400 text-[10px] font-medium">docs</span>
                        <span class="text-white text-xs font-bold tabular-nums">{{ $project->documents->count() }}</span>
                    </div>
                    <div class="w-max max-w-[56px] rounded-xl bg-slate-800/80 backdrop-blur border border-slate-700/50 px-2 py-1 flex items-center justify-between gap-1.5 shadow-inner">
                        <span class="text-slate-400 text-[10px] font-medium">notes</span>
                        <span class="text-white text-xs font-bold tabular-nums">{{ $project->projectNotes->count() }}</span>
                    </div>
                    <div class="w-max max-w-[56px] rounded-xl bg-slate-800/80 backdrop-blur border border-slate-700/50 px-2 py-1 flex items-center justify-between gap-1.5 shadow-inner">
                        <span class="text-slate-400 text-[10px] font-medium">bugs</span>
                        <span class="text-white text-xs font-bold tabular-nums">{{ $openBugs }}</span>
                    </div>
                    <div class="w-max max-w-[56px] rounded-xl bg-slate-800/80 backdrop-blur border border-slate-700/50 px-2 py-1 flex items-center justify-between gap-1.5 shadow-inner">
                        <span class="text-slate-400 text-[10px] font-medium">links</span>
                        <span class="text-white text-xs font-bold tabular-nums">{{ $project->projectLinks->count() }}</span>
                    </div>
                </div>
            </div>
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
        </div>
        <p class="text-slate-500 text-xs mt-1">Amounts above fill as completed payments are received ({{ number_format($project->realized_ratio * 100, 0) }}% realized).</p>

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
                <button @click="setTab('client')" :class="activeTab === 'client' ? 'bg-sky-500/20 text-sky-400 border-b-2 border-sky-500' : 'text-slate-400 hover:text-white'" class="px-5 py-4 font-medium text-sm whitespace-nowrap border-b-2 border-transparent">Client</button>
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
                    <button @click="paymentModal = true" class="px-3 py-1.5 rounded-lg bg-sky-500/20 text-sky-400 hover:bg-sky-500/30 text-sm font-medium">Add</button>
                </div>
                <ul class="space-y-3">
                    @forelse($project->payments as $payment)
                        <li class="flex items-center justify-between py-2 border-b border-slate-700/30 last:border-0">
                            <div class="min-w-0">
                                <span class="text-white font-medium">৳ {{ number_format($payment->amount, 0) }}</span>
                                @if($payment->payment_method)<span class="text-slate-400 text-sm ml-2">· {{ $payment->payment_method }}</span>@endif
                                @if($payment->note)<span class="text-slate-500 text-sm ml-2">({{ $payment->note }})</span>@endif
                                <div class="flex items-center gap-2 mt-1 flex-wrap">
                                    @if($payment->payment_date)<span class="text-slate-500 text-xs">{{ $payment->payment_date->format('M d, Y') }}</span>@endif
                                    <span @class([
                                        'px-2 py-0.5 rounded text-xs font-medium',
                                        'bg-sky-500/20 text-sky-400' => $payment->status === 'upcoming',
                                        'bg-amber-500/20 text-amber-400' => $payment->status === 'due',
                                        'bg-emerald-500/20 text-emerald-400' => $payment->status === 'completed',
                                    ])>{{ $payment->status === 'completed' ? 'Paid' : ucfirst($payment->status) }}</span>
                                </div>
                            </div>
                            <form action="{{ route('projects.payments.destroy', [$project, $payment]) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('Remove this payment?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Remove</button>
                            </form>
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
                    <button @click="expenseModal = true" class="px-3 py-1.5 rounded-lg bg-sky-500/20 text-sky-400 hover:bg-sky-500/30 text-sm font-medium">Add</button>
                </div>
                <ul class="space-y-3">
                    @forelse($project->expenses as $expense)
                        <li class="flex items-center justify-between py-2 border-b border-slate-700/30 last:border-0">
                            <div>
                                <span class="text-white font-medium">৳ {{ number_format($expense->amount, 0) }}</span>
                                @if($expense->note)<span class="text-slate-500 text-sm ml-2">— {{ Str::limit($expense->note, 30) }}</span>@endif
                            </div>
                            <form action="{{ route('projects.expenses.destroy', [$project, $expense]) }}" method="POST" class="inline" onsubmit="return confirm('Remove this expense?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Remove</button>
                            </form>
                        </li>
                    @empty
                        <li class="text-slate-500 text-sm">No expenses yet.</li>
                    @endforelse
                </ul>
                <div class="mt-4 pt-4 border-t border-slate-700/50 flex justify-between text-sm">
                    <span class="text-slate-400">Total expense</span>
                    <span class="font-semibold text-white">৳ {{ number_format($project->expense_total, 0) }}</span>
                </div>
            </div>

            {{-- Tab: Client --}}
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

            {{-- Tab: Documents --}}
            <div x-show="activeTab === 'documents'" class="p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-white">Documents</h2>
                    <button @click="documentModal = true" class="px-3 py-1.5 rounded-lg bg-sky-500/20 text-sky-400 hover:bg-sky-500/30 text-sm font-medium">Upload</button>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @forelse($project->documents as $doc)
                        <div class="bg-slate-800/80 border border-slate-700/50 rounded-xl p-4 flex items-center justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('projects.documents.view', [$project, $doc]) }}" target="_blank" rel="noopener noreferrer" class="font-medium text-white block hover:text-sky-400 transition break-words">{{ $doc->title }}</a>
                                <p class="text-slate-500 text-xs mt-0.5">
                                    <span class="text-slate-400 font-medium uppercase">{{ pathinfo($doc->file_path, PATHINFO_EXTENSION) }}</span>
                                    <span class="text-slate-600 mx-1">·</span>
                                    {{ $doc->uploaded_at?->format('M d, Y') }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <a href="{{ route('projects.documents.download', [$project, $doc]) }}" class="text-slate-400 hover:text-slate-300 text-sm">Download</a>
                                <form action="{{ route('projects.documents.destroy', [$project, $doc]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this document?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
                                </form>
                            </div>
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
                    <button @click="taskModal = true" class="px-3 py-1.5 rounded-lg bg-sky-500/20 text-sky-400 hover:bg-sky-500/30 text-sm font-medium">Add Task</button>
                </div>
                <div class="h-2 bg-slate-700 rounded-full overflow-hidden mb-6">
                    <div class="h-full bg-emerald-500 rounded-full transition-all" style="width: {{ $taskPct }}%"></div>
                </div>
                <div class="grid md:grid-cols-3 gap-4">
                    <div class="bg-slate-800/40 border border-slate-700/50 rounded-xl p-4">
                        <h3 class="text-amber-400 font-medium text-sm mb-3">To Do</h3>
                        <div class="space-y-3">
                            @foreach($project->tasks->where('status', 'todo') as $task)
                                @include('projects.partials.task-card', ['task' => $task, 'project' => $project])
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
                                @include('projects.partials.task-card', ['task' => $task, 'project' => $project])
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
                                @include('projects.partials.task-card', ['task' => $task, 'project' => $project])
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
                    <button @click="noteModal = true" class="px-3 py-1.5 rounded-lg bg-sky-500/20 text-sky-400 hover:bg-sky-500/30 text-sm font-medium">New Note</button>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
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
                            </div>
                            <div class="px-4 py-3 border-t border-slate-700/50 flex justify-end gap-2">
                                <button type="button" @click="noteEditModal = {{ $note->id }}" class="text-sky-400 hover:text-sky-300 text-sm">Edit</button>
                                <form action="{{ route('projects.notes.destroy', [$project, $note]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this note?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
                                </form>
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
                    <button @click="linkModal = true" class="px-3 py-1.5 rounded-lg bg-sky-500/20 text-sky-400 hover:bg-sky-500/30 text-sm font-medium">Add Link</button>
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
                            <div class="flex items-center gap-2 shrink-0">
                                <button type="button" @click="linkEditModal = {{ $link->id }}" class="text-sky-400 hover:text-sky-300 text-sm">Edit</button>
                                <form action="{{ route('projects.links.destroy', [$project, $link]) }}" method="POST" class="inline" onsubmit="return confirm('Remove this link?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Remove</button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="text-slate-500 text-sm">No links yet. Add one to store URLs and optional login credentials.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        @include('projects.partials.modal-payout')
        @include('projects.partials.modal-payment')
        @include('projects.partials.modal-expense')
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
