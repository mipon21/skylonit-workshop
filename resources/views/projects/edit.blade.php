<x-app-layout>
    <x-slot name="title">Edit Project</x-slot>

    <div class="max-w-2xl">
        <div class="mb-6">
            <a href="{{ route('projects.show', $project) }}" class="text-slate-400 hover:text-white text-sm">← Back to project</a>
            <h1 class="text-2xl font-semibold text-white mt-1">Edit Project</h1>
        </div>
        <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-6">
            <form action="{{ route('projects.update', $project) }}" method="POST">
                @csrf
                @method('PATCH')
                @include('projects._form', ['project' => $project, 'clients' => $clients])
                <div class="mt-6 flex gap-3">
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium">Update</button>
                    <a href="{{ route('projects.show', $project) }}" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
