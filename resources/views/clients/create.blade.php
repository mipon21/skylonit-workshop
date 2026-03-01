<x-app-layout>
    <x-slot name="title">Add Client</x-slot>

    <div class="max-w-2xl">
        <h1 class="text-2xl font-semibold theme-text-primary mb-6">Add Client</h1>
        <div class="theme-card-bg-only theme-border border rounded-2xl p-6">
            <form action="{{ route('clients.store') }}" method="POST">
                @csrf
                @include('clients._form')
                <div class="mt-6 flex gap-3">
                    <button type="submit" class="px-4 py-2.5 rounded-xl theme-btn-primary font-medium">Save</button>
                    <a href="{{ route('clients.index') }}" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
