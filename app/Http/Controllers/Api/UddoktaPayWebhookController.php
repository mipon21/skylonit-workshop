<?php

namespace App\Http\Controllers\Api;

use App\Events\PaymentSuccess;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\InvoiceService;
use App\Services\UddoktaPayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UddoktaPayWebhookController extends Controller
{
    public function __construct(
        protected UddoktaPayService $uddoktaPay,
        protected InvoiceService $invoiceService
    ) {
    }

    /**
     * UddoktaPay IPN webhook. Validate API key, find payment, verify via API, mark PAID and generate invoice.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $apiKey = $request->header('RT-UDDOKTAPAY-API-KEY');
        if ($apiKey !== config('services.uddoktapay.api_key') || $apiKey === '') {
            Log::warning('UddoktaPay webhook: invalid or missing API key.');
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $invoiceId = $request->input('invoice_id');
        $metadata = $request->input('metadata', []);
        $paymentId = $metadata['payment_id'] ?? $request->input('payment_id');

        $payment = null;
        if ($paymentId) {
            $payment = Payment::find($paymentId);
        }
        if (! $payment && $invoiceId) {
            $payment = Payment::where('gateway_invoice_id', $invoiceId)->first();
        }

        if (! $payment) {
            Log::warning('UddoktaPay webhook: payment not found.', ['invoice_id' => $invoiceId, 'metadata' => $metadata]);
            return response()->json(['message' => 'Payment not found'], 404);
        }

        if ($payment->payment_status === Payment::PAYMENT_STATUS_PAID) {
            return response()->json(['message' => 'Already processed'], 200);
        }

        $verified = $this->uddoktaPay->verifyPayment($invoiceId ?? $payment->gateway_invoice_id);
        if (! $verified || ($verified['status'] ?? '') !== 'COMPLETED') {
            Log::warning('UddoktaPay webhook: verify did not return COMPLETED.', ['payment_id' => $payment->id, 'verified' => $verified]);
            return response()->json(['message' => 'Payment not completed'], 400);
        }

        $payment->update([
            'payment_status' => Payment::PAYMENT_STATUS_PAID,
            'paid_at' => now(),
            'paid_method' => Payment::PAID_METHOD_GATEWAY,
            'status' => Payment::STATUS_COMPLETED,
            'gateway_invoice_id' => $invoiceId ?? $payment->gateway_invoice_id,
        ]);

        if (! $payment->invoice) {
            $this->invoiceService->generateInvoice($payment);
        }

        event(new PaymentSuccess($payment->fresh()));

        return response()->json(['message' => 'OK'], 200);
    }
}
