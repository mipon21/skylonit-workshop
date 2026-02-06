<x-guest-portal-layout title="Dashboard">
    <div class="space-y-6">
        <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl p-5 shadow">
            <h2 class="text-slate-200 font-semibold text-lg mb-2">Welcome</h2>
            <p class="text-slate-400 text-sm">Browse our public projects, live links, and APK downloads. Use the sidebar to explore, or <a href="{{ route('guest.contact') }}" class="text-sky-400 hover:text-sky-300">contact us</a> for enquiries.</p>
        </div>

        <h1 class="text-2xl font-semibold text-white">Dashboard</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 max-md:grid-cols-1 max-md:gap-3">
            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl p-5 shadow-lg hover:shadow-xl transition-shadow max-md:p-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-cyan-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    </div>
                    <span class="text-slate-400 text-sm font-medium">Total Public Projects</span>
                </div>
                <p class="text-2xl font-bold text-white">{{ $totalPublicProjects }}</p>
                <p class="text-slate-500 text-xs mt-1">Showcase projects</p>
            </div>

            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl p-5 shadow-lg hover:shadow-xl transition-shadow max-md:p-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-sky-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <span class="text-slate-400 text-sm font-medium">Running Public Projects</span>
                </div>
                <p class="text-2xl font-bold text-white">{{ $runningPublicProjects }}</p>
                <p class="text-slate-500 text-xs mt-1">Pending + Running</p>
            </div>

            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl p-5 shadow-lg hover:shadow-xl transition-shadow max-md:p-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <span class="text-slate-400 text-sm font-medium">Open Public Tasks</span>
                </div>
                <p class="text-2xl font-bold text-white">{{ $openPublicTasks }}</p>
                <p class="text-slate-500 text-xs mt-1">To do + Doing</p>
            </div>

            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-2xl p-5 shadow-lg hover:shadow-xl transition-shadow max-md:p-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-red-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <span class="text-slate-400 text-sm font-medium">Open Public Bugs</span>
                </div>
                <p class="text-2xl font-bold text-white">{{ $openPublicBugs }}</p>
                <p class="text-slate-500 text-xs mt-1">Open + In progress</p>
            </div>
        </div>
    </div>
</x-guest-portal-layout>
