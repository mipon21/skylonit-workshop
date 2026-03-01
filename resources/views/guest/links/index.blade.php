<x-guest-portal-layout title="Live Links / APK">
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold theme-text-primary">Live Links / APK</h1>
        <p class="theme-text-secondary text-sm">Public live URLs and APK downloads across all projects.</p>

        <div class="space-y-6">
            @php $currentProjectId = null; @endphp
            @foreach($links as $link)
                @if($link->project_id !== $currentProjectId)
                    @php $currentProjectId = $link->project_id; @endphp
                    <h2 class="text-lg font-medium theme-text-secondary border-b theme-border pb-2">{{ $link->project->project_name ?? 'Project' }}</h2>
                @endif
                <div class="theme-bg-tertiary/60 backdrop-blur border theme-border rounded-xl p-4 flex flex-wrap items-center justify-between gap-3 shadow-lg hover:theme-border/80 transition-all max-md:p-4">
                    <div class="min-w-0 flex-1">
                        <p class="font-medium theme-text-primary">{{ $link->label }}</p>
                        @if($link->isApk() && $link->file_path)
                            <p class="theme-text-muted text-sm mt-0.5">APK download</p>
                        @else
                            <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="text-orange-400 hover:text-orange-300 text-sm mt-1 break-all">{{ $link->url }}</a>
                        @endif
                    </div>
                    @if($link->isApk() && $link->file_path)
                        <a href="{{ route('guest.links.download', $link) }}" class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30 text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download
                        </a>
                    @else
                        <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="shrink-0 px-4 py-2 rounded-lg bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 text-sm font-medium">Open</a>
                    @endif
                </div>
            @endforeach
        </div>

        @if($links->isEmpty())
            <div class="theme-card-bg-only theme-border border rounded-2xl p-12 text-center theme-text-muted">
                No public links or APK downloads at the moment.
            </div>
        @endif
    </div>
</x-guest-portal-layout>
