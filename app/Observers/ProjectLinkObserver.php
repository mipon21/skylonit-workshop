<?php

namespace App\Observers;

use App\Models\ProjectActivity;
use App\Models\ProjectLink;

class ProjectLinkObserver
{
    public function created(ProjectLink $link): void
    {
        $label = str_replace("'", "\'", $link->label);
        $visibility = $link->is_public ? ProjectActivity::VISIBILITY_CLIENT : ProjectActivity::VISIBILITY_INTERNAL;
        ProjectActivity::log(
            $link->project_id,
            'link_created',
            "Link '{$label}' created",
            $visibility
        );
    }

    public function saved(ProjectLink $link): void
    {
        if ($link->wasChanged() && ! $link->wasRecentlyCreated) {
            $label = str_replace("'", "\'", $link->label);
            $visibility = $link->is_public ? ProjectActivity::VISIBILITY_CLIENT : ProjectActivity::VISIBILITY_INTERNAL;
            ProjectActivity::log(
                $link->project_id,
                'link_updated',
                "Link '{$label}' updated",
                $visibility
            );
        }
    }
}
