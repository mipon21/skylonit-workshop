<?php

namespace App\Observers;

use App\Jobs\SyncTaskToSheetJob;
use App\Models\Task;

class TaskObserver
{
    public function saved(Task $task): void
    {
        SyncTaskToSheetJob::dispatch($task);
    }
}
