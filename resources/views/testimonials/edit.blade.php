<x-app-layout>
    <x-slot name="title">Edit Testimonial</x-slot>

    <div class="max-w-2xl">
        <div class="mb-6">
            <a href="{{ route('testimonials.index') }}" class="text-slate-400 hover:text-white text-sm">← Testimonials</a>
            <h1 class="text-2xl font-semibold text-white mt-1">Edit Testimonial</h1>
        </div>
        <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-6">
            <form action="{{ route('testimonials.update', $testimonial) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="client_name" class="block text-sm font-medium text-slate-400 mb-1">Client Name *</label>
                    <input type="text" name="client_name" id="client_name" value="{{ old('client_name', $testimonial->client_name) }}" required class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    @error('client_name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="feedback" class="block text-sm font-medium text-slate-400 mb-1">Feedback *</label>
                    <textarea name="feedback" id="feedback" rows="4" required class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">{{ old('feedback', $testimonial->feedback) }}</textarea>
                    @error('feedback')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="photo" class="block text-sm font-medium text-slate-400 mb-1">Photo URL (optional)</label>
                    <input type="text" name="photo" id="photo" value="{{ old('photo', $testimonial->photo) }}" placeholder="e.g. /images/testimonials/photo.jpg" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    @error('photo')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $testimonial->is_active) ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-900 text-sky-500 focus:ring-sky-500">
                        <span class="text-sm font-medium text-slate-400">Active (show on guest dashboard)</span>
                    </label>
                </div>
                <div class="pt-2 flex gap-3">
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium">Update</button>
                    <a href="{{ route('testimonials.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
