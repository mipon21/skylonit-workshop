<x-app-layout>
    <x-slot name="title">{{ $user->name }} ({{ $roleLabel }})</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold theme-text-primary">{{ $user->name }}</h1>
            @php
                $editRoute = $role === 'developer' ? 'developers.edit' : 'sales.edit';
                $indexRoute = $role === 'developer' ? 'developers.index' : 'sales.index';
            @endphp
            <div class="flex gap-2">
                <a href="{{ route($editRoute, $user) }}" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover text-sm font-medium">Edit</a>
                <a href="{{ route($indexRoute) }}" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover text-sm font-medium">← {{ $roleLabel }}s</a>
            </div>
        </div>

        <div class="theme-card-bg-only theme-border border rounded-2xl p-6">
            <p class="theme-text-secondary"><span class="theme-text-muted">Email:</span> {{ $user->email }}</p>
            <p class="theme-text-secondary mt-1"><span class="theme-text-muted">Role:</span> {{ $roleLabel }}</p>
        </div>

        @php
            $assignedProjects = $role === 'developer' ? $user->projectsAsDeveloper : $user->projectsAsSales;
        @endphp
        <div class="theme-card-bg-only theme-border border rounded-2xl p-6">
            <h2 class="text-lg font-semibold theme-text-primary mb-3">Assigned projects ({{ $assignedProjects->count() }})</h2>
            @if($assignedProjects->isEmpty())
                <p class="theme-text-muted text-sm">Not assigned to any project yet.</p>
            @else
                <ul class="space-y-2">
                    @foreach($assignedProjects as $p)
                        <li>
                            <a href="{{ route('projects.show', $p) }}" class="text-orange-400 hover:text-orange-300">{{ $p->project_name }}</a>
                            @if($p->project_code)<span class="theme-text-muted text-sm">· {{ $p->project_code }}</span>@endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="theme-card-bg-only theme-border border rounded-2xl p-6">
            <h2 class="text-lg font-semibold theme-text-primary mb-3">Payment methods</h2>
            <p class="theme-text-muted text-sm mb-3">Visible only to Admin. {{ $roleLabel }} can manage these from their Profile.</p>
            @if($user->paymentMethods->isEmpty())
                <p class="theme-text-muted text-sm">No payment methods added.</p>
            @else
                <ul class="space-y-2">
                    @foreach($user->paymentMethods as $pm)
                        <li class="flex items-center gap-2 theme-text-secondary text-sm">
                            <span class="font-medium">{{ $pm->label ?: ucfirst($pm->type) }}</span>
                            @if($pm->details)<span class="theme-text-muted">— {{ Str::limit($pm->details, 60) }}</span>@endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-app-layout>
