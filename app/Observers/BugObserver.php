<?php

namespace App\Observers;

use App\Jobs\SyncBugToSheetJob;
use App\Models\Bug;

class BugObserver
{
    public function saved(Bug $bug): void
    {
        SyncBugToSheetJob::dispatch($bug);
    }
}
