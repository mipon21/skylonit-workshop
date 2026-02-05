<?php

namespace App\Listeners;

use App\Events\NoteCreated;
use App\Jobs\SendTemplateMailJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNoteCreatedNotification implements ShouldQueue
{
    public function handle(NoteCreated $event): void
    {
        if (! $event->sendEmail) {
            return;
        }
        if (($event->note->visibility ?? '') !== 'client') {
            return;
        }

        $note = $event->note->load(['project.client']);
        $project = $note->project;
        $client = $project->client;
        if (! $client) {
            return;
        }

        $email = $client->user?->email ?? $client->email;
        if (! $email) {
            return;
        }

        SendTemplateMailJob::dispatch(
            'client_note_created',
            $email,
            [
                'client_name' => $client->name,
                'client_email' => $email,
                'project_name' => $project->project_name,
                'project_code' => $project->project_code ?? '',
                'note_title' => $note->title,
                'login_url' => route('login'),
            ]
        );
    }
}
