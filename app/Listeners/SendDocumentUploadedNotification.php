<?php

namespace App\Listeners;

use App\Events\DocumentUploaded;
use App\Jobs\SendTemplateMailJob;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendDocumentUploadedNotification implements ShouldQueue
{
    /** Notify client when Admin or Developer uploads a document (not when client uploads). */
    public function handle(DocumentUploaded $event): void
    {
        if (! $event->document->is_public) {
            return;
        }
        $uploader = $event->document->uploaded_by_user_id ? User::find($event->document->uploaded_by_user_id) : null;
        if (! $uploader || $uploader->isClient()) {
            return;
        }
        // Developer upload: always notify client (form has no send_email checkbox for developers). Admin: respect checkbox.
        if ($uploader->isAdmin() && ! $event->sendEmail) {
            return;
        }

        $document = $event->document->load(['project.client']);
        $project = $document->project;
        $client = $project->client;
        if (! $client) {
            return;
        }

        $email = $client->user?->email ?? $client->email;
        if (! $email) {
            return;
        }

        SendTemplateMailJob::dispatch(
            'client_document_uploaded',
            $email,
            [
                'client_name' => $client->name,
                'client_email' => $email,
                'project_name' => $project->project_name,
                'project_code' => $project->project_code ?? '',
                'document_name' => $document->title,
                'login_url' => route('login'),
            ]
        );
    }
}
