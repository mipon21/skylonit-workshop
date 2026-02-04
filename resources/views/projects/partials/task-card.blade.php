@props(['task', 'project'])
<div class="bg-slate-900/60 border border-slate-700/50 rounded-xl overflow-hidden transition-all duration-200 hover:border-slate-600"
     :class="{ 'ring-1 ring-sky-500/30': expandedTaskId == {{ $task->id }} }">
    <button type="button" @click="expandedTaskId = expandedTaskId == {{ $task->id }} ? null : {{ $task->id }}" class="w-full text-left p-3">
        <p class="font-medium text-white text-sm">{{ $task->title }}</p>
        @if($task->due_date)
            <p class="text-slate-500 text-xs mt-1">Due {{ $task->due_date->format('M d, Y') }}</p>
        @endif
        <div class="flex flex-wrap items-center justify-between gap-2 mt-2">
            <span @class([
                'px-2 py-0.5 rounded text-xs font-medium',
                'bg-red-500/20 text-red-400' => $task->priority === 'high',
                'bg-amber-500/20 text-amber-400' => $task->priority === 'medium',
                'bg-slate-500/20 text-slate-400' => $task->priority === 'low',
            ])>{{ ucfirst($task->priority) }}</span>
        </div>
    </button>
    <div x-show="expandedTaskId == {{ $task->id }}" x-transition class="px-3 pb-3 border-t border-slate-700/50">
        <div class="pt-2 text-slate-300 text-sm whitespace-pre-wrap">{{ $task->description ?: '—' }}</div>
        <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
            <form action="{{ route('projects.tasks.update', [$project, $task]) }}" method="POST" class="inline">
                @csrf
                @method('PATCH')
                <input type="hidden" name="title" value="{{ $task->title }}">
                <input type="hidden" name="description" value="{{ $task->description }}">
                <input type="hidden" name="priority" value="{{ $task->priority }}">
                <input type="hidden" name="due_date" value="{{ $task->due_date?->format('Y-m-d') }}">
                <select name="status" onchange="this.form.submit()" class="rounded bg-slate-800 border border-slate-600 text-white text-xs px-2 py-1">
                    <option value="todo" {{ $task->status === 'todo' ? 'selected' : '' }}>To Do</option>
                    <option value="doing" {{ $task->status === 'doing' ? 'selected' : '' }}>Doing</option>
                    <option value="done" {{ $task->status === 'done' ? 'selected' : '' }}>Done</option>
                </select>
            </form>
            <div class="flex items-center gap-2">
                <button type="button" @click="taskEditModal = {{ $task->id }}" class="text-sky-400 hover:text-sky-300 text-xs">Edit</button>
                <form action="{{ route('projects.tasks.destroy', [$project, $task]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this task?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-400 hover:text-red-300 text-xs">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
