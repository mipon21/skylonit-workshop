<?php

namespace App\Observers;

use App\Models\ClientNotification;
use App\Models\Invoice;
use App\Models\ProjectActivity;
use App\Observers\ProjectActivityObserver;

class InvoiceObserver
{
    public function created(Invoice $invoice): void
    {
        $activity = ProjectActivity::log(
            $invoice->project_id,
            'invoice_generated',
            'Invoice ' . $invoice->invoice_number . ' generated',
            ProjectActivity::VISIBILITY_CLIENT
        );
        $projectName = $invoice->project?->project_name ?? 'your project';
        ProjectActivityObserver::createNotificationsForProjectClients($invoice->project_id, [
            'activity_id' => $activity->id,
            'type' => ClientNotification::TYPE_PAYMENT,
            'title' => 'Invoice generated',
            'message' => 'Invoice ' . $invoice->invoice_number . ' has been generated for ' . $projectName . '.',
            'invoice_id' => $invoice->id,
            'link' => route('invoices.view', $invoice),
        ]);
    }
}
