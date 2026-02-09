<?php

namespace App\Listeners;

use App\Events\PayoutStatusChanged;
use App\Jobs\SendTemplateMailJob;
use App\Models\ProjectPayout;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPayoutStatusChangedNotification implements ShouldQueue
{
    public function handle(PayoutStatusChanged $event): void
    {
        $payout = $event->payout->load('project.developers', 'project.sales');
        $project = $payout->project;
        if (! $project) {
            return;
        }

        $templateKey = null;
        $users = collect();

        if ($payout->type === ProjectPayout::TYPE_DEVELOPER) {
            $templateKey = 'developer_payout_updated';
            $users = $project->developers;
        } elseif ($payout->type === ProjectPayout::TYPE_SALES) {
            $templateKey = 'sales_payout_updated';
            $users = $project->sales;
        }

        if (! $templateKey || $users->isEmpty()) {
            return;
        }

        $statusLabel = ProjectPayout::statusLabel($payout->status);
        $typeLabel = ProjectPayout::typeLabel($payout->type);

        foreach ($users as $user) {
            if (! $user->email) {
                continue;
            }
            SendTemplateMailJob::dispatch(
                $templateKey,
                $user->email,
                [
                    'name' => $user->name,
                    'project_name' => $project->project_name,
                    'project_code' => $project->project_code ?? '',
                    'payout_type' => $typeLabel,
                    'payout_status' => $statusLabel,
                    'login_url' => route('login'),
                    'project_url' => route('projects.show', $project),
                ]
            );
        }
    }
}
