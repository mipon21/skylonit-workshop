<x-app-layout>
    <x-slot name="title">Add Project</x-slot>

    <div class="max-w-2xl">
        <h1 class="text-2xl font-semibold text-white mb-6">Add Project</h1>
        <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-6">
            <form action="{{ route('projects.store') }}" method="POST">
                @csrf
                @include('projects._form', ['clients' => $clients, 'nextProjectCode' => $nextProjectCode])
                <div class="mt-6 flex gap-3">
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium">Save</button>
                    <a href="{{ route('projects.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
