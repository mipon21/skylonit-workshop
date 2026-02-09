<?php

namespace App\Listeners;

use App\Events\LinkCreated;
use App\Jobs\SendTemplateMailJob;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendLinkCreatedNotification implements ShouldQueue
{
    /** Notify client when Admin or Developer creates a link (not when client creates). */
    public function handle(LinkCreated $event): void
    {
        if (! $event->link->visible_to_client) {
            return;
        }
        $creator = $event->link->created_by ? User::find($event->link->created_by) : null;
        if (! $creator || $creator->isClient()) {
            return;
        }
        // Developer: always notify client. Admin: respect send_email checkbox.
        if ($creator->isAdmin() && ! $event->sendEmail) {
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
