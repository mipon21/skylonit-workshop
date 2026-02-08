<x-app-layout>
    <x-slot name="title">Add Testimonial</x-slot>

    <div class="max-w-2xl">
        <div class="mb-6">
            <a href="{{ route('testimonials.index') }}" class="text-slate-400 hover:text-white text-sm">← Testimonials</a>
            <h1 class="text-2xl font-semibold text-white mt-1">Add Testimonial</h1>
        </div>
        <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-6">
            <form action="{{ route('testimonials.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label for="client_name" class="block text-sm font-medium text-slate-400 mb-1">Client Name *</label>
                    <input type="text" name="client_name" id="client_name" value="{{ old('client_name') }}" required class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    @error('client_name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="feedback" class="block text-sm font-medium text-slate-400 mb-1">Feedback *</label>
                    <textarea name="feedback" id="feedback" rows="4" required class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">{{ old('feedback') }}</textarea>
                    @error('feedback')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="photo" class="block text-sm font-medium text-slate-400 mb-1">Photo (optional)</label>
                    <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/gif,image/webp" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-slate-700 file:text-slate-200 file:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    <p class="text-slate-500 text-xs mt-1">Upload client photo (JPG, PNG, GIF, WebP). Max 2 MB.</p>
                    @error('photo')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-900 text-sky-500 focus:ring-sky-500">
                        <span class="text-sm font-medium text-slate-400">Active (show on guest dashboard)</span>
                    </label>
                </div>
                <div class="pt-2 flex gap-3">
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium">Create</button>
                    <a href="{{ route('testimonials.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
