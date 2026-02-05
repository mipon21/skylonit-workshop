<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-white">Dashboard</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 max-md:grid-cols-1 max-md:gap-3">
            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl p-5 shadow-lg hover:shadow-xl transition-shadow max-md:p-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-sky-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-slate-400 text-sm font-medium">{{ $isClient ?? false ? 'Total Contract Amount' : 'Total Revenue' }}</span>
                </div>
                <p class="text-2xl font-bold text-white">৳ {{ number_format($isClient ?? false ? $totalContractAmount : $totalRevenue, 0) }}</p>
                <p class="text-slate-500 text-xs mt-1">{{ $isClient ?? false ? 'Your projects' : 'Gross contract amount' }}</p>
            </div>

            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl p-5 shadow-lg hover:shadow-xl transition-shadow max-md:p-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-slate-400 text-sm font-medium">{{ $isClient ?? false ? 'Total Paid' : 'Total Profit' }}</span>
                </div>
                <p class="text-2xl font-bold text-emerald-400">৳ {{ number_format($isClient ?? false ? ($totalPaid ?? 0) : $totalProfit, 0) }}</p>
                <p class="text-slate-500 text-xs mt-1">{{ $isClient ?? false ? 'Received' : 'Realized from completed payments' }}</p>
            </div>

            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl p-5 shadow-lg hover:shadow-xl transition-shadow max-md:p-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-slate-400 text-sm font-medium">Total Due</span>
                </div>
                <p class="text-2xl font-bold text-amber-400">৳ {{ number_format($totalDue, 0) }}</p>
                <p class="text-slate-500 text-xs mt-1">{{ $isClient ?? false ? 'Outstanding' : 'Unpaid from clients' }}</p>
            </div>

            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl p-5 shadow-lg hover:shadow-xl transition-shadow max-md:p-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-cyan-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    </div>
                    <span class="text-slate-400 text-sm font-medium">Active Projects</span>
                </div>
                <p class="text-2xl font-bold text-white">{{ $activeProjects }}</p>
                <p class="text-slate-500 text-xs mt-1">Pending + Running</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 max-md:grid-cols-1 max-md:gap-3">
            <div class="bg-slate-800/60 backdrop-blur border border-slate-700/50 rounded-2xl p-4 shadow hover:shadow-lg transition-shadow max-md:p-3">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-9 h-9 rounded-lg bg-red-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <span class="text-slate-400 text-sm font-medium">Open Bugs</span>
                </div>
                <p class="text-xl font-bold text-white">{{ $openBugs }}</p>
                <p class="text-slate-500 text-xs mt-0.5">Open + In progress</p>
            </div>
            <div class="bg-slate-800/60 backdrop-blur border border-slate-700/50 rounded-2xl p-4 shadow hover:shadow-lg transition-shadow">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-9 h-9 rounded-lg bg-amber-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <span class="text-slate-400 text-sm font-medium">Active Tasks</span>
                </div>
                <p class="text-xl font-bold text-white">{{ $activeTasks }}</p>
                <p class="text-slate-500 text-xs mt-0.5">To do + Doing</p>
            </div>
            <div class="bg-slate-800/60 backdrop-blur border border-slate-700/50 rounded-2xl p-4 shadow hover:shadow-lg transition-shadow">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-9 h-9 rounded-lg bg-sky-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <span class="text-slate-400 text-sm font-medium">Documents</span>
                </div>
                <p class="text-xl font-bold text-white">{{ $documentsCount }}</p>
                <p class="text-slate-500 text-xs mt-0.5">Uploaded total</p>
            </div>
            <div class="bg-slate-800/60 backdrop-blur border border-slate-700/50 rounded-2xl p-4 shadow hover:shadow-lg transition-shadow">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-9 h-9 rounded-lg bg-slate-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <span class="text-slate-400 text-sm font-medium">Total Notes</span>
                </div>
                <p class="text-xl font-bold text-white">{{ $notesCount }}</p>
                <p class="text-slate-500 text-xs mt-0.5">Project notes</p>
            </div>
        </div>

        {{-- Calendar & Chart: equal width on desktop; full-width stack on mobile --}}
        <div class="grid grid-cols-2 max-md:grid-cols-1 gap-4 items-stretch">
            {{-- Calendar: 7-column grid with date notes --}}
            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-xl p-3 shadow-lg flex flex-col min-w-0 max-md:w-full" style="height: 268px;" x-data="dashboardCalendar()" x-init="init()">
                <div class="flex items-center justify-between mb-2 shrink-0">
                    <h2 class="text-slate-300 font-semibold text-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Calendar
                    </h2>
                    <div class="flex items-center gap-0.5">
                        <button type="button" @click="prevMonth(); fetchNotes();" class="p-1.5 rounded-lg hover:bg-slate-700/50 text-slate-400 hover:text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <span class="text-white font-medium text-xs min-w-[4.5rem] text-center" x-text="monthLabel"></span>
                        <button type="button" @click="nextMonth(); fetchNotes();" class="p-1.5 rounded-lg hover:bg-slate-700/50 text-slate-400 hover:text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
                <div class="border border-slate-600/50 rounded-lg overflow-visible bg-slate-700/30 flex-1 min-h-0" style="display: grid; grid-template-columns: repeat(7, 1fr); grid-auto-rows: 1fr;">
                    <template x-for="day in weekDays" :key="day">
                        <span class="border-b border-r border-slate-600/50 bg-slate-800/80 text-slate-400 text-[9px] font-semibold uppercase py-1 flex items-center justify-center" style="min-width: 0;" x-text="day"></span>
                    </template>
                    <template x-for="(cell, i) in days" :key="i">
                        <span
                            :class="{
                                'bg-slate-800/40 border-b border-r border-slate-600/40': cell.empty,
                                'bg-sky-500 text-white font-bold border-2 border-sky-300 shadow-md shadow-sky-500/30 ring-2 ring-sky-400/50 ring-inset': !cell.empty && cell.isToday,
                                'bg-amber-500/25 text-amber-100 border-b border-r border-amber-400/50 cursor-pointer': !cell.empty && !cell.isToday && notesByDate[cell.dateKey],
                                'bg-slate-800/70 text-slate-200 border-b border-r border-slate-600/40 cursor-pointer': !cell.empty && !cell.isToday && !notesByDate[cell.dateKey],
                                'border-r-0': (i + 1) % 7 === 0
                            }"
                            class="flex flex-col items-center justify-center text-[10px] relative"
                            :style="getDateCellStyle(cell, i)"
                            x-text="cell.label"
                            :title="cell.isToday ? 'Today' : (cell.dateKey && notesByDate[cell.dateKey] ? 'Has note – click to view' : 'Click to add note')"
                            @mouseenter="!cell.empty && (hoveredDateIndex = i)"
                            @mouseleave="hoveredDateIndex = null"
                            @click="!cell.empty && openNoteModal(cell.dateKey)"
                        >
                            <template x-if="!cell.empty && notesByDate[cell.dateKey]">
                                <span class="absolute bottom-0.5 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-amber-400 ring-1 ring-amber-300/50" title="Has note"></span>
                            </template>
                        </span>
                    </template>
                </div>
                {{-- Calendar note modal (dark) --}}
                <div x-show="noteModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;" @keydown.escape.window="closeNoteModal()">
                    <div x-show="noteModalOpen" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="absolute inset-0 bg-slate-950/90" @click="closeNoteModal()"></div>
                    <div x-show="noteModalOpen" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative w-full max-w-md bg-slate-800 border border-slate-600 rounded-2xl shadow-xl shadow-black/30 p-5" @click.stop>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-white font-semibold" x-text="noteModalDateLabel"></h3>
                            <button type="button" @click="closeNoteModal()" class="p-1.5 rounded-lg hover:bg-slate-700 text-slate-400 hover:text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <template x-if="noteViewMode">
                            <div>
                                <p class="text-slate-300 text-sm whitespace-pre-wrap mb-4 min-h-[4rem]" x-text="noteFormBody || 'No note for this date.'"></p>
                                <div class="flex gap-2">
                                    <button type="button" @click="noteViewMode = false" class="px-3 py-2 rounded-xl bg-sky-500/20 text-sky-400 hover:bg-sky-500/30 text-sm font-medium">Edit</button>
                                    <button type="button" @click="deleteNote()" class="px-3 py-2 rounded-xl bg-red-500/20 text-red-400 hover:bg-red-500/30 text-sm font-medium">Delete</button>
                                    <button type="button" @click="closeNoteModal()" class="px-3 py-2 rounded-xl bg-slate-700 text-slate-300 hover:bg-slate-600 text-sm font-medium ml-auto">Close</button>
                                </div>
                            </div>
                        </template>
                        <template x-if="!noteViewMode">
                            <form @submit.prevent="saveNote()" class="space-y-4">
                                <textarea
                                    x-model="noteFormBody"
                                    rows="4"
                                    class="w-full rounded-xl border p-3 resize-y text-sm bg-slate-700 text-slate-200 border-slate-500 placeholder-slate-400 focus:ring-2 focus:ring-sky-500/50 focus:border-sky-500"
                                    placeholder="Add a note for this date..."
                                ></textarea>
                                <div class="flex gap-2">
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-sky-500 text-white hover:bg-sky-600 text-sm font-medium">Save</button>
                                    <button type="button" @click="notesByDate[noteSelectedDate] ? (noteViewMode = true, noteFormBody = notesByDate[noteSelectedDate] || '') : closeNoteModal()" class="px-3 py-2 rounded-xl bg-slate-700 text-slate-300 hover:bg-slate-600 text-sm font-medium">Cancel</button>
                                </div>
                            </form>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Overview pie chart: same height as calendar --}}
            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-xl p-3 shadow-lg flex flex-col min-w-0 max-md:w-full" style="height: 268px;">
                <h2 class="text-slate-300 font-semibold text-sm mb-2 shrink-0 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/></svg>
                    Overview
                </h2>
                @if (array_sum($overviewChart['values']) > 0)
                    <div class="flex items-center justify-center flex-1 min-h-0" style="width: 100%;">
                        <canvas id="dashboardOverviewChart" width="200" height="200" style="max-height: 200px; max-width: 100%;"></canvas>
                    </div>
                    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
                    <script>
                        (function() {
                            const ctx = document.getElementById('dashboardOverviewChart');
                            if (!ctx) return;
                            if (ctx.chart) ctx.chart.destroy();
                            ctx.chart = new Chart(ctx, {
                                type: 'doughnut',
                                data: {
                                    labels: @json($overviewChart['labels']),
                                    datasets: [{
                                        data: @json($overviewChart['values']),
                                        backgroundColor: [
                                            'rgba(248, 113, 113, 0.8)',
                                            'rgba(251, 191, 36, 0.8)',
                                            'rgba(56, 189, 248, 0.8)',
                                            'rgba(148, 163, 184, 0.8)'
                                        ],
                                        borderColor: ['#f87171', '#fbbf24', '#38bdf8', '#94a3b8'],
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: true,
                                    animation: false,
                                    animations: { resize: { duration: 0 } },
                                    plugins: {
                                        legend: { position: 'bottom', labels: { color: '#cbd5e1', padding: 8, font: { size: 10 } } }
                                    }
                                }
                            });
                        })();
                    </script>
                @else
                    <div class="flex items-center justify-center flex-1 min-h-0 rounded-lg bg-slate-700/30 border border-slate-600/50">
                        <p class="text-slate-500 text-xs">No overview data yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        window.__calendarNoteRoutes = {
            index: "{{ route('calendar-notes.index') }}",
            store: "{{ route('calendar-notes.store') }}",
            destroyBase: "{{ url('/dashboard/calendar-notes') }}"
        };
        function dashboardCalendar() {
            const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            let today = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            return {
                weekDays,
                month: today.getMonth(),
                year: today.getFullYear(),
                days: [],
                notesByDate: {},
                noteModalOpen: false,
                noteSelectedDate: null,
                noteFormBody: '',
                noteViewMode: false,
                hoveredDateIndex: null,
                get monthLabel() {
                    return monthNames[this.month] + ' ' + this.year;
                },
                get noteModalDateLabel() {
                    if (!this.noteSelectedDate) return '';
                    const [y, m, d] = this.noteSelectedDate.split('-');
                    return monthNames[parseInt(m, 10) - 1] + ' ' + d + ', ' + y;
                },
                getDateCellStyle(cell, i) {
                    const base = 'min-height: 1.25rem; min-width: 0; transition: border-color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease, background-color 0.15s ease;';
                    if (cell.empty) return base;
                    const hasNote = cell.dateKey && this.notesByDate[cell.dateKey] !== undefined;
                    const isHovered = this.hoveredDateIndex === i;
                    if (isHovered) {
                        if (cell.isToday) return base + ' border-color: rgb(186, 230, 253) !important; box-shadow: 0 6px 16px rgba(56, 189, 248, 0.45); transform: scale(1.05); z-index: 10;';
                        return base + ' border-color: rgb(56, 189, 248) !important; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.45); transform: scale(1.05); z-index: 10;';
                    }
                    if (hasNote && !cell.isToday) {
                        return base + ' background-color: rgba(245, 158, 11, 0.35) !important; border-color: rgba(251, 191, 36, 0.7) !important; color: rgb(254, 243, 199) !important;';
                    }
                    return base;
                },
                init() {
                    this.buildDays();
                    this.fetchNotes();
                },
                buildDays() {
                    const first = new Date(this.year, this.month, 1);
                    const last = new Date(this.year, this.month + 1, 0);
                    const startPad = first.getDay();
                    const totalDays = last.getDate();
                    const todayY = today.getFullYear();
                    const todayM = today.getMonth();
                    const todayD = today.getDate();
                    const year = this.year;
                    const month = this.month;
                    let out = [];
                    for (let i = 0; i < startPad; i++) out.push({ empty: true, label: '' });
                    for (let d = 1; d <= totalDays; d++) {
                        const isToday = year === todayY && month === todayM && d === todayD;
                        const dateKey = year + '-' + pad(month + 1) + '-' + pad(d);
                        out.push({ empty: false, label: d, isToday, dateKey });
                    }
                    this.days = out;
                },
                prevMonth() {
                    if (this.month === 0) { this.month = 11; this.year--; } else this.month++;
                    this.buildDays();
                },
                nextMonth() {
                    if (this.month === 11) { this.month = 0; this.year++; } else this.month++;
                    this.buildDays();
                },
                fetchNotes() {
                    const url = window.__calendarNoteRoutes.index + '?year=' + this.year + '&month=' + (this.month + 1);
                    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                        .then(r => r.json())
                        .then(data => {
                            const obj = data && typeof data === 'object' && !Array.isArray(data) ? data : {};
                            this.notesByDate = { ...obj };
                        })
                        .catch(() => {});
                },
                openNoteModal(dateKey) {
                    this.noteSelectedDate = dateKey;
                    this.noteFormBody = this.notesByDate[dateKey] || '';
                    this.noteViewMode = !!this.notesByDate[dateKey];
                    this.noteModalOpen = true;
                },
                closeNoteModal() {
                    this.noteModalOpen = false;
                    this.noteSelectedDate = null;
                },
                saveNote() {
                    const body = typeof this.noteFormBody === 'string' ? this.noteFormBody.trim() : '';
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const url = window.__calendarNoteRoutes?.store;
                    if (!url) return;
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token || '',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ note_date: this.noteSelectedDate, body: body })
                    })
                        .then(r => {
                            if (!r.ok) return r.json().then(j => Promise.reject(j));
                            return r.json();
                        })
                        .then(data => {
                            if (data && data.date !== undefined) {
                                this.notesByDate = { ...this.notesByDate, [data.date]: data.body };
                                this.noteFormBody = data.body;
                                this.noteViewMode = true;
                            }
                        })
                        .catch(err => {
                            const msg = err && (err.message || (err.errors && Object.values(err.errors).flat().join(' ')));
                            alert(msg || 'Could not save note. Try again.');
                        });
                },
                deleteNote() {
                    if (!confirm('Delete this note?')) return;
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const url = window.__calendarNoteRoutes.destroyBase + '/' + this.noteSelectedDate;
                    fetch(url, {
                        method: 'DELETE',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token || '', 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(() => {
                            const next = { ...this.notesByDate };
                            delete next[this.noteSelectedDate];
                            this.notesByDate = next;
                            this.closeNoteModal();
                        })
                        .catch(() => {});
                }
            };
        }
    </script>
</x-app-layout>
