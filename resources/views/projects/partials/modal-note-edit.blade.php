@foreach($project->projectNotes as $note)
<div x-show="noteEditModal === {{ $note->id }}" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4 max-md:p-0 max-md:items-stretch">
        <div x-show="noteEditModal === {{ $note->id }}" x-transition class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="noteEditModal = null"></div>
        <div x-show="noteEditModal === {{ $note->id }}" x-transition class="relative w-full max-w-md bg-slate-800 border border-slate-700 rounded-2xl shadow-xl p-6 max-md:max-w-none max-md:max-h-full max-md:rounded-none max-md:border-0">
            <h2 class="text-lg font-semibold text-white mb-4">Edit Note</h2>
            <form action="{{ route('projects.notes.update', [$project, $note]) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Title *</label>
                        <input type="text" name="title" value="{{ old('title', $note->title) }}" required class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        @error('title')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Body</label>
                        <textarea name="body" rows="5" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">{{ old('body', $note->body) }}</textarea>
                    </div>
                    @if($isDeveloper ?? false)
                    <input type="hidden" name="visibility" value="{{ $note->visibility ?? 'client' }}">
                    @else
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-slate-400">Visibility</span>
                        <select name="visibility" class="rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500">
                            <option value="client" {{ ($note->visibility ?? 'client') === 'client' ? 'selected' : '' }}>Public (admin, client, developer)</option>
                            <option value="internal_developer" {{ ($note->visibility ?? '') === 'internal_developer' ? 'selected' : '' }}>Admin & Developer only</option>
                            <option value="internal" {{ ($note->visibility ?? '') === 'internal' ? 'selected' : '' }}>Private (admin only)</option>
                        </select>
                    </div>
                    @endif
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="noteEditModal = null" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700">Cancel</button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
