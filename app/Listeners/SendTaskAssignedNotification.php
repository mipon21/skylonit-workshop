<?php

namespace App\Listeners;

use App\Events\TaskAssigned;
use App\Jobs\SendTemplateMailJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTaskAssignedNotification implements ShouldQueue
{
    public function handle(TaskAssigned $event): void
    {
        $task = $event->task->load(['project']);
        $user = $event->assignedTo;
        if (! $user->email || $user->role !== 'developer') {
            return;
        }

        $project = $task->project;
        SendTemplateMailJob::dispatch(
            'developer_task_assigned',
            $user->email,
            [
                'name' => $user->name,
                'project_name' => $project->project_name,
                'project_code' => $project->project_code ?? '',
                'task_title' => $task->title,
                'login_url' => route('login'),
                'project_url' => route('projects.show', $project),
            ]
        );
    }
}
