<x-app-layout>
    <x-slot name="title">Edit Client</x-slot>

    <div class="max-w-2xl">
        <h1 class="text-2xl font-semibold text-white mb-6">Edit Client</h1>
        <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-6">
            <form action="{{ route('clients.update', $client) }}" method="POST">
                @csrf
                @method('PATCH')
                @include('clients._form', ['client' => $client])
                <div class="mt-6 flex gap-3">
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium">Update</button>
                    <a href="{{ route('clients.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
