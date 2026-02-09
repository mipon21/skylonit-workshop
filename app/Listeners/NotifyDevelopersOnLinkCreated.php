<?php

namespace App\Listeners;

use App\Events\LinkCreated;
use App\Jobs\SendTemplateMailJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyDevelopersOnLinkCreated implements ShouldQueue
{
    /** Notify project developers when a link visible to them is created (visible_to_client or visible_to_developer). */
    public function handle(LinkCreated $event): void
    {
        $link = $event->link;
        $visibleToDeveloper = $link->visible_to_client
            || (isset($link->visible_to_developer) && $link->visible_to_developer);
        if (! $visibleToDeveloper) {
            return;
        }

        $link->load(['project.developers']);
        $project = $link->project;
        foreach ($project->developers as $user) {
            if (! $user->email) {
                continue;
            }
            SendTemplateMailJob::dispatch(
                'developer_link_added',
                $user->email,
                [
                    'name' => $user->name,
                    'project_name' => $project->project_name,
                    'project_code' => $project->project_code ?? '',
                    'link_label' => $link->label ?? $link->url,
                    'project_url' => route('projects.show', $project),
                ]
            );
        }
    }
}
