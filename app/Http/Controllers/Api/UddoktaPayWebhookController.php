<?php

namespace App\Http\Controllers\Api;

use App\Events\PaymentSuccess;
use App\Events\SupportPackagePaymentSuccess;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\SupportPackage;
use App\Services\InvoiceService;
use App\Services\SupportInvoiceService;
use App\Services\UddoktaPayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UddoktaPayWebhookController extends Controller
{
    public function __construct(
        protected UddoktaPayService $uddoktaPay,
        protected InvoiceService $invoiceService,
        protected SupportInvoiceService $supportInvoiceService
    ) {
    }

    /**
     * UddoktaPay IPN webhook. Handles both project payments and support package payments.
     * Metadata support_package_id = support package; metadata payment_id = project payment.
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
        $supportPackageId = $metadata['support_package_id'] ?? null;
        $paymentId = $metadata['payment_id'] ?? $request->input('payment_id');

        if ($supportPackageId) {
            return $this->handleSupportPackagePayment($supportPackageId, $invoiceId);
        }

        return $this->handleProjectPayment($paymentId, $invoiceId, $metadata);
    }

    protected function handleSupportPackagePayment(?string $supportPackageId, ?string $invoiceId): JsonResponse
    {
        $supportPackage = $supportPackageId ? SupportPackage::find($supportPackageId) : null;
        if (! $supportPackage && $invoiceId) {
            $supportPackage = SupportPackage::where('gateway_invoice_id', $invoiceId)->first();
        }

        if (! $supportPackage) {
            Log::warning('UddoktaPay webhook: support package not found.', ['invoice_id' => $invoiceId, 'support_package_id' => $supportPackageId]);
            return response()->json(['message' => 'Support package not found'], 404);
        }

        if ($supportPackage->payment_status === SupportPackage::PAYMENT_STATUS_PAID) {
            return response()->json(['message' => 'Already processed'], 200);
        }

        $verifyId = $invoiceId ?? $supportPackage->gateway_invoice_id;
        if (! $verifyId) {
            return response()->json(['message' => 'Cannot verify without invoice_id'], 400);
        }

        $verified = $this->uddoktaPay->verifyPayment($verifyId);
        if (! $verified || ($verified['status'] ?? '') !== 'COMPLETED') {
            Log::warning('UddoktaPay webhook: support verify not COMPLETED.', ['support_package_id' => $supportPackage->id]);
            return response()->json(['message' => 'Payment not completed'], 400);
        }

        $supportPackage->update([
            'payment_status' => SupportPackage::PAYMENT_STATUS_PAID,
            'paid_at' => now(),
            'gateway_invoice_id' => $invoiceId ?? $supportPackage->gateway_invoice_id,
        ]);

        $this->supportInvoiceService->generateInvoice($supportPackage->fresh());

        \App\Models\ProjectActivity::log(
            $supportPackage->project_id,
            'support_payment_completed',
            "Support package paid: {$supportPackage->package_label} – ৳" . number_format($supportPackage->amount, 0),
            \App\Models\ProjectActivity::VISIBILITY_CLIENT
        );

        event(new SupportPackagePaymentSuccess($supportPackage->fresh()));

        return response()->json(['message' => 'OK'], 200);
    }

    protected function handleProjectPayment($paymentId, ?string $invoiceId, array $metadata): JsonResponse
    {
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
