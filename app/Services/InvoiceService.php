<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    /**
     * Generate invoice for a payment
     */
    public function generateInvoice(Payment $payment): Invoice
    {
        $project = $payment->project;
        $client = $project->client;

        // Calculate due amount
        $totalPaid = $project->payments()->where('status', Payment::STATUS_COMPLETED)->sum('amount');
        $dueAmount = $project->contract_amount - $totalPaid;

        // Determine payment status
        $paymentStatus = $this->calculatePaymentStatus($project->contract_amount, $totalPaid);

        // Generate invoice number
        $invoiceNumber = Invoice::generateInvoiceNumber();

        // Create invoice record
        $invoice = Invoice::create([
            'project_id' => $project->id,
            'payment_id' => $payment->id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => now(),
            'payment_status' => $paymentStatus,
        ]);

        // Generate PDF
        $pdfPath = $this->generatePdf($invoice, $project, $client, $payment, $dueAmount);

        // Update invoice with file path
        $invoice->update(['file_path' => $pdfPath]);

        return $invoice;
    }

    /**
     * Calculate payment status based on amounts
     */
    protected function calculatePaymentStatus(float $contractAmount, float $totalPaid): string
    {
        if ($totalPaid >= $contractAmount) {
            return Invoice::STATUS_PAID;
        }

        if ($totalPaid > 0 && $totalPaid < $contractAmount) {
            return Invoice::STATUS_PARTIAL;
        }

        return Invoice::STATUS_DUE;
    }

    /**
     * Generate PDF file and store it
     */
    protected function generatePdf(Invoice $invoice, $project, $client, $payment, float $dueAmount): string
    {
        // Prepare data for the view
        $data = [
            'invoice' => $invoice,
            'project' => $project,
            'client' => $client,
            'payment' => $payment,
            'due' => $dueAmount,
        ];

        // Generate PDF using SVG template
        $pdf = Pdf::loadView('invoices.svg-template', $data);
        $pdf->setPaper('a4', 'portrait');

        // Generate filename
        $filename = sprintf('invoice_%s_%s.pdf', $invoice->invoice_number, now()->format('YmdHis'));
        $path = 'invoices/' . $filename;

        // Ensure directory exists
        if (!Storage::exists('invoices')) {
            Storage::makeDirectory('invoices');
        }

        // Save PDF to storage
        Storage::put($path, $pdf->output());

        return $path;
    }

    /**
     * Regenerate invoice PDF (useful for updates)
     */
    public function regenerateInvoice(Invoice $invoice): Invoice
    {
        $payment = $invoice->payment;
        $project = $invoice->project;
        $client = $project->client;

        // Calculate due amount
        $totalPaid = $project->payments()->where('status', Payment::STATUS_COMPLETED)->sum('amount');
        $dueAmount = $project->contract_amount - $totalPaid;

        // Update payment status
        $paymentStatus = $this->calculatePaymentStatus($project->contract_amount, $totalPaid);
        $invoice->update(['payment_status' => $paymentStatus]);

        // Delete old PDF if exists
        if ($invoice->file_path && Storage::exists($invoice->file_path)) {
            Storage::delete($invoice->file_path);
        }

        // Generate new PDF
        $pdfPath = $this->generatePdf($invoice, $project, $client, $payment, $dueAmount);

        // Update invoice with new file path
        $invoice->update(['file_path' => $pdfPath]);

        return $invoice;
    }
}
