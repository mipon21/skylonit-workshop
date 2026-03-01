<div x-show="bugModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4 max-md:p-0 max-md:items-stretch">
        <div x-show="bugModal" x-transition class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="bugModal = false"></div>
        <div x-show="bugModal" x-transition class="relative w-full max-w-md theme-bg-tertiary border theme-border rounded-2xl shadow-xl p-6 max-md:max-w-none max-md:max-h-full max-md:rounded-none max-md:border-0">
            <h2 class="text-lg font-semibold theme-text-primary mb-4">Report Bug</h2>
            <form action="{{ route('projects.bugs.store', $project) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Title *</label>
                        <input type="text" name="title" required class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus" placeholder="Short description">
                        @error('title')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Description</label>
                        <textarea name="description" rows="3" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus" placeholder="Steps to reproduce, etc."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Attachment</label>
                        <input type="file" name="attachment" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.zip,.txt" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 file:mr-3 file:py-1.5 file:rounded-lg file:border-0 file:theme-bg-tertiary file:theme-text-secondary text-sm">
                        <p class="theme-text-muted text-xs mt-1">Optional. PDF, doc, docx, png, jpg, zip, txt. Max 10MB.</p>
                        @error('attachment')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Severity</label>
                        <select name="severity" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 focus:ring-2 focus:ring-orange-500">
                            <option value="minor">Minor</option>
                            <option value="major">Major</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                    @if(($developersForAssign ?? collect())->isNotEmpty())
                    @php $defaultBugDeveloperId = $developersForAssign->count() === 1 ? $developersForAssign->first()->id : null; @endphp
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Assign to Developer</label>
                        <select name="assigned_to_user_id" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 focus:ring-2 focus:ring-orange-500">
                            <option value="" {{ old('assigned_to_user_id', $defaultBugDeveloperId) === '' || old('assigned_to_user_id', $defaultBugDeveloperId) === null ? 'selected' : '' }}>— None —</option>
                            @foreach($developersForAssign as $dev)
                                <option value="{{ $dev->id }}" {{ old('assigned_to_user_id', $defaultBugDeveloperId) == $dev->id ? 'selected' : '' }}>{{ $dev->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="pt-2 border-t theme-border">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="is_public" value="0">
                            <input type="checkbox" name="is_public" value="1" checked class="rounded theme-border theme-input-bg text-orange-500 focus:ring-orange-500">
                            <span class="text-sm font-medium theme-text-secondary">Show on public (guest) portal</span>
                        </label>
                    </div>
                    <input type="hidden" name="status" value="open">
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="bugModal = false" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover">Cancel</button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl theme-btn-primary font-medium">Report</button>
                </div>
            </form>
        </div>
    </div>
</div>
