<?php

namespace App\Observers;

use App\Jobs\SyncPaymentToSheetJob;
use App\Models\Payment;
use App\Models\ProjectActivity;
use App\Services\InvoiceService;

class PaymentObserver
{
    public function __construct(protected InvoiceService $invoiceService)
    {
    }

    public function created(Payment $payment): void
    {
        $amount = number_format($payment->amount, 0);
        ProjectActivity::log(
            $payment->project_id,
            'payment_created',
            "Payment ৳{$amount} created",
            ProjectActivity::VISIBILITY_CLIENT
        );
    }

    public function saved(Payment $payment): void
    {
        // Sync to Google Sheets
        SyncPaymentToSheetJob::dispatch($payment);

        if ($payment->wasChanged('payment_status') && $payment->payment_status === Payment::PAYMENT_STATUS_PAID) {
            $amount = number_format($payment->amount, 0);
            ProjectActivity::log(
                $payment->project_id,
                'payment_marked_paid',
                "Payment ৳{$amount} marked as PAID",
                ProjectActivity::VISIBILITY_CLIENT
            );
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
