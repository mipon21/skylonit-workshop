<?php

namespace App\Listeners;

use App\Events\BugAssigned;
use App\Jobs\SendTemplateMailJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendBugAssignedNotification implements ShouldQueue
{
    public function handle(BugAssigned $event): void
    {
        $bug = $event->bug->load(['project']);
        $user = $event->assignedTo;
        if (! $user->email || $user->role !== 'developer') {
            return;
        }

        $project = $bug->project;
        SendTemplateMailJob::dispatch(
            'developer_bug_assigned',
            $user->email,
            [
                'name' => $user->name,
                'project_name' => $project->project_name,
                'project_code' => $project->project_code ?? '',
                'bug_title' => $bug->title,
                'login_url' => route('login'),
                'project_url' => route('projects.show', $project),
            ]
        );
    }
}
