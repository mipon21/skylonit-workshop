<div x-show="taskModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4">
        <div x-show="taskModal" x-transition class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="taskModal = false"></div>
        <div x-show="taskModal" x-transition class="relative w-full max-w-md bg-slate-800 border border-slate-700 rounded-2xl shadow-xl p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Add Task</h2>
            <form action="{{ route('projects.tasks.store', $project) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Title *</label>
                        <input type="text" name="title" required class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        @error('title')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Description</label>
                        <textarea name="description" rows="2" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Status</label>
                            <select name="status" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500">
                                <option value="todo">To Do</option>
                                <option value="doing">Doing</option>
                                <option value="done">Done</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Priority</label>
                            <select name="priority" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Due date</label>
                        <input type="date" name="due_date" class="w-full rounded-xl bg-slate-900 border border-slate-600 text-white px-4 py-2.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    </div>
                    <div class="pt-2 border-t border-slate-700/50">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="send_email" value="1" {{ old('send_email') ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-900 text-sky-500 focus:ring-sky-500">
                            <span class="text-sm font-medium text-slate-400">Send Email Notification?</span>
                        </label>
                        <p class="text-slate-500 text-xs mt-1">If checked and status is set to Done, client is notified.</p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="taskModal = false" class="px-4 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700">Cancel</button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-medium">Add Task</button>
                </div>
            </form>
        </div>
    </div>
</div>
