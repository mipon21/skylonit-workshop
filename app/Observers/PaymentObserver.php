<?php

namespace App\Observers;

use App\Jobs\SyncPaymentToSheetJob;
use App\Models\Payment;
use App\Services\InvoiceService;

class PaymentObserver
{
    public function __construct(protected InvoiceService $invoiceService)
    {
    }

    public function created(Payment $payment): void
    {
        // Invoice is generated ONLY when payment_status becomes PAID (webhook or manual mark-paid)
        // Do not generate invoice on create; payment starts as DUE.
    }

    public function saved(Payment $payment): void
    {
        // Sync to Google Sheets
        SyncPaymentToSheetJob::dispatch($payment);

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
