<?php

namespace App\Listeners;

use App\Events\BugStatusUpdated;
use App\Jobs\SendTemplateMailJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendBugResolvedNotification implements ShouldQueue
{
    public function handle(BugStatusUpdated $event): void
    {
        if (! $event->sendEmail) {
            return;
        }
        if ($event->newStatus !== 'resolved') {
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
