<x-app-layout>
    <x-slot name="title">Clients</x-slot>

    <script type="application/json" id="clients-initial-data">{!! json_encode(['clientsData' => $clientsData]) !!}</script>
    <script>
        function registerClientsPage() {
            Alpine.data('clientsPage', function () {
                const el = document.getElementById('clients-initial-data');
                const initial = el ? JSON.parse(el.textContent) : { clientsData: [] };
                return {
                    clientsData: initial.clientsData || [],
                    searchText: '',
                    get filteredIds() {
                        const q = (this.searchText || '').toLowerCase().trim();
                        if (!q) return this.clientsData.map(function (c) { return c.id; });
                        return this.clientsData.filter(function (c) {
                            const name = (c.name || '').toLowerCase();
                            const phone = (c.phone || '').toLowerCase();
                            const email = (c.email || '').toLowerCase();
                            return name.includes(q) || phone.includes(q) || email.includes(q);
                        }).map(function (c) { return c.id; });
                    }
                };
            });
        }
        if (window.Alpine) registerClientsPage(); else document.addEventListener('alpine:init', registerClientsPage);
    </script>

    <div class="space-y-6" x-data="{ open: {{ json_encode($errors->any()) }} }">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold text-white">Clients</h1>
            <button @click="open = true" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium text-sm transition">
                Add Client
            </button>
        </div>

        <div class="space-y-6" x-data="clientsPage()">
            <label for="clients-search" class="sr-only">Search clients</label>
            <input type="text" id="clients-search" x-model="searchText" placeholder="Search by name, phone, email…" class="w-full max-w-md rounded-xl bg-slate-800 border border-slate-600 text-white px-4 py-2.5 text-sm placeholder-slate-500 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">

        <div class="bg-slate-800/60 backdrop-blur border border-slate-700/50 rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-800/80 border-b border-slate-700/50">
                        <tr>
                            <th class="text-left px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Name</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Phone</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Email</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Projects</th>
                            <th class="text-right px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($clients as $client)
                            <tr class="hover:bg-slate-800/40 transition" x-show="filteredIds.includes({{ $client->id }})" x-transition>
                                <td class="px-5 py-4">
                                    <a href="{{ route('clients.show', $client) }}" class="font-medium text-sky-400 hover:text-sky-300">{{ $client->name }}</a>
                                </td>
                                <td class="px-5 py-4 text-slate-400">{{ $client->phone ?? '—' }}</td>
                                <td class="px-5 py-4 text-slate-400">{{ $client->email ?? '—' }}</td>
                                <td class="px-5 py-4 text-slate-400">{{ $client->projects_count }}</td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('clients.edit', $client) }}" class="text-slate-400 hover:text-white text-sm mr-3">Edit</a>
                                    <form action="{{ route('clients.destroy', $client) }}" method="POST" class="inline" onsubmit="return confirm('Delete this client?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center text-slate-500">No clients yet. Add one to get started.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
            <p x-show="clientsData.length && searchText && filteredIds.length === 0" x-transition class="py-6 text-center text-slate-500">No clients match your search.</p>
        </div>

        {{-- Add Client Modal --}}
        <div x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="open" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="open = false"></div>
                <div x-show="open" x-transition class="relative w-full max-w-md max-h-[90vh] flex flex-col bg-slate-800 border border-slate-700 rounded-2xl shadow-xl">
                    <div class="p-6 overflow-y-auto">
                        <h2 class="text-lg font-semibold text-white mb-4">New Client</h2>
                        <form action="{{ route('clients.store') }}" method="POST">
                            @csrf
                            @include('clients._form', ['client' => null])
                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button" @click="open = false" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700">Cancel</button>
                                <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
