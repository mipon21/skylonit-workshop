<?php

namespace App\Listeners;

use App\Events\TaskStatusUpdated;
use App\Jobs\SendTemplateMailJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTaskDoneNotification implements ShouldQueue
{
    public function handle(TaskStatusUpdated $event): void
    {
        if (! $event->sendEmail) {
            return;
        }
        if ($event->newStatus !== 'done') {
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
