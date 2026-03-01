<div x-show="taskModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4 max-md:p-0 max-md:items-stretch">
        <div x-show="taskModal" x-transition class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="taskModal = false"></div>
        <div x-show="taskModal" x-transition class="relative w-full max-w-md theme-bg-tertiary border theme-border rounded-2xl shadow-xl p-6 max-md:max-w-none max-md:max-h-full max-md:rounded-none max-md:border-0">
            <h2 class="text-lg font-semibold theme-text-primary mb-4">Add Task</h2>
            <form action="{{ route('projects.tasks.store', $project) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    @if(($project->milestones ?? collect())->isNotEmpty())
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Milestone</label>
                        <select name="milestone_id" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                            <option value="">— No milestone —</option>
                            @foreach($project->milestones as $m)
                                <option value="{{ $m->id }}" {{ old('milestone_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Title *</label>
                        <input type="text" name="title" required class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                        @error('title')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Description</label>
                        <textarea name="description" rows="2" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium theme-text-secondary mb-1">Status</label>
                            <select name="status" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 focus:ring-2 focus:ring-orange-500">
                                <option value="todo">To Do</option>
                                <option value="doing">Doing</option>
                                <option value="done">Done</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium theme-text-secondary mb-1">Priority</label>
                            <select name="priority" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 focus:ring-2 focus:ring-orange-500">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Due date</label>
                        <input type="date" name="due_date" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 theme-input-focus">
                    </div>
                    @if(($developersForAssign ?? collect())->isNotEmpty())
                    @php $defaultTaskDeveloperId = $developersForAssign->count() === 1 ? $developersForAssign->first()->id : null; @endphp
                    <div>
                        <label class="block text-sm font-medium theme-text-secondary mb-1">Assign to Developer</label>
                        <select name="assigned_to_user_id" class="w-full rounded-xl theme-input-bg border theme-border theme-text-primary px-4 py-2.5 focus:ring-2 focus:ring-orange-500">
                            <option value="" {{ old('assigned_to_user_id', $defaultTaskDeveloperId) === '' || old('assigned_to_user_id', $defaultTaskDeveloperId) === null ? 'selected' : '' }}>— None —</option>
                            @foreach($developersForAssign as $dev)
                                <option value="{{ $dev->id }}" {{ old('assigned_to_user_id', $defaultTaskDeveloperId) == $dev->id ? 'selected' : '' }}>{{ $dev->name }}</option>
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
                        <p class="theme-text-muted text-xs mt-1">If checked and status is set to Done, client is notified.</p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3 max-md:flex-col max-md:[&_button]:w-full">
                    <button type="button" @click="taskModal = false" class="px-4 py-2.5 rounded-xl border theme-border theme-text-secondary theme-sidebar-link-hover">Cancel</button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl theme-btn-primary font-medium">Add Task</button>
                </div>
            </form>
        </div>
    </div>
</div>
