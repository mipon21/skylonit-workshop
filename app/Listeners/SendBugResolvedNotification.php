<?php

namespace App\Listeners;

use App\Events\BugStatusUpdated;
use App\Jobs\SendTemplateMailJob;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendBugResolvedNotification implements ShouldQueue
{
    /** Notify client when Admin or Developer marks the bug as resolved (not when client does). */
    public function handle(BugStatusUpdated $event): void
    {
        if ($event->newStatus !== 'resolved') {
            return;
        }
        $updater = $event->updatedByUserId ? User::find($event->updatedByUserId) : null;
        if (! $updater || $updater->isClient()) {
            return;
        }
        $bug = $event->bug->load(['project.client']);
        $project = $bug->project;
        $client = $project->client;
        if (! $client) {
            return;
        }

        $email = $client->user?->email ?? $client->email;
        if (! $email) {
            return;
        }

        SendTemplateMailJob::dispatch(
            'client_bug_resolved',
            $email,
            [
                'client_name' => $client->name,
                'client_email' => $email,
                'project_name' => $project->project_name,
                'project_code' => $project->project_code ?? '',
                'bug_title' => $bug->title,
                'login_url' => route('login'),
            ]
        );
    }
}
