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
        // Generate invoice when payment is created
        $this->invoiceService->generateInvoice($payment);
    }

    public function saved(Payment $payment): void
    {
        // Sync to Google Sheets
        SyncPaymentToSheetJob::dispatch($payment);

        // Regenerate invoice if it exists (for updates)
        if ($payment->invoice) {
            $this->invoiceService->regenerateInvoice($payment->invoice);
        }
    }
}
