<?php

namespace App\Observers;

use App\Events\ProjectStatusChanged;
use App\Jobs\SyncProjectToSheetJob;
use App\Models\Project;
use App\Models\ProjectActivity;

class ProjectObserver
{
    public function created(Project $project): void
    {
        ProjectActivity::log(
            $project->id,
            'project_created',
            'Project created',
            ProjectActivity::VISIBILITY_INTERNAL
        );
    }

    public function saved(Project $project): void
    {
        SyncProjectToSheetJob::dispatch($project);

        if ($project->wasChanged('status')) {
            $oldStatus = $project->getOriginal('status');
            ProjectActivity::log(
                $project->id,
                'project_status_changed',
                'Project status changed to ' . $project->status,
                ProjectActivity::VISIBILITY_INTERNAL
            );
            event(new ProjectStatusChanged($project->fresh(), $oldStatus, $project->status));
        }
    }
}
