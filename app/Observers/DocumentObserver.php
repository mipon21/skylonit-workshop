<?php

namespace App\Observers;

use App\Jobs\SyncDocumentToSheetJob;
use App\Models\Document;

class DocumentObserver
{
    public function saved(Document $document): void
    {
        SyncDocumentToSheetJob::dispatch($document);
    }
}
