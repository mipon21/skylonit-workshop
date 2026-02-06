<?php

namespace App\Observers;

use App\Jobs\SyncBugToSheetJob;
use App\Models\Bug;
use App\Models\ProjectActivity;

class BugObserver
{
    public function created(Bug $bug): void
    {
        $title = str_replace("'", "\'", $bug->title);
        ProjectActivity::log(
            $bug->project_id,
            'bug_created',
            "Bug '{$title}' created",
            ProjectActivity::VISIBILITY_CLIENT
        );
    }

    public function saved(Bug $bug): void
    {
        SyncBugToSheetJob::dispatch($bug);

        if ($bug->wasChanged('status') && ! $bug->wasRecentlyCreated) {
            $title = str_replace("'", "\'", $bug->title);
            $statusLabel = str_replace('_', ' ', ucfirst($bug->status));
            ProjectActivity::log(
                $bug->project_id,
                'bug_status_changed',
                "Bug '{$title}' marked as {$statusLabel}",
                ProjectActivity::VISIBILITY_CLIENT
            );
        }
    }
}
