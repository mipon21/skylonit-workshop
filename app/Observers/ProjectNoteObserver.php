<?php

namespace App\Observers;

use App\Jobs\SyncNoteToSheetJob;
use App\Models\ProjectActivity;
use App\Models\ProjectNote;

class ProjectNoteObserver
{
    public function created(ProjectNote $note): void
    {
        $title = str_replace("'", "\'", $note->title);
        $visibility = $note->visibility === 'client' ? ProjectActivity::VISIBILITY_CLIENT : ProjectActivity::VISIBILITY_INTERNAL;
        ProjectActivity::log(
            $note->project_id,
            'note_created',
            "Note '{$title}' created",
            $visibility
        );
    }

    public function saved(ProjectNote $note): void
    {
        SyncNoteToSheetJob::dispatch($note);

        if ($note->wasChanged() && ! $note->wasRecentlyCreated) {
            $title = str_replace("'", "\'", $note->title);
            $visibility = $note->visibility === 'client' ? ProjectActivity::VISIBILITY_CLIENT : ProjectActivity::VISIBILITY_INTERNAL;
            ProjectActivity::log(
                $note->project_id,
                'note_updated',
                "Note '{$title}' updated",
                $visibility
            );
        }
    }
}
