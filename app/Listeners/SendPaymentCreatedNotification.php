<?php

namespace App\Listeners;

use App\Events\PaymentCreated;
use App\Jobs\SendTemplateMailJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPaymentCreatedNotification implements ShouldQueue
{
    public function handle(PaymentCreated $event): void
    {
        if (! $event->sendEmail) {
            return;
        }

        $payment = $event->payment->load(['project.client']);
        $project = $payment->project;
        $client = $project->client;
        if (! $client) {
            return;
        }

        $email = $client->user?->email ?? $client->email;
        if (! $email) {
            return;
        }

        SendTemplateMailJob::dispatch(
            'client_payment_created',
            $email,
            [
                'client_name' => $client->name,
                'client_email' => $email,
                'project_name' => $project->project_name,
                'project_code' => $project->project_code ?? '',
                'payment_amount' => number_format($payment->amount, 2),
                'payment_link' => $payment->payment_link ?? route('client.payments.index'),
                'login_url' => route('login'),
            ]
        );
    }
}
