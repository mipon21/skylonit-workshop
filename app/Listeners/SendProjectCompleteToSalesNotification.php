<?php

namespace App\Listeners;

use App\Events\ProjectStatusChanged;
use App\Jobs\SendTemplateMailJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendProjectCompleteToSalesNotification implements ShouldQueue
{
    public function handle(ProjectStatusChanged $event): void
    {
        if ($event->newStatus !== 'Complete') {
            return;
        }

        $project = $event->project->load('sales');
        foreach ($project->sales as $user) {
            if (! $user->email) {
                continue;
            }
            SendTemplateMailJob::dispatch(
                'sales_project_complete',
                $user->email,
                [
                    'name' => $user->name,
                    'project_name' => $project->project_name,
                    'project_code' => $project->project_code ?? '',
                    'login_url' => route('login'),
                    'project_url' => route('projects.show', $project),
                ]
            );
        }
    }
}
