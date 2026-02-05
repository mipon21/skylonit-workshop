<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Console\Command;

class TestInvoiceGeneration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:test {payment_id? : The payment ID to generate invoice for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test invoice generation with SVG template';

    /**
     * Execute the console command.
     */
    public function handle(InvoiceService $invoiceService)
    {
        $paymentId = $this->argument('payment_id');

        if ($paymentId) {
            $payment = Payment::with(['project.client'])->find($paymentId);
            
            if (!$payment) {
                $this->error("Payment with ID {$paymentId} not found.");
                return 1;
            }

            $this->info("Generating invoice for Payment ID: {$paymentId}");
        } else {
            // Find the first payment with completed status
            $payment = Payment::with(['project.client'])
                ->where('status', Payment::STATUS_COMPLETED)
                ->first();

            if (!$payment) {
                $this->error("No completed payments found. Please create a payment first.");
                return 1;
            }

            $this->info("Using Payment ID: {$payment->id}");
        }

        try {
            // Check if invoice already exists
            if ($payment->invoice) {
                $this->warn("Invoice already exists for this payment. Regenerating...");
                $invoice = $invoiceService->regenerateInvoice($payment->invoice);
                $this->info("✓ Invoice regenerated successfully!");
            } else {
                $invoice = $invoiceService->generateInvoice($payment);
                $this->info("✓ Invoice generated successfully!");
            }

            $this->newLine();
            $this->table(
                ['Field', 'Value'],
                [
                    ['Invoice Number', $invoice->invoice_number],
                    ['Invoice Date', $invoice->invoice_date->format('Y-m-d')],
                    ['Project', $payment->project->project_name],
                    ['Client', $payment->project->client->name],
                    ['Amount', '৳ ' . number_format($payment->amount, 2)],
                    ['Payment Status', $invoice->payment_status],
                    ['File Path', $invoice->file_path],
                ]
            );

            $fullPath = storage_path('app/' . $invoice->file_path);
            if (file_exists($fullPath)) {
                $this->info("✓ PDF file created at: {$fullPath}");
                $this->info("✓ File size: " . round(filesize($fullPath) / 1024, 2) . " KB");
            } else {
                $this->error("✗ PDF file not found at expected location!");
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to generate invoice: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
