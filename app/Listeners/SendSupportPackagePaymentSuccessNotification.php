<?php

namespace App\Listeners;

use App\Events\SupportPackagePaymentSuccess;
use App\Jobs\SendTemplateMailJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;

class SendSupportPackagePaymentSuccessNotification implements ShouldQueue
{
    public function handle(SupportPackagePaymentSuccess $event): void
    {
        $sp = $event->supportPackage->load(['project.client', 'client']);
        $client = $sp->client;
        if (! $client) {
            return;
        }

        $email = $client->user?->email ?? $client->email;
        if (! $email) {
            return;
        }

        $attachmentPath = null;
        $attachmentName = null;
        if ($sp->invoice_path && Storage::disk('local')->exists($sp->invoice_path)) {
            $attachmentPath = $sp->invoice_path;
            $attachmentName = 'support-invoice-' . ($sp->invoice_number ?? 'support') . '.pdf';
        }

        SendTemplateMailJob::dispatch(
            'client_support_payment_success',
            $email,
            [
                'client_name' => $client->name,
                'client_email' => $email,
                'project_name' => $sp->project->project_name,
                'support_package_label' => $sp->package_label,
                'payment_amount' => number_format($sp->amount, 2),
                'invoice_link' => route('projects.show', $sp->project) . '#support',
                'login_url' => route('login'),
            ],
            $attachmentPath,
            $attachmentName
        );
    }
}
