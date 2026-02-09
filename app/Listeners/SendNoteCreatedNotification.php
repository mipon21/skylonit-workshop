<?php

namespace App\Listeners;

use App\Events\NoteCreated;
use App\Jobs\SendTemplateMailJob;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNoteCreatedNotification implements ShouldQueue
{
    /** Notify client when Admin or Developer creates a note (not when client creates). */
    public function handle(NoteCreated $event): void
    {
        if (($event->note->visibility ?? '') !== 'client') {
            return;
        }
        $creator = $event->note->created_by ? User::find($event->note->created_by) : null;
        if (! $creator || $creator->isClient()) {
            return;
        }
        // Developer: always notify client. Admin: respect send_email checkbox.
        if ($creator->isAdmin() && ! $event->sendEmail) {
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
