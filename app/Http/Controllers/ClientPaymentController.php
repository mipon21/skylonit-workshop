<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\InvoiceService;
use App\Services\UddoktaPayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ClientPaymentController extends Controller
{
    public function __construct(
        protected UddoktaPayService $uddoktaPay,
        protected InvoiceService $invoiceService
    ) {
    }

    /**
     * Client payments list (own projects only).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $client = $user->client;
        if (! $client) {
            abort(403, 'You must be associated with a client account.');
        }

        $payments = Payment::whereHas('project', fn ($q) => $q->where('client_id', $client->id))
            ->with('project')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('client-payments.index', compact('payments'));
    }

    /**
     * Success redirect: verify payment and mark PAID if COMPLETED, then redirect to Invoices.
     */
    public function success(Request $request): RedirectResponse|View
    {
        $invoiceId = $request->query('invoice_id');
        if (! $invoiceId) {
            return redirect()->route('client.payments.index')->with('error', 'Invalid return from payment.');
        }

        $payment = Payment::where('gateway_invoice_id', $invoiceId)->first();
        if (! $payment) {
            return redirect()->route('client.payments.index')->with('error', 'Payment not found.');
        }

        $user = $request->user();
        $client = $user->client;
        if (! $client || $payment->project->client_id !== $client->id) {
            abort(403, 'Unauthorized.');
        }

        if ($payment->payment_status === Payment::PAYMENT_STATUS_PAID) {
            return redirect()->route('invoices.index')->with('success', 'Payment already recorded. Thank you!');
        }

        $verified = $this->uddoktaPay->verifyPayment($invoiceId);
        if (! $verified || ($verified['status'] ?? '') !== 'COMPLETED') {
            return redirect()->route('client.payments.index')->with('error', 'Payment could not be verified. Please contact support if amount was deducted.');
        }

        $payment->update([
            'payment_status' => Payment::PAYMENT_STATUS_PAID,
            'paid_at' => now(),
            'paid_method' => Payment::PAID_METHOD_GATEWAY,
            'status' => Payment::STATUS_COMPLETED,
        ]);

        if (! $payment->invoice) {
            $this->invoiceService->generateInvoice($payment);
        }

        return redirect()->route('invoices.index')->with('success', 'Payment received. Your invoice is ready.');
    }

    /**
     * Cancel redirect.
     */
    public function cancel(): RedirectResponse
    {
        return redirect()->route('client.payments.index')->with('info', 'Payment was cancelled.');
    }
}
