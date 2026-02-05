<?php

namespace App\Listeners;

use App\Events\ProjectCreated;
use App\Jobs\SendTemplateMailJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendProjectCreatedNotification implements ShouldQueue
{
    public function handle(ProjectCreated $event): void
    {
        if (! $event->sendEmail) {
            return;
        }

        $project = $event->project->load('client');
        $client = $project->client;
        if (! $client) {
            return;
        }

        $email = $client->user?->email ?? $client->email;
        if (! $email) {
            return;
        }

        SendTemplateMailJob::dispatch(
            'client_project_created',
            $email,
            [
                'client_name' => $client->name,
                'client_email' => $email,
                'project_name' => $project->project_name,
                'project_code' => $project->project_code ?? '',
                'login_url' => route('login'),
            ]
        );
    }
}
