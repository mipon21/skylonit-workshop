<x-guest-portal-layout title="Live Links / APK">
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-white">Live Links / APK</h1>
        <p class="text-slate-400 text-sm">Public live URLs and APK downloads across all projects.</p>

        <div class="space-y-6">
            @php $currentProjectId = null; @endphp
            @foreach($links as $link)
                @if($link->project_id !== $currentProjectId)
                    @php $currentProjectId = $link->project_id; @endphp
                    <h2 class="text-lg font-medium text-slate-300 border-b border-slate-700/50 pb-2">{{ $link->project->project_name ?? 'Project' }}</h2>
                @endif
                <div class="bg-slate-800/80 border border-slate-700/50 rounded-xl p-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-white">{{ $link->label }}</p>
                        @if($link->isApk() && $link->file_path)
                            <p class="text-slate-500 text-sm mt-0.5">APK download</p>
                        @else
                            <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="text-sky-400 hover:text-sky-300 text-sm mt-1 break-all">{{ $link->url }}</a>
                        @endif
                    </div>
                    @if($link->isApk() && $link->file_path)
                        <a href="{{ route('guest.links.download', $link) }}" class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30 text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download
                        </a>
                    @else
                        <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="shrink-0 px-4 py-2 rounded-lg bg-sky-500/20 text-sky-400 hover:bg-sky-500/30 text-sm font-medium">Open</a>
                    @endif
                </div>
            @endforeach
        </div>

        @if($links->isEmpty())
            <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-12 text-center text-slate-500">
                No public links or APK downloads at the moment.
            </div>
        @endif
    </div>
</x-guest-portal-layout>
