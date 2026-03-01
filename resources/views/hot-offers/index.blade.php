<x-app-layout>
    <x-slot name="title">Hot Offers</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-semibold theme-text-primary">Marketing → Hot Offers</h1>
            <a href="{{ route('hot-offers.create') }}" class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 theme-text-primary font-medium text-sm transition">Add Hot Offer</a>
        </div>

        <div class="theme-bg-tertiary/60 backdrop-blur border theme-border rounded-2xl overflow-hidden max-md:overflow-x-auto">
            <div class="overflow-x-auto">
                <table class="w-full max-md:min-w-[600px]">
                    <thead class="theme-bg-tertiary/80 border-b theme-border">
                        <tr>
                            <th class="text-left px-5 py-4 text-xs font-semibold theme-text-secondary uppercase tracking-wider">Title</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold theme-text-secondary uppercase tracking-wider">Description</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold theme-text-secondary uppercase tracking-wider">Price</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold theme-text-secondary uppercase tracking-wider">CTA</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold theme-text-secondary uppercase tracking-wider">Active</th>
                            <th class="text-right px-5 py-4 text-xs font-semibold theme-text-secondary uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($hotOffers as $offer)
                            <tr class="hover:theme-bg-tertiary/40 transition">
                                <td class="px-5 py-4 font-medium theme-text-primary">{{ $offer->title }}</td>
                                <td class="px-5 py-4 theme-text-secondary max-w-xs truncate" title="{{ $offer->description }}">{{ Str::limit($offer->description, 50) ?: '—' }}</td>
                                <td class="px-5 py-4 theme-text-secondary">{{ $offer->price !== null ? '৳' . number_format($offer->price, 2) : '—' }}</td>
                                <td class="px-5 py-4 theme-text-secondary">{{ $offer->cta_text }}</td>
                                <td class="px-5 py-4">
                                    @if($offer->is_active)
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-emerald-500/20 text-emerald-400">Yes</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-slate-500/20 theme-text-secondary">No</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('hot-offers.edit', $offer) }}" class="text-orange-400 hover:text-orange-300 text-sm font-medium">Edit</a>
                                    <form action="{{ route('hot-offers.destroy', $offer) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('Delete this offer?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300 text-sm font-medium">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center theme-text-muted">No hot offers yet. Add one to show on the guest dashboard.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t theme-border">
                {{ $hotOffers->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
