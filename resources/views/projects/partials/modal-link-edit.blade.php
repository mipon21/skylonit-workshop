@foreach($project->projectLinks as $link)
<div x-show="linkEditModal === {{ $link->id }}" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4">
        <div x-show="linkEditModal === {{ $link->id }}" x-transition class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="linkEditModal = null"></div>
        <div x-show="linkEditModal === {{ $link->id }}" x-transition class="relative w-full max-w-md bg-slate-800 border border-slate-700 rounded-2xl shadow-xl p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Edit Link</h2>
            <form action="{{ route('projects.links.update', [$project, $link]) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Label *</label>
                        <input type="text" name="label" value="{{ old('label', $link->label) }}" required class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500" placeholder="e.g. Admin Panel, Staging">
                        @error('label')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">URL *</label>
                        <input type="url" name="url" value="{{ old('url', $link->url) }}" required class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500" placeholder="https://...">
                        @error('url')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Login username (optional)</label>
                        <input type="text" name="login_username" value="{{ old('login_username', $link->login_username) }}" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500" placeholder="Username for this link">
                        @error('login_username')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Login password (optional)</label>
                        <input type="text" name="login_password" value="{{ old('login_password', $link->login_password) }}" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500" placeholder="Password for this link">
                        @error('login_password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-slate-400">Visibility</span>
                        <label class="relative inline-flex items-center cursor-pointer gap-2 flex-shrink-0">
                            <input type="hidden" name="is_public" value="0">
                            <input type="checkbox" name="is_public" value="1" {{ old('is_public', $link->is_public ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <span class="relative inline-block h-6 w-11 shrink-0 rounded-full border border-slate-500 bg-slate-600 transition-colors peer-focus:ring-2 peer-focus:ring-sky-500/50 peer-checked:bg-sky-500" aria-hidden="true"></span>
                            <span class="pointer-events-none absolute left-1 top-1 h-4 w-4 rounded-full border border-slate-400 bg-white shadow transition-transform duration-200 peer-checked:translate-x-5" aria-hidden="true"></span>
                            <span class="text-sm text-slate-300">{{ ($link->is_public ?? true) ? 'Public (anyone can see)' : 'Private (admin only)' }}</span>
                        </label>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="linkEditModal = null" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700">Cancel</button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
