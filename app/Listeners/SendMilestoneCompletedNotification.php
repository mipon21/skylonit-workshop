<?php

namespace App\Listeners;

use App\Events\TaskStatusUpdated;
use App\Jobs\SendTemplateMailJob;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SendMilestoneCompletedNotification
{
    /** When the last task in a milestone is marked done, mark milestone complete and notify the client. */
    public function handle(TaskStatusUpdated $event): void
    {
        if ($event->newStatus !== 'done') {
            return;
        }
        $task = $event->task->fresh(['milestone', 'project.client', 'project.additionalClients']);
        if (! $task->milestone_id || ! $task->milestone) {
            return;
        }
        $updater = $event->updatedByUserId ? User::find($event->updatedByUserId) : null;
        if (! $updater || $updater->isClient()) {
            return;
        }
        $milestone = $task->milestone;
        if ($milestone->completed_at) {
            return;
        }
        $taskCount = $milestone->tasks()->count();
        if ($taskCount === 0 || $milestone->tasks()->where('status', '!=', 'done')->exists()) {
            return;
        }

        $project = $task->project;
        $clientIds = collect([$project->client_id])
            ->merge($project->additionalClients()->pluck('clients.id'))
            ->filter()
            ->unique();

        $emailSent = false;
        foreach ($clientIds as $clientId) {
            $client = Client::find($clientId);
            if (! $client) {
                continue;
            }
            $email = $client->user?->email ?? $client->email;
            if (! $email) {
                Log::info('Milestone completed but client has no email', ['client_id' => $clientId, 'milestone' => $milestone->name]);
                continue;
            }
            try {
                // Use client_task_done template (known to work) with milestone as "task" for reliable delivery
                SendTemplateMailJob::dispatchSync(
                    'client_task_done',
                    $email,
                    [
                        'client_name' => $client->name,
                        'client_email' => $email,
                        'project_name' => $project->project_name,
                        'project_code' => $project->project_code ?? '',
                        'task_title' => 'Milestone "' . $milestone->name . '" completed',
                        'login_url' => route('login'),
                    ]
                );
                $emailSent = true;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // Mark milestone complete only after sending (so it can retry if email failed)
        if ($emailSent) {
            $milestone->update(['completed_at' => now()]);
        }
    }
}
