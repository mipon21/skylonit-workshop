@foreach($project->bugs as $bug)
<div x-show="bugEditModal === {{ $bug->id }}" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4 max-md:p-0 max-md:items-stretch">
        <div x-show="bugEditModal === {{ $bug->id }}" x-transition class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="bugEditModal = null"></div>
        <div x-show="bugEditModal === {{ $bug->id }}" x-transition class="relative w-full max-w-md theme-bg-tertiary border theme-border rounded-2xl shadow-xl p-6 max-md:max-w-none max-md:max-h-full max-md:rounded-none max-md:border-0">
            <h2 class="text-lg font-semibold theme-text-primary mb-4">Edit Bug</h2>
            <form action="{{ route('projects.bugs.update', [$project, $bug]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Title *</label>
                        <input type="text" name="title" value="{{ old('title', $bug->title) }}" required class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                        @error('title')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Description</label>
                        <textarea name="description" rows="3" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">{{ old('description', $bug->description) }}</textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium theme-text-secondary mb-1">Severity</label>
                            <select name="severity" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 focus:ring-2 focus:ring-orange-500">
                                <option value="minor" {{ $bug->severity === 'minor' ? 'selected' : '' }}>Minor</option>
                                <option value="major" {{ $bug->severity === 'major' ? 'selected' : '' }}>Major</option>
                                <option value="critical" {{ $bug->severity === 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium theme-text-secondary mb-1">Status</label>
                            <select name="status" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 focus:ring-2 focus:ring-orange-500">
                                <option value="open" {{ $bug->status === 'open' ? 'selected' : '' }}>Open</option>
                                <option value="in_progress" {{ $bug->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="resolved" {{ $bug->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            </select>
                        </div>
                    </div>
                    @if(($developersForAssign ?? collect())->isNotEmpty())
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Assign to Developer</label>
                        <select name="assigned_to_user_id" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 focus:ring-2 focus:ring-orange-500">
                            <option value="">— None —</option>
                            @foreach($developersForAssign as $dev)
                                <option value="{{ $dev->id }}" {{ old('assigned_to_user_id', $bug->assigned_to_user_id) == $dev->id ? 'selected' : '' }}>{{ $dev->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="pt-2 border-t theme-border">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="is_public" value="0">
                            <input type="checkbox" name="is_public" value="1" {{ old('is_public', $bug->is_public ?? true) ? 'checked' : '' }} class="rounded theme-border theme-input-bg text-orange-500 focus:ring-orange-500">
                            <span class="text-sm font-medium theme-text-secondary">Show on public (guest) portal</span>
                        </label>
                    </div>
                    <div class="pt-2 border-t theme-border">
                        <label class="flex items-center gap-2 cursor-pointer">
                            @if(($isDeveloper ?? false) || ($isClient ?? false))
                            <input type="hidden" name="send_email" value="1">
                            <p class="theme-text-muted text-xs">Email notification will be sent when relevant.</p>
                            @else
                            <input type="checkbox" name="send_email" value="1" {{ old('send_email', false) ? 'checked' : '' }} class="rounded theme-border theme-input-bg text-orange-500 focus:ring-orange-500">
                            @endif
                            <span class="text-sm font-medium theme-text-secondary">Send Email Notification?</span>
                        </label>
                        <p class="theme-text-muted text-xs mt-1">If checked and status is set to Resolved, client is notified.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Attachment</label>
                        @if($bug->attachment_path)
                            <p class="theme-text-muted text-xs mb-1">Current file attached. Upload a new file to replace, or check below to remove.</p>
                            <label class="inline-flex items-center gap-2 theme-text-secondary text-sm">
                                <input type="hidden" name="remove_attachment" value="0">
                                <input type="checkbox" name="remove_attachment" value="1" class="rounded theme-border theme-input-bg text-orange-500 focus:ring-orange-500">
                                Remove attachment
                            </label>
                        @endif
                        <input type="file" name="attachment" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.zip,.txt" class="mt-2 w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 file:mr-3 file:py-1.5 file:rounded-lg file:border-0 file:theme-bg-tertiary file:theme-text-secondary text-sm">
                        <p class="theme-text-muted text-xs mt-1">Optional. Max 10MB.</p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="bugEditModal = null" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover">Cancel</button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 theme-text-primary font-medium">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
