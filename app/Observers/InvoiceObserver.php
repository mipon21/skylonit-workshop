<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\ProjectActivity;

class InvoiceObserver
{
    public function created(Invoice $invoice): void
    {
        ProjectActivity::log(
            $invoice->project_id,
            'invoice_generated',
            'Invoice ' . $invoice->invoice_number . ' generated',
            ProjectActivity::VISIBILITY_CLIENT
        );
    }
}
