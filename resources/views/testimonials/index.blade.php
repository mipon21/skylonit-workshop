<x-app-layout>
    <x-slot name="title">Testimonials</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold text-white">Marketing → Testimonials</h1>
            <a href="{{ route('testimonials.create') }}" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium text-sm transition">Add Testimonial</a>
        </div>

        <div class="bg-slate-800/60 backdrop-blur border border-slate-700/50 rounded-2xl overflow-hidden max-md:overflow-x-auto">
            <div class="overflow-x-auto">
                <table class="w-full max-md:min-w-[600px]">
                    <thead class="bg-slate-800/80 border-b border-slate-700/50">
                        <tr>
                            <th class="text-left px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Client</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Feedback</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Active</th>
                            <th class="text-right px-5 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($testimonials as $t)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="px-5 py-4 font-medium text-white">{{ $t->client_name }}</td>
                                <td class="px-5 py-4 text-slate-400 max-w-md truncate" title="{{ $t->feedback }}">{{ Str::limit($t->feedback, 60) }}</td>
                                <td class="px-5 py-4">
                                    @if($t->is_active)
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-emerald-500/20 text-emerald-400">Yes</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-slate-500/20 text-slate-400">No</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('testimonials.edit', $t) }}" class="text-sky-400 hover:text-sky-300 text-sm font-medium">Edit</a>
                                    <form action="{{ route('testimonials.destroy', $t) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('Delete this testimonial?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300 text-sm font-medium">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center text-slate-500">No testimonials yet. Add one to show on the guest dashboard.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-slate-700/50">
                {{ $testimonials->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
