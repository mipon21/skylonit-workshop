<div x-show="noteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4 max-md:p-0 max-md:items-stretch">
        <div x-show="noteModal" x-transition class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="noteModal = false"></div>
        <div x-show="noteModal" x-transition class="relative w-full max-w-md theme-bg-tertiary border theme-border rounded-2xl shadow-xl p-6 max-md:max-w-none max-md:max-h-full max-md:rounded-none max-md:border-0">
            <h2 class="text-lg font-semibold theme-text-primary mb-4">New Note</h2>
            <form action="{{ route('projects.notes.store', $project) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Title *</label>
                        <input type="text" name="title" required class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus" placeholder="Note title">
                        @error('title')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Body</label>
                        <textarea name="body" rows="5" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus" placeholder="Markdown supported"></textarea>
                        @error('body')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    @if(($isDeveloper ?? false) || ($isClient ?? false))
                    <input type="hidden" name="send_email" value="1">
                    <p class="theme-text-muted text-xs pt-2 border-t theme-border">Email notification will be sent to the client.</p>
                    @else
                    <div class="pt-2 border-t theme-border">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="send_email" value="1" {{ old('send_email', false) ? 'checked' : '' }} class="rounded theme-border theme-input-bg text-orange-500 focus:ring-orange-500">
                            <span class="text-sm font-medium theme-text-secondary">Send Email Notification?</span>
                        </label>
                    </div>
                    @endif
                    @if(!($isDeveloper ?? false))
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm font-medium theme-text-secondary">Visibility</span>
                        <select name="visibility" class="rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 focus:ring-2 focus:ring-orange-500">
                            <option value="client">Public (admin, client, developer)</option>
                            <option value="internal_developer">Admin & Developer only</option>
                            <option value="internal">Private (admin only)</option>
                        </select>
                    </div>
                    @else
                    <input type="hidden" name="visibility" value="client">
                    @endif
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="noteModal = false" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover">Cancel</button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl theme-btn-primary font-medium">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
