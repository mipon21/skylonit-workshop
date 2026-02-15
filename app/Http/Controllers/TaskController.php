<?php

namespace App\Http\Controllers;

use App\Events\TaskAssigned;
use App\Events\TaskStatusUpdated;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'assigned_to_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'milestone_id' => ['nullable', 'integer', 'exists:milestones,id'],
        ]);
        $validated['is_public'] = $request->boolean('is_public', true);
        $validated['assigned_to_user_id'] = $request->input('assigned_to_user_id') ?: null;
        $validated['milestone_id'] = $request->input('milestone_id') ?: null;
        if ($validated['milestone_id']) {
            $m = \App\Models\Milestone::find($validated['milestone_id']);
            if (! $m || $m->project_id !== $project->id) {
                $validated['milestone_id'] = null;
            }
        }
        if (in_array($validated['status'] ?? 'todo', ['doing', 'done'], true)) {
            $validated['status_updated_at'] = now();
        }
        $task = $project->tasks()->create($validated);
        $assigneeId = $task->assigned_to_user_id;
        if ($assigneeId) {
            $assignee = User::find($assigneeId);
            if ($assignee && $assignee->isDeveloper()) {
                event(new TaskAssigned($task->load('project'), $assignee));
            }
        }
        return redirect()->route('projects.show', $project)->withFragment('tasks')->with('success', 'Task added.');
    }

    public function update(Request $request, Project $project, Task $task): RedirectResponse
    {
        if ($task->project_id !== $project->id) {
            abort(404);
        }
        $user = Auth::user();
        $isDeveloper = $user->isDeveloper();
        if ($isDeveloper) {
            if ($task->assigned_to_user_id !== $user->id) {
                abort(403, 'You can only update tasks assigned to you.');
            }
            $validated = $request->validate([
                'status' => ['required', 'in:todo,doing,done'],
            ]);
            $oldStatus = $task->status;
            $newStatus = $validated['status'];
            $validated['status_updated_at'] = in_array($newStatus, ['doing', 'done'], true) ? now() : $task->status_updated_at;
            $task->update($validated);
            event(new TaskStatusUpdated($task->fresh(), false, $oldStatus, $newStatus, $user->id));
            return redirect()->route('projects.show', $project)->withFragment('tasks')->with('success', 'Task status updated.');
        }
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'required', 'in:todo,doing,done'],
            'priority' => ['sometimes', 'required', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date'],
            'is_public' => ['nullable', 'boolean'],
            'send_email' => ['nullable', 'boolean'],
            'assigned_to_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'milestone_id' => ['nullable', 'integer', 'exists:milestones,id'],
        ]);
        $oldStatus = $task->status;
        $newStatus = $validated['status'] ?? $oldStatus;
        if ($newStatus !== $oldStatus && in_array($newStatus, ['doing', 'done'], true)) {
            $validated['status_updated_at'] = now();
        }
        unset($validated['send_email']);
        $validated['milestone_id'] = $request->input('milestone_id') ?: null;
        if (isset($validated['milestone_id']) && $validated['milestone_id']) {
            $m = \App\Models\Milestone::find($validated['milestone_id']);
            if (! $m || $m->project_id !== $project->id) {
                $validated['milestone_id'] = null;
            }
        }
        if (array_key_exists('is_public', $validated)) {
            $validated['is_public'] = $request->boolean('is_public');
        }
        $previousAssigneeId = $task->assigned_to_user_id;
        $validated['assigned_to_user_id'] = $request->input('assigned_to_user_id') ?: null;
        $task->update($validated);
        $newStatus = $task->fresh()->status ?? $newStatus;
        event(new TaskStatusUpdated($task->fresh(), $request->boolean('send_email'), $oldStatus, $newStatus, Auth::id()));

        $newAssigneeId = $task->fresh()->assigned_to_user_id;
        if ($newAssigneeId && $newAssigneeId !== $previousAssigneeId) {
            $assignee = User::find($newAssigneeId);
            if ($assignee && $assignee->isDeveloper()) {
                event(new TaskAssigned($task->fresh()->load('project'), $assignee));
            }
        }

        return redirect()->route('projects.show', $project)->withFragment('tasks')->with('success', 'Task updated.');
    }

    public function destroy(Project $project, Task $task): RedirectResponse
    {
        if ($task->project_id !== $project->id) {
            abort(404);
        }
        if (Auth::user()->isDeveloper() || Auth::user()->isSales()) {
            abort(403, 'Only admin can delete tasks.');
        }
        $task->delete();
        return redirect()->route('projects.show', $project)->withFragment('tasks')->with('success', 'Task deleted.');
    }
}
