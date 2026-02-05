<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Project;
use Illuminate\Database\Seeder;

class UddoktaPayDemoPaymentsSeeder extends Seeder
{
    /**
     * Seed demo payments: one DUE (with fake link), one PAID via gateway, one PAID via cash.
     */
    public function run(): void
    {
        $project = Project::has('client')->first();
        if (! $project) {
            return;
        }

        $baseCreated = now()->subDays(10);

        // DUE payment (UddoktaPay link would be set by real API)
        if (! $project->payments()->where('payment_status', Payment::PAYMENT_STATUS_DUE)->exists()) {
            $project->payments()->create([
                'payment_type' => Payment::TYPE_FIRST,
                'amount' => round($project->contract_amount * 0.2, 2),
                'payment_date' => $baseCreated,
                'status' => Payment::STATUS_DUE,
                'gateway' => Payment::GATEWAY_UDDOKTAPAY,
                'payment_status' => Payment::PAYMENT_STATUS_DUE,
                'payment_link' => 'https://sandbox.uddoktapay.com/demo-checkout',
                'gateway_invoice_id' => null,
                'note' => 'First (DUE – demo)',
            ]);
        }

        // PAID via gateway
        if (! $project->payments()->where('paid_method', Payment::PAID_METHOD_GATEWAY)->exists()) {
            $project->payments()->create([
                'payment_type' => Payment::TYPE_MIDDLE,
                'amount' => round($project->contract_amount * 0.3, 2),
                'payment_date' => $baseCreated->copy()->subDays(5),
                'status' => Payment::STATUS_COMPLETED,
                'gateway' => Payment::GATEWAY_UDDOKTAPAY,
                'payment_status' => Payment::PAYMENT_STATUS_PAID,
                'gateway_invoice_id' => 'demo-inv-gateway',
                'paid_at' => $baseCreated->copy()->subDays(5),
                'paid_method' => Payment::PAID_METHOD_GATEWAY,
                'note' => 'Middle (PAID – gateway)',
            ]);
        }

        // PAID via cash (manual)
        if (! $project->payments()->where('paid_method', Payment::PAID_METHOD_CASH)->exists()) {
            $project->payments()->create([
                'payment_type' => Payment::TYPE_FINAL,
                'amount' => round($project->contract_amount * 0.5, 2),
                'payment_date' => $baseCreated->copy()->subDays(2),
                'status' => Payment::STATUS_COMPLETED,
                'gateway' => Payment::GATEWAY_MANUAL,
                'payment_status' => Payment::PAYMENT_STATUS_PAID,
                'paid_at' => $baseCreated->copy()->subDays(2),
                'paid_method' => Payment::PAID_METHOD_CASH,
                'note' => 'Final (PAID – cash)',
            ]);
        }
    }
}
