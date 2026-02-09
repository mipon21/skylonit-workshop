<?php

namespace App\Listeners;

use App\Events\DocumentUploaded;
use App\Jobs\SendTemplateMailJob;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyDevelopersOnDocumentUploaded implements ShouldQueue
{
    /** Notify project developers when a public document is uploaded (by admin or client). */
    public function handle(DocumentUploaded $event): void
    {
        $document = $event->document;
        if (! $document->is_public) {
            return;
        }
        $uploader = $document->uploaded_by_user_id ? User::find($document->uploaded_by_user_id) : null;
        if (! $uploader) {
            return;
        }
        if ($uploader->isDeveloper()) {
            return;
        }
        $document->load(['project.developers']);
        $project = $document->project;
        foreach ($project->developers as $user) {
            if (! $user->email) {
                continue;
            }
            SendTemplateMailJob::dispatch(
                'developer_document_uploaded',
                $user->email,
                [
                    'name' => $user->name,
                    'project_name' => $project->project_name,
                    'project_code' => $project->project_code ?? '',
                    'document_name' => $document->title,
                    'project_url' => route('projects.show', $project),
                ]
            );
        }
    }
}
