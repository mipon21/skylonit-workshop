<?php

namespace App\Listeners;

use App\Events\DocumentUploaded;
use App\Jobs\SendTemplateMailJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendDocumentUploadedNotification implements ShouldQueue
{
    public function handle(DocumentUploaded $event): void
    {
        if (! $event->sendEmail) {
            return;
        }
        if (! $event->document->is_public) {
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
