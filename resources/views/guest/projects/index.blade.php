<x-guest-portal-layout title="Projects">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold theme-text-primary">Projects</h1>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 max-md:grid-cols-1 max-md:gap-3">
            @forelse($projects as $project)
                <div class="group relative theme-bg-tertiary/60 backdrop-blur border theme-border rounded-2xl p-5 shadow-lg hover:shadow-xl hover:theme-border transition-all hover:-translate-y-0.5 overflow-visible max-md:p-4">
                    <a href="{{ route('guest.projects.show', $project) }}" class="block">
                        <p class="font-semibold theme-text-primary group-hover:text-orange-400 transition">{{ $project->project_name }} <span class="theme-text-muted text-sm font-normal">· {{ $project->project_code ?: $project->formatted_id }}</span></p>
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
                        </div>
                        @php
                            $cardTasksTotal = $project->tasks_count ?? 0;
                            $cardTasksDone = $project->tasks_done_count ?? 0;
                            $cardProgressPercent = $cardTasksTotal > 0 ? round(($cardTasksDone / $cardTasksTotal) * 100) : 0;
                        @endphp
                        <div class="mt-3">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="theme-text-muted font-medium">Progress</span>
                                <span class="text-orange-400 tabular-nums">{{ $cardProgressPercent }}%</span>
                            </div>
                            <div class="w-full overflow-hidden rounded-full border theme-border/50 theme-bg-tertiary" style="height: 10px;">
                                <div class="h-full rounded-full transition-[width] duration-500 ease-out" style="width: {{ $cardProgressPercent }}%; height: 10px; background: linear-gradient(to right, #EF8121, #EF8121);"></div>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t theme-border flex justify-between text-sm">
                            <span class="theme-text-secondary">Start</span>
                            <span class="theme-text-primary font-medium">{{ $project->contract_date ? $project->contract_date->format('M j, Y') : '—' }}</span>
                        </div>
                        <div class="mt-1 flex justify-between text-sm">
                            <span class="theme-text-secondary">Delivery</span>
                            <span class="theme-text-secondary">{{ $project->delivery_date ? $project->delivery_date->format('M j, Y') : '—' }}</span>
                        </div>
                    </a>
                    @php $guestLinks = $project->projectLinks ?? collect(); @endphp
                    @if($guestLinks->isNotEmpty())
                    <div class="mt-4 pt-4 border-t theme-border">
                        <p class="theme-text-secondary text-xs font-medium mb-2">Live links / APK</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($guestLinks as $link)
                                @if($link->isApk() && $link->file_path)
                                    <a href="{{ route('guest.links.download', $link) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30 text-xs font-medium truncate max-w-[140px]" title="{{ $link->label }}">{{ Str::limit($link->label, 18) }}</a>
                                @else
                                    <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 text-xs font-medium truncate max-w-[140px]" title="{{ $link->label }}">{{ Str::limit($link->label, 18) }}</a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif
                    <div class="mt-4 pt-4 border-t theme-border flex flex-wrap items-center gap-2">
                        <a href="{{ route('guest.projects.show', $project) }}" class="px-3 py-1.5 rounded-lg theme-bg-tertiary/80 hover:theme-bg-tertiary theme-text-secondary theme-hover-primary text-xs font-medium">View</a>
                    </div>
                </div>
            @empty
                <div class="sm:col-span-2 lg:col-span-3 theme-card-bg-only theme-border border rounded-2xl p-12 text-center theme-text-muted">
                    No public projects at the moment.
                </div>
            @endforelse
        </div>
    </div>
</x-guest-portal-layout>
