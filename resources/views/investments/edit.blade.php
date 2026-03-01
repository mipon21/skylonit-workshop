<x-app-layout>
    <x-slot name="title">Edit {{ $investment->investor_name }}</x-slot>

    <div class="space-y-6">
        <div>
            <a href="{{ route('investments.show', $investment) }}" class="theme-text-secondary theme-hover-primary text-sm mb-2 inline-block">← {{ $investment->investor_name }}</a>
            <h1 class="text-2xl font-semibold theme-text-primary">Edit investor</h1>
        </div>

        <div class="max-w-xl">
            @if($shareholderTotalError ?? null)
                <p class="text-amber-400 text-sm bg-amber-500/10 border border-amber-500/30 rounded-xl px-4 py-3 mb-4">{{ $shareholderTotalError }}</p>
            @endif
            <form action="{{ route('investments.update', $investment) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="investor_name" class="block text-sm font-medium theme-text-secondary mb-1">Name</label>
                    <input type="text" name="investor_name" id="investor_name" value="{{ old('investor_name', $investment->investor_name) }}" required class="w-full rounded-xl theme-bg-tertiary border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                    @error('investor_name')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                @if($investment->category === 'shareholder')
                <div>
                    <label for="share_percent" class="block text-sm font-medium theme-text-secondary mb-1">Share percent</label>
                    <input type="number" name="share_percent" id="share_percent" value="{{ old('share_percent', $investment->share_percent) }}" step="0.01" min="0.01" max="100" required class="w-full rounded-xl theme-bg-tertiary border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                    <p class="mt-1 text-xs theme-text-muted">Shareholder splits must total 100% across all shareholders.</p>
                    @error('share_percent')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                @else
                <p class="theme-text-muted text-sm">Amount, risk level, and cap cannot be changed after creation.</p>
                @endif
                <div>
                    <label for="notes" class="block text-sm font-medium theme-text-secondary mb-1">Notes (optional)</label>
                    <textarea name="notes" id="notes" rows="3" class="w-full rounded-xl theme-bg-tertiary border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">{{ old('notes', $investment->notes) }}</textarea>
                    @error('notes')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div class="flex gap-3 pt-2">
                    <a href="{{ route('investments.show', $investment) }}" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover">Cancel</a>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 theme-text-primary font-medium">Update</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
