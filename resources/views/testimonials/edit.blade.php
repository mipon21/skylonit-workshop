<x-app-layout>
    <x-slot name="title">Edit Testimonial</x-slot>

    <div class="max-w-2xl">
        <div class="mb-6">
            <a href="{{ route('testimonials.index') }}" class="theme-text-secondary theme-hover-primary text-sm">← Testimonials</a>
            <h1 class="text-2xl font-semibold theme-text-primary mt-1">Edit Testimonial</h1>
        </div>
        <div class="theme-card-bg-only theme-border border rounded-2xl p-6">
            <form action="{{ route('testimonials.update', $testimonial) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="client_name" class="block text-sm font-medium theme-text-secondary mb-1">Client Name *</label>
                    <input type="text" name="client_name" id="client_name" value="{{ old('client_name', $testimonial->client_name) }}" required class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                    @error('client_name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="feedback" class="block text-sm font-medium theme-text-secondary mb-1">Feedback *</label>
                    <textarea name="feedback" id="feedback" rows="4" required class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">{{ old('feedback', $testimonial->feedback) }}</textarea>
                    @error('feedback')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="photo" class="block text-sm font-medium theme-text-secondary mb-1">Photo (optional)</label>
                    @php
                        $currentPhotoPath = old('photo', $testimonial->photo);
                        $currentPhotoUrl = $currentPhotoPath ? (str_starts_with($currentPhotoPath, 'http') ? $currentPhotoPath : asset($currentPhotoPath)) : null;
                    @endphp
                    @if($currentPhotoUrl)
                        <div class="mb-3 flex items-center gap-4">
                            <img src="{{ $currentPhotoUrl }}" alt="Current photo" class="w-16 h-16 rounded-full object-cover border theme-border" onerror="this.style.display='none'">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="hidden" name="remove_photo" value="0">
                                <input type="checkbox" name="remove_photo" value="1" {{ old('remove_photo') ? 'checked' : '' }} class="rounded theme-border theme-input-bg text-red-500 focus:ring-red-500">
                                <span class="text-sm theme-text-secondary">Remove current photo</span>
                            </label>
                        </div>
                    @endif
                    <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/gif,image/webp" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:theme-bg-tertiary file:theme-text-secondary file:text-sm theme-input-focus">
                    <p class="theme-text-muted text-xs mt-1">Upload client photo (JPG, PNG, GIF, WebP). Max 2 MB.</p>
                    @error('photo')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $testimonial->is_active) ? 'checked' : '' }} class="rounded theme-border theme-input-bg text-orange-500 focus:ring-orange-500">
                        <span class="text-sm font-medium theme-text-secondary">Active (show on guest dashboard)</span>
                    </label>
                </div>
                <div class="pt-2 flex gap-3">
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 theme-text-primary font-medium">Update</button>
                    <a href="{{ route('testimonials.index') }}" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
