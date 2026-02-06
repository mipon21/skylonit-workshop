<x-guest-portal-layout title="Projects">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold text-white">Projects</h1>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 max-md:grid-cols-1 max-md:gap-3">
            @forelse($projects as $project)
                <div class="group relative bg-slate-800/60 backdrop-blur border border-slate-700/50 rounded-2xl p-5 shadow-lg hover:shadow-xl hover:border-slate-600 transition-all hover:-translate-y-0.5 overflow-visible max-md:p-4">
                    <a href="{{ route('guest.projects.show', $project) }}" class="block">
                        <p class="font-semibold text-white group-hover:text-sky-400 transition">{{ $project->project_name }} <span class="text-slate-500 text-sm font-normal">· {{ $project->project_code ?: $project->formatted_id }}</span></p>
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
                        </div>
                        @php
                            $cardTasksTotal = $project->tasks_count ?? 0;
                            $cardTasksDone = $project->tasks_done_count ?? 0;
                            $cardProgressPercent = $cardTasksTotal > 0 ? round(($cardTasksDone / $cardTasksTotal) * 100) : 0;
                        @endphp
                        <div class="mt-3">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-slate-500 font-medium">Progress</span>
                                <span class="text-sky-400 tabular-nums">{{ $cardProgressPercent }}%</span>
                            </div>
                            <div class="w-full overflow-hidden rounded-full border border-slate-600/50 bg-slate-700/90" style="height: 10px;">
                                <div class="h-full rounded-full transition-[width] duration-500 ease-out" style="width: {{ $cardProgressPercent }}%; height: 10px; background: linear-gradient(to right, #0ea5e9, #22d3ee);"></div>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-slate-700/50 flex justify-between text-sm">
                            <span class="text-slate-400">Start</span>
                            <span class="text-white font-medium">{{ $project->contract_date ? $project->contract_date->format('M j, Y') : '—' }}</span>
                        </div>
                        <div class="mt-1 flex justify-between text-sm">
                            <span class="text-slate-400">Delivery</span>
                            <span class="text-slate-300">{{ $project->delivery_date ? $project->delivery_date->format('M j, Y') : '—' }}</span>
                        </div>
                    </a>
                    @php $guestLinks = $project->projectLinks ?? collect(); @endphp
                    @if($guestLinks->isNotEmpty())
                    <div class="mt-4 pt-4 border-t border-slate-700/50">
                        <p class="text-slate-400 text-xs font-medium mb-2">Live links / APK</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($guestLinks as $link)
                                @if($link->isApk() && $link->file_path)
                                    <a href="{{ route('guest.links.download', $link) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30 text-xs font-medium truncate max-w-[140px]" title="{{ $link->label }}">{{ Str::limit($link->label, 18) }}</a>
                                @else
                                    <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-sky-500/20 text-sky-400 hover:bg-sky-500/30 text-xs font-medium truncate max-w-[140px]" title="{{ $link->label }}">{{ Str::limit($link->label, 18) }}</a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif
                    <div class="mt-4 pt-4 border-t border-slate-700/50 flex flex-wrap items-center gap-2">
                        <a href="{{ route('guest.projects.show', $project) }}" class="px-3 py-1.5 rounded-lg bg-slate-700/80 hover:bg-slate-600 text-slate-300 hover:text-white text-xs font-medium">View</a>
                    </div>
                </div>
            @empty
                <div class="sm:col-span-2 lg:col-span-3 bg-slate-800/40 border border-slate-700/50 rounded-2xl p-12 text-center text-slate-500">
                    No public projects at the moment.
                </div>
            @endforelse
        </div>
    </div>
</x-guest-portal-layout>
