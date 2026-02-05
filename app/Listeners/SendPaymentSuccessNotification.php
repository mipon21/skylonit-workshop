<?php

namespace App\Listeners;

use App\Events\PaymentSuccess;
use App\Jobs\SendTemplateMailJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;

class SendPaymentSuccessNotification implements ShouldQueue
{
    public function handle(PaymentSuccess $event): void
    {
        $payment = $event->payment->load(['project.client', 'invoice']);
        $project = $payment->project;
        $client = $project->client;
        if (! $client) {
            return;
        }

        $email = $client->user?->email ?? $client->email;
        if (! $email) {
            return;
        }

        $invoiceLink = route('invoices.index');
        $attachmentPath = null;
        $attachmentName = null;
        if ($payment->invoice && $payment->invoice->file_path && Storage::disk('local')->exists($payment->invoice->file_path)) {
            $attachmentPath = $payment->invoice->file_path;
            $attachmentName = 'invoice-' . ($payment->invoice->invoice_number ?? 'payment') . '.pdf';
        }

        SendTemplateMailJob::dispatch(
            'client_payment_success',
            $email,
            [
                'client_name' => $client->name,
                'client_email' => $email,
                'project_name' => $project->project_name,
                'project_code' => $project->project_code ?? '',
                'payment_amount' => number_format($payment->amount, 2),
                'invoice_link' => $invoiceLink,
                'login_url' => route('login'),
            ],
            $attachmentPath,
            $attachmentName
        );
    }
}
