<x-app-layout>
    <x-slot name="title">Add Hot Offer</x-slot>

    <div class="max-w-2xl">
        <div class="mb-6">
            <a href="{{ route('hot-offers.index') }}" class="theme-text-secondary theme-hover-primary text-sm">← Hot Offers</a>
            <h1 class="text-2xl font-semibold theme-text-primary mt-1">Add Hot Offer</h1>
        </div>
        <div class="theme-card-bg-only theme-border border rounded-2xl p-6">
            <form action="{{ route('hot-offers.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="title" class="block text-sm font-medium theme-text-secondary mb-1">Title *</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                    @error('title')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium theme-text-secondary mb-1">Description</label>
                    <textarea name="description" id="description" rows="12" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus font-mono text-sm" placeholder="Plain text; use new lines for paragraphs. Lines starting with ১।, ২। or 1., 2. become a bullet list.">{{ old('description') }}</textarea>
                    @error('description')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="price" class="block text-sm font-medium theme-text-secondary mb-1">Price (optional)</label>
                    <input type="number" name="price" id="price" value="{{ old('price') }}" step="0.01" min="0" placeholder="Leave empty for no price" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                    @error('price')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="cta_text" class="block text-sm font-medium theme-text-secondary mb-1">CTA Button Text *</label>
                    <input type="text" name="cta_text" id="cta_text" value="{{ old('cta_text', 'Get Started') }}" required class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                    @error('cta_text')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded theme-border theme-input-bg text-orange-500 focus:ring-orange-500">
                        <span class="text-sm font-medium theme-text-secondary">Active (show on guest dashboard)</span>
                    </label>
                </div>
                <div class="pt-2 flex gap-3">
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 theme-text-primary font-medium">Create</button>
                    <a href="{{ route('hot-offers.index') }}" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
