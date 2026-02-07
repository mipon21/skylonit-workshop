<x-app-layout>
    <x-slot name="title">Edit Hot Offer</x-slot>

    <div class="max-w-2xl">
        <div class="mb-6">
            <a href="{{ route('hot-offers.index') }}" class="text-slate-400 hover:text-white text-sm">← Hot Offers</a>
            <h1 class="text-2xl font-semibold text-white mt-1">Edit Hot Offer</h1>
        </div>
        <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-6">
            <form action="{{ route('hot-offers.update', $hotOffer) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="title" class="block text-sm font-medium text-slate-400 mb-1">Title *</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $hotOffer->title) }}" required class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    @error('title')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-slate-400 mb-1">Description</label>
                    <textarea name="description" id="description" rows="12" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 font-mono text-sm" placeholder="Plain text; use new lines for paragraphs. Lines starting with ১।, ২। or 1., 2. become a bullet list.">{{ old('description', $hotOffer->description) }}</textarea>
                    @error('description')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="price" class="block text-sm font-medium text-slate-400 mb-1">Price (optional)</label>
                    <input type="number" name="price" id="price" value="{{ old('price', $hotOffer->price) }}" step="0.01" min="0" placeholder="Leave empty for no price" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    @error('price')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="cta_text" class="block text-sm font-medium text-slate-400 mb-1">CTA Button Text *</label>
                    <input type="text" name="cta_text" id="cta_text" value="{{ old('cta_text', $hotOffer->cta_text) }}" required class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    @error('cta_text')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $hotOffer->is_active) ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-900 text-sky-500 focus:ring-sky-500">
                        <span class="text-sm font-medium text-slate-400">Active (show on guest dashboard)</span>
                    </label>
                </div>
                <div class="pt-2 flex gap-3">
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium">Update</button>
                    <a href="{{ route('hot-offers.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
