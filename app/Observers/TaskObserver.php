<?php

namespace App\Observers;

use App\Jobs\SyncTaskToSheetJob;
use App\Models\ProjectActivity;
use App\Models\Task;

class TaskObserver
{
    public function created(Task $task): void
    {
        $title = str_replace("'", "\'", $task->title);
        ProjectActivity::log(
            $task->project_id,
            'task_created',
            "Task '{$title}' created",
            ProjectActivity::VISIBILITY_CLIENT
        );
    }

    public function saved(Task $task): void
    {
        SyncTaskToSheetJob::dispatch($task);

        if ($task->wasChanged('status') && ! $task->wasRecentlyCreated) {
            $title = str_replace("'", "\'", $task->title);
            $statusLabel = ucfirst($task->status);
            ProjectActivity::log(
                $task->project_id,
                'task_status_changed',
                "Task '{$title}' marked as {$statusLabel}",
                ProjectActivity::VISIBILITY_CLIENT
            );
        }
    }
}
