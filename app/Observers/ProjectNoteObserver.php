<?php

namespace App\Observers;

use App\Jobs\SyncNoteToSheetJob;
use App\Models\ProjectNote;

class ProjectNoteObserver
{
    public function saved(ProjectNote $note): void
    {
        SyncNoteToSheetJob::dispatch($note);
    }
}
