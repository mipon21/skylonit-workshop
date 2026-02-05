<?php

namespace App\Listeners;

use App\Events\ExpenseCreated;
use App\Jobs\SendTemplateMailJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendExpenseCreatedNotification implements ShouldQueue
{
    public function handle(ExpenseCreated $event): void
    {
        if (! $event->sendEmail) {
            return;
        }
        if (! $event->expense->is_public) {
            return;
        }

        $expense = $event->expense->load(['project.client']);
        $project = $expense->project;
        $client = $project->client;
        if (! $client) {
            return;
        }

        $email = $client->user?->email ?? $client->email;
        if (! $email) {
            return;
        }

        SendTemplateMailJob::dispatch(
            'client_expense_created',
            $email,
            [
                'client_name' => $client->name,
                'client_email' => $email,
                'project_name' => $project->project_name,
                'project_code' => $project->project_code ?? '',
                'expense_amount' => number_format($expense->amount, 2),
                'login_url' => route('login'),
            ]
        );
    }
}
