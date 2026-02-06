<?php

namespace App\Observers;

use App\Jobs\SyncDocumentToSheetJob;
use App\Models\Document;
use App\Models\ProjectActivity;

class DocumentObserver
{
    public function created(Document $document): void
    {
        $title = str_replace("'", "\'", $document->title);
        $visibility = $document->is_public ? ProjectActivity::VISIBILITY_CLIENT : ProjectActivity::VISIBILITY_INTERNAL;
        ProjectActivity::log(
            $document->project_id,
            'document_uploaded',
            "Document '{$title}' uploaded",
            $visibility
        );
    }

    public function saved(Document $document): void
    {
        SyncDocumentToSheetJob::dispatch($document);
    }

    public function deleted(Document $document): void
    {
        $title = str_replace("'", "\'", $document->title);
        $visibility = $document->is_public ? ProjectActivity::VISIBILITY_CLIENT : ProjectActivity::VISIBILITY_INTERNAL;
        ProjectActivity::log(
            $document->project_id,
            'document_deleted',
            "Document '{$title}' deleted",
            $visibility
        );
    }
}
