<x-app-layout>
    <x-slot name="title">Edit {{ $roleLabel }}</x-slot>

    <div class="max-w-2xl">
        <h1 class="text-2xl font-semibold theme-text-primary mb-6">Edit {{ $roleLabel }}</h1>
        <div class="theme-card-bg-only theme-border border rounded-2xl p-6">
            @php
                $updateRoute = $role === 'developer' ? 'developers.update' : 'sales.update';
                $indexRoute = $role === 'developer' ? 'developers.index' : 'sales.index';
            @endphp
            <form action="{{ route($updateRoute, $user) }}" method="POST">
                @csrf
                @method('PUT')
                @include('internal-users._form', ['user' => $user])
                <div class="mt-6 flex gap-3">
                    <button type="submit" class="px-4 py-2.5 rounded-xl theme-btn-primary font-medium">Update</button>
                    <a href="{{ route($indexRoute) }}" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
