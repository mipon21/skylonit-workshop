<?php

namespace App\Http\Controllers;

use App\Events\TaskStatusUpdated;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:todo,doing,done'],
            'priority' => ['required', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date'],
            'is_public' => ['nullable', 'boolean'],
        ]);
        $validated['is_public'] = $request->boolean('is_public', true);
        $project->tasks()->create($validated);
        return redirect()->route('projects.show', $project)->withFragment('tasks')->with('success', 'Task added.');
    }

    public function update(Request $request, Project $project, Task $task): RedirectResponse
    {
        if ($task->project_id !== $project->id) {
            abort(404);
        }
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'required', 'in:todo,doing,done'],
            'priority' => ['sometimes', 'required', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date'],
            'is_public' => ['nullable', 'boolean'],
            'send_email' => ['nullable', 'boolean'],
        ]);
        $oldStatus = $task->status;
        unset($validated['send_email']);
        if (array_key_exists('is_public', $validated)) {
            $validated['is_public'] = $request->boolean('is_public');
        }
        $task->update($validated);
        $newStatus = $task->fresh()->status;
        event(new TaskStatusUpdated($task->fresh(), $request->boolean('send_email'), $oldStatus, $newStatus));

        return redirect()->route('projects.show', $project)->withFragment('tasks')->with('success', 'Task updated.');
    }

    public function destroy(Project $project, Task $task): RedirectResponse
    {
        if ($task->project_id !== $project->id) {
            abort(404);
        }
        $task->delete();
        return redirect()->route('projects.show', $project)->withFragment('tasks')->with('success', 'Task deleted.');
    }
}
