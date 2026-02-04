<?php

namespace App\Observers;

use App\Jobs\SyncProjectToSheetJob;
use App\Models\Project;

class ProjectObserver
{
    public function saved(Project $project): void
    {
        SyncProjectToSheetJob::dispatch($project);
    }
}
