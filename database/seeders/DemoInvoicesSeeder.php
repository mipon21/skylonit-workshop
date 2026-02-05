<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Services\InvoiceService;
use Illuminate\Database\Seeder;

class DemoInvoicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $invoiceService = app(InvoiceService::class);

        // Get all payments that don't have invoices yet
        $payments = Payment::whereDoesntHave('invoice')->get();

        $this->command->info("Found {$payments->count()} payments without invoices.");

        foreach ($payments as $payment) {
            try {
                $invoice = $invoiceService->generateInvoice($payment);
                $this->command->info("Generated invoice {$invoice->invoice_number} for payment ID {$payment->id}");
            } catch (\Exception $e) {
                $this->command->error("Failed to generate invoice for payment ID {$payment->id}: {$e->getMessage()}");
            }
        }

        $this->command->info('Demo invoices seeding completed!');
    }
}
