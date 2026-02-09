<x-app-layout>
    <x-slot name="title">{{ $user->name }} ({{ $roleLabel }})</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold text-white">{{ $user->name }}</h1>
            @php
                $editRoute = $role === 'developer' ? 'developers.edit' : 'sales.edit';
                $indexRoute = $role === 'developer' ? 'developers.index' : 'sales.index';
            @endphp
            <div class="flex gap-2">
                <a href="{{ route($editRoute, $user) }}" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700 text-sm font-medium">Edit</a>
                <a href="{{ route($indexRoute) }}" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700 text-sm font-medium">← {{ $roleLabel }}s</a>
            </div>
        </div>

        <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-6">
            <p class="text-slate-400"><span class="text-slate-500">Email:</span> {{ $user->email }}</p>
            <p class="text-slate-400 mt-1"><span class="text-slate-500">Role:</span> {{ $roleLabel }}</p>
        </div>

        @php
            $assignedProjects = $role === 'developer' ? $user->projectsAsDeveloper : $user->projectsAsSales;
        @endphp
        <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-6">
            <h2 class="text-lg font-semibold text-white mb-3">Assigned projects ({{ $assignedProjects->count() }})</h2>
            @if($assignedProjects->isEmpty())
                <p class="text-slate-500 text-sm">Not assigned to any project yet.</p>
            @else
                <ul class="space-y-2">
                    @foreach($assignedProjects as $p)
                        <li>
                            <a href="{{ route('projects.show', $p) }}" class="text-sky-400 hover:text-sky-300">{{ $p->project_name }}</a>
                            @if($p->project_code)<span class="text-slate-500 text-sm">· {{ $p->project_code }}</span>@endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-6">
            <h2 class="text-lg font-semibold text-white mb-3">Payment methods</h2>
            <p class="text-slate-500 text-sm mb-3">Visible only to Admin. {{ $roleLabel }} can manage these from their Profile.</p>
            @if($user->paymentMethods->isEmpty())
                <p class="text-slate-500 text-sm">No payment methods added.</p>
            @else
                <ul class="space-y-2">
                    @foreach($user->paymentMethods as $pm)
                        <li class="flex items-center gap-2 text-slate-300 text-sm">
                            <span class="font-medium">{{ $pm->label ?: ucfirst($pm->type) }}</span>
                            @if($pm->details)<span class="text-slate-500">— {{ Str::limit($pm->details, 60) }}</span>@endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-app-layout>
