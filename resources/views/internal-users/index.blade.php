<x-app-layout>
    <x-slot name="title">{{ $roleLabel }}s</x-slot>

    <script type="application/json" id="internal-users-initial-data">{!! json_encode(['usersData' => $usersData]) !!}</script>
    <script>
        function registerInternalUsersPage() {
            Alpine.data('internalUsersPage', function () {
                const el = document.getElementById('internal-users-initial-data');
                const initial = el ? JSON.parse(el.textContent) : { usersData: [] };
                return {
                    usersData: initial.usersData || [],
                    searchText: '',
                    get filteredIds() {
                        const q = (this.searchText || '').toLowerCase().trim();
                        if (!q) return this.usersData.map(function (c) { return c.id; });
                        return this.usersData.filter(function (c) {
                            const name = (c.name || '').toLowerCase();
                            const email = (c.email || '').toLowerCase();
                            return name.includes(q) || email.includes(q);
                        }).map(function (c) { return c.id; });
                    }
                };
            });
        }
        if (window.Alpine) registerInternalUsersPage(); else document.addEventListener('alpine:init', registerInternalUsersPage);
    </script>

    <div class="space-y-6" x-data="{ open: {{ json_encode($errors->any()) }} }">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold text-white">{{ $roleLabel }}s</h1>
            @php
                $createRoute = $role === 'developer' ? 'developers.create' : 'sales.create';
            @endphp
            <a href="{{ route($createRoute) }}" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium text-sm transition">
                Add {{ $roleLabel }}
            </a>
        </div>

        <div class="space-y-6" x-data="internalUsersPage()">
            <label for="internal-users-search" class="sr-only">Search</label>
            <input type="text" id="internal-users-search" x-model="searchText" placeholder="Search by name, email…" class="w-full max-w-md rounded-xl bg-slate-800 border border-slate-600 text-white px-4 py-2.5 text-sm placeholder-slate-500 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">

            <div class="bg-slate-800/60 backdrop-blur border border-slate-700/50 rounded-2xl overflow-hidden max-md:overflow-x-auto">
                <div class="overflow-x-auto">
                    <table class="w-full max-md:min-w-[400px]">
                        <thead class="bg-slate-800/80 border-b border-slate-700/50">
                            <tr>
                                <th class="text-left px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Name</th>
                                <th class="text-left px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Email</th>
                                <th class="text-right px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            @forelse($users as $u)
                                <tr class="hover:bg-slate-800/40 transition" x-show="filteredIds.includes({{ $u->id }})" x-transition>
                                    <td class="px-5 py-4">
                                        @php $showRoute = $role === 'developer' ? 'developers.show' : 'sales.show'; @endphp
                                        <a href="{{ route($showRoute, $u) }}" class="font-medium text-sky-400 hover:text-sky-300">{{ $u->name }}</a>
                                    </td>
                                    <td class="px-5 py-4 text-slate-400">{{ $u->email }}</td>
                                    <td class="px-5 py-4 text-right">
                                        @php $editRoute = $role === 'developer' ? 'developers.edit' : 'sales.edit'; $destroyRoute = $role === 'developer' ? 'developers.destroy' : 'sales.destroy'; @endphp
                                        <a href="{{ route($editRoute, $u) }}" class="text-slate-400 hover:text-white text-sm mr-3">Edit</a>
                                        <form action="{{ route($destroyRoute, $u) }}" method="POST" class="inline" onsubmit="return confirm('Delete this {{ strtolower($roleLabel) }} account?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-12 text-center text-slate-500">No {{ $roleLabel }}s yet. Add one to get started.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <p x-show="usersData.length && searchText && filteredIds.length === 0" x-transition class="py-6 text-center text-slate-500">No {{ $roleLabel }}s match your search.</p>
        </div>
    </div>
</x-app-layout>
