<?php

namespace App\Listeners;

use App\Events\LinkCreated;
use App\Jobs\SendTemplateMailJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendLinkCreatedNotification implements ShouldQueue
{
    public function handle(LinkCreated $event): void
    {
        if (! $event->sendEmail) {
            return;
        }
        if (! $event->link->is_public) {
            return;
        }

        $link = $event->link->load(['project.client']);
        $project = $link->project;
        $client = $project->client;
        if (! $client) {
            return;
        }

        $email = $client->user?->email ?? $client->email;
        if (! $email) {
            return;
        }

        SendTemplateMailJob::dispatch(
            'client_link_created',
            $email,
            [
                'client_name' => $client->name,
                'client_email' => $email,
                'project_name' => $project->project_name,
                'project_code' => $project->project_code ?? '',
                'link_url' => $link->url,
                'login_url' => route('login'),
            ]
        );
    }
}
