<x-guest-portal-layout :title="$project->project_name">
    <div class="space-y-6" x-data="{ activeTab: (() => { const h = window.location.hash.slice(1); return ['links','tasks','bugs'].includes(h) ? h : 'links'; })(), setTab(tab) { this.activeTab = tab; window.location.hash = tab; } }">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('guest.projects.index') }}" class="text-slate-400 hover:text-white text-sm">← Projects</a>
                <div class="flex flex-wrap items-center gap-2 mt-1">
                    <h1 class="text-2xl font-semibold text-white">{{ $project->project_name }}</h1>
                    @if($project->project_type)
                        <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-600/80 text-slate-300 border border-slate-500/50">{{ $project->project_type }}</span>
                    @endif
                </div>
                <p class="text-slate-400 text-sm mt-0.5">{{ $project->project_code ?: $project->formatted_id }}</p>
            </div>
            <span class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-400 text-sm">{{ $project->status }}</span>
        </div>

        @if($project->guest_description)
        <div class="bg-slate-800/60 backdrop-blur border border-slate-700/50 rounded-2xl overflow-hidden flex flex-col" style="height: 10rem;">
            <div class="p-5 overflow-y-auto flex-1 min-h-0 [scrollbar-width:thin] [scrollbar-color:rgba(100,116,139,0.6)_transparent]">
                <p class="text-slate-300 text-sm leading-relaxed whitespace-pre-wrap pr-2">{{ $project->guest_description }}</p>
            </div>
        </div>
        @endif

        {{-- Top cards: name, status, start, delivery only --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 max-md:grid-cols-2 max-md:gap-3">
            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl p-4">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Status</p>
                <p class="text-lg font-bold text-white mt-0.5">{{ $project->status }}</p>
            </div>
            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl p-4">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Start Date</p>
                <p class="text-lg font-bold text-white mt-0.5">{{ $project->contract_date ? $project->contract_date->format('M j, Y') : '—' }}</p>
            </div>
            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl p-4">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Delivery Date</p>
                <p class="text-lg font-bold text-white mt-0.5">{{ $project->delivery_date ? $project->delivery_date->format('M j, Y') : '—' }}</p>
            </div>
            @php
                $tasksTotal = $project->tasks->count();
                $tasksDone = $project->tasks->where('status', 'done')->count();
                $progressPercent = $tasksTotal > 0 ? round(($tasksDone / $tasksTotal) * 100) : 0;
            @endphp
            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl p-4">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-wide">Progress</p>
                <p class="text-lg font-bold text-sky-400 mt-0.5">{{ $progressPercent }}%</p>
            </div>
        </div>

        {{-- Tabs: Tasks, Bugs, Live Links / APK only --}}
        <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl overflow-hidden">
            <div class="flex border-b border-slate-700/50 overflow-x-auto">
                <button @click="setTab('links')" :class="activeTab === 'links' ? 'bg-sky-500/20 text-sky-400 border-b-2 border-sky-500' : 'text-slate-400 hover:text-white'" class="px-5 py-4 font-medium text-sm whitespace-nowrap border-b-2 border-transparent">Live Links / APK</button>
                <button @click="setTab('tasks')" :class="activeTab === 'tasks' ? 'bg-sky-500/20 text-sky-400 border-b-2 border-sky-500' : 'text-slate-400 hover:text-white'" class="px-5 py-4 font-medium text-sm whitespace-nowrap border-b-2 border-transparent">Tasks</button>
                <button @click="setTab('bugs')" :class="activeTab === 'bugs' ? 'bg-sky-500/20 text-sky-400 border-b-2 border-sky-500' : 'text-slate-400 hover:text-white'" class="px-5 py-4 font-medium text-sm whitespace-nowrap border-b-2 border-transparent">Bugs</button>
            </div>

            {{-- Tab: Tasks (public, read-only) --}}
            <div x-show="activeTab === 'tasks'" class="p-5">
                <div class="grid md:grid-cols-3 gap-4 max-md:grid-cols-1 max-md:gap-3">
                    <div class="bg-slate-800/40 border border-slate-700/50 rounded-xl p-4">
                        <h3 class="text-amber-400 font-medium text-sm mb-3">To Do</h3>
                        <div class="space-y-3">
                            @foreach($project->tasks->where('status', 'todo') as $task)
                                <div class="bg-slate-800/80 border border-slate-700/50 rounded-xl p-3">
                                    <p class="font-medium text-white text-sm">{{ $task->title }}</p>
                                    @if($task->description)<p class="text-slate-500 text-xs mt-1 line-clamp-2">{{ Str::limit($task->description, 120) }}</p>@endif
                                    @if($task->due_date)<p class="text-slate-500 text-xs mt-0.5">{{ $task->due_date->format('M j, Y') }}</p>@endif
                                </div>
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
                                <div class="bg-slate-800/80 border border-slate-700/50 rounded-xl p-3">
                                    <p class="font-medium text-white text-sm">{{ $task->title }}</p>
                                    @if($task->description)<p class="text-slate-500 text-xs mt-1 line-clamp-2">{{ Str::limit($task->description, 120) }}</p>@endif
                                    @if($task->due_date)<p class="text-slate-500 text-xs mt-0.5">{{ $task->due_date->format('M j, Y') }}</p>@endif
                                </div>
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
                                <div class="bg-slate-800/80 border border-slate-700/50 rounded-xl p-3">
                                    <p class="font-medium text-white text-sm">{{ $task->title }}</p>
                                    @if($task->description)<p class="text-slate-500 text-xs mt-1 line-clamp-2">{{ Str::limit($task->description, 120) }}</p>@endif
                                    @if($task->due_date)<p class="text-slate-500 text-xs mt-0.5">{{ $task->due_date->format('M j, Y') }}</p>@endif
                                </div>
                            @endforeach
                            @if($project->tasks->where('status', 'done')->isEmpty())
                                <p class="text-slate-500 text-sm">No tasks</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab: Bugs (public, read-only) --}}
            <div x-show="activeTab === 'bugs'" class="p-5">
                <div class="space-y-3">
                    @foreach($project->bugs as $bug)
                        <div class="bg-slate-800/80 border border-slate-700/50 rounded-xl p-4">
                            <p class="font-medium text-white">{{ $bug->title }}</p>
                            @if($bug->description)<p class="text-slate-500 text-sm mt-1 line-clamp-2">{{ Str::limit($bug->description, 120) }}</p>@endif
                            <div class="flex flex-wrap gap-2 mt-2">
                                <span @class([
                                    'px-2 py-0.5 rounded text-xs font-medium',
                                    'bg-red-500/20 text-red-400' => $bug->severity === 'critical',
                                    'bg-amber-500/20 text-amber-400' => $bug->severity === 'major',
                                    'bg-slate-500/20 text-slate-400' => $bug->severity === 'minor',
                                ])>{{ ucfirst($bug->severity) }}</span>
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-slate-600/50 text-slate-400">{{ ucfirst(str_replace('_', ' ', $bug->status)) }}</span>
                            </div>
                        </div>
                    @endforeach
                    @if($project->bugs->isEmpty())
                        <p class="text-slate-500 text-sm">No bugs reported.</p>
                    @endif
                </div>
            </div>

            {{-- Tab: Live Links / APK (public only) --}}
            <div x-show="activeTab === 'links'" class="p-5">
                <ul class="space-y-4">
                    @forelse($project->projectLinks as $link)
                        <li class="bg-slate-800/80 border border-slate-700/50 rounded-xl p-4">
                            <p class="font-medium text-white">{{ $link->label }}</p>
                            @if($link->isApk() && $link->file_path)
                                <a href="{{ route('guest.links.download', $link) }}" class="inline-flex items-center gap-1.5 mt-2 px-3 py-2 rounded-lg bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30 text-sm font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Download APK
                                </a>
                            @else
                                <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="text-sky-400 hover:text-sky-300 text-sm mt-1 break-all">{{ $link->url }}</a>
                            @endif
                        </li>
                    @empty
                        <li class="text-slate-500 text-sm">No public links or downloads yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-guest-portal-layout>
