<?php

namespace App\Observers;

use App\Jobs\SyncPaymentToSheetJob;
use App\Models\Payment;

class PaymentObserver
{
    public function saved(Payment $payment): void
    {
        SyncPaymentToSheetJob::dispatch($payment);
    }
}
