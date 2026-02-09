<?php

namespace App\Observers;

use App\Jobs\SyncPaymentToSheetJob;
use App\Models\ClientNotification;
use App\Models\Payment;
use App\Models\ProjectActivity;
use App\Observers\ProjectActivityObserver;
use App\Services\InvoiceService;

class PaymentObserver
{
    public function __construct(protected InvoiceService $invoiceService)
    {
    }

    public function created(Payment $payment): void
    {
        $amount = number_format($payment->amount, 0);
        $activity = ProjectActivity::log(
            $payment->project_id,
            'payment_created',
            "Payment ৳{$amount} created",
            ProjectActivity::VISIBILITY_CLIENT
        );
        $projectName = $payment->project?->project_name ?? 'your project';
        ProjectActivityObserver::createNotificationsForProjectClients($payment->project_id, [
            'activity_id' => $activity->id,
            'type' => ClientNotification::TYPE_PAYMENT,
            'title' => 'Payment created',
            'message' => "Payment ৳{$amount} is due for {$projectName}.",
            'payment_id' => $payment->id,
            'link' => $payment->payment_link ?: null,
        ]);
    }

    public function saved(Payment $payment): void
    {
        // Sync to Google Sheets
        SyncPaymentToSheetJob::dispatch($payment);

        // When a payment link is generated (DUE payment), notify project clients so the popup shows
        if ($payment->wasChanged('payment_link') && $payment->payment_link && $payment->payment_status === Payment::PAYMENT_STATUS_DUE) {
            $amount = number_format($payment->amount, 0);
            $projectName = $payment->project?->project_name ?? 'your project';
            ProjectActivityObserver::createNotificationsForProjectClients($payment->project_id, [
                'type' => ClientNotification::TYPE_PAYMENT,
                'title' => 'Payment link ready',
                'message' => "You can now pay ৳{$amount} for {$projectName}. Use the Pay Now button below.",
                'payment_id' => $payment->id,
                'link' => $payment->payment_link,
            ]);
        }

        if ($payment->wasChanged('payment_status') && $payment->payment_status === Payment::PAYMENT_STATUS_PAID) {
            $amount = number_format($payment->amount, 0);
            $activity = ProjectActivity::log(
                $payment->project_id,
                'payment_marked_paid',
                "Payment ৳{$amount} marked as PAID",
                ProjectActivity::VISIBILITY_CLIENT
            );
            ProjectActivity::log(
                $payment->project_id,
                'payment_marked_paid',
                'Payment marked as PAID',
                ProjectActivity::VISIBILITY_DEVELOPER_SALES
            );
            $projectName = $payment->project?->project_name ?? 'your project';
            ProjectActivityObserver::createNotificationsForProjectClients($payment->project_id, [
                'activity_id' => $activity->id,
                'type' => ClientNotification::TYPE_PAYMENT,
                'title' => 'Payment received',
                'message' => "Payment ৳{$amount} has been marked as PAID for {$projectName}.",
                'payment_id' => $payment->id,
                'link' => $payment->relationLoaded('invoice') && $payment->invoice
                    ? route('invoices.view', $payment->invoice)
                    : null,
            ]);
        }

        // Generate invoice only when payment is PAID and no invoice yet
        if ($payment->payment_status === Payment::PAYMENT_STATUS_PAID && ! $payment->invoice) {
            $this->invoiceService->generateInvoice($payment);
            return;
        }

        // Regenerate invoice if it exists (for updates, e.g. amount/date change)
        if ($payment->invoice) {
            $this->invoiceService->regenerateInvoice($payment->invoice);
        }
    }
}
