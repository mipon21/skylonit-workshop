<?php

namespace App\Services;

use App\Models\SupportPackage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class SupportInvoiceService
{
    /**
     * Generate invoice PDF for a paid support package. Idempotent.
     */
    public function generateInvoice(SupportPackage $supportPackage): string
    {
        if ($supportPackage->invoice_path && Storage::exists($supportPackage->invoice_path)) {
            return $supportPackage->invoice_path;
        }

        $project = $supportPackage->project;
        $client = $supportPackage->client;

        $invoiceNumber = $this->generateInvoiceNumber();

        $supportPackage->update([
            'invoice_number' => $invoiceNumber,
        ]);

        $pdfPath = $this->generatePdf($supportPackage, $project, $client);

        $supportPackage->update(['invoice_path' => $pdfPath]);

        return $pdfPath;
    }

    protected function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $last = SupportPackage::whereNotNull('invoice_number')
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        $next = $last ? (intval(preg_replace('/^SUP-\d+-/', '', $last->invoice_number ?? '0')) + 1) : 1;
        return sprintf('SUP-%s-%04d', $year, $next);
    }

    protected function generatePdf(SupportPackage $supportPackage, $project, $client): string
    {
        $data = [
            'supportPackage' => $supportPackage,
            'project' => $project,
            'client' => $client,
        ];

        $pdf = Pdf::loadView('invoices.png-template-support', $data);
        $pdf->getDomPDF()->setHttpContext(stream_context_create([
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
        ]));
        $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
        $pdf->setPaper('a4', 'portrait');

        $filename = sprintf('support_invoice_%s_%s.pdf', $supportPackage->invoice_number, now()->format('YmdHis'));
        $path = 'invoices/support/' . $filename;

        if (! Storage::exists('invoices/support')) {
            Storage::makeDirectory('invoices/support');
        }

        Storage::put($path, $pdf->output());

        return $path;
    }
}
