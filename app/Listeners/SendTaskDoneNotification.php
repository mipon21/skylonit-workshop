<?php

namespace App\Listeners;

use App\Events\TaskStatusUpdated;
use App\Jobs\SendTemplateMailJob;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTaskDoneNotification implements ShouldQueue
{
    /** Notify client when Admin or Developer marks the task as done (not when client does). */
    public function handle(TaskStatusUpdated $event): void
    {
        if ($event->newStatus !== 'done') {
            return;
        }
        $updater = $event->updatedByUserId ? User::find($event->updatedByUserId) : null;
        if (! $updater || $updater->isClient()) {
            return;
        }
        $task = $event->task->load(['project.client']);
        $project = $task->project;
        $client = $project->client;
        if (! $client) {
            return;
        }

        $email = $client->user?->email ?? $client->email;
        if (! $email) {
            return;
        }

        SendTemplateMailJob::dispatch(
            'client_task_done',
            $email,
            [
                'client_name' => $client->name,
                'client_email' => $email,
                'project_name' => $project->project_name,
                'project_code' => $project->project_code ?? '',
                'task_title' => $task->title,
                'login_url' => route('login'),
            ]
        );
    }
}
