<?php

namespace App\Http\Controllers;

use App\Events\PaymentSuccess;
use App\Models\Payment;
use App\Services\InvoiceService;
use App\Services\UddoktaPayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
     * Guests are sent to login by auth middleware. Logged-in users without a client
     * (e.g. admin) are redirected to dashboard with a message instead of 403.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $client = $user->client;
        if (! $client) {
            return redirect()->route('dashboard')
                ->with('info', 'This page is for client accounts only. If you just completed a payment as a guest, it was successful — log in with your client account to view your payments and invoice.');
        }

        $query = Payment::whereHas('project', fn ($q) => $q->forClient($client->id))
            ->with('project.client', 'invoice')
            ->orderByDesc('created_at');

        $search = $request->input('search');
        if ($search && is_string($search)) {
            $term = trim($search);
            if ($term !== '') {
                $query->where(function ($q) use ($term) {
                    $q->whereHas('project', function ($p) use ($term) {
                        $p->where('project_code', 'like', '%' . $term . '%')
                            ->orWhere('project_name', 'like', '%' . $term . '%')
                            ->orWhereHas('client', fn ($c) => $c->where('name', 'like', '%' . $term . '%'));
                    })->orWhereHas('invoice', fn ($i) => $i->where('invoice_number', 'like', '%' . $term . '%'));
                });
            }
        }

        $statusFilter = $request->input('status');
        if ($statusFilter === 'due') {
            $query->where('payment_status', Payment::PAYMENT_STATUS_DUE);
        } elseif ($statusFilter === 'paid') {
            $query->where('payment_status', Payment::PAYMENT_STATUS_PAID);
        }

        $payments = $query->paginate(20)->withQueryString();

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('client-payments.partials.list', compact('payments'));
        }

        return view('client-payments.index', compact('payments'));
    }

    /**
     * Success redirect after gateway payment. Works for guests and logged-in clients.
     * Verifies payment, marks PAID if needed, then shows success or redirects to invoices.
     */
    public function success(Request $request): RedirectResponse|View
    {
        // Gateway may send reference via GET or POST; check common parameter names
        $identifier = $request->input('invoice_id')
            ?? $request->input('order_id')
            ?? $request->input('transaction_id')
            ?? $request->input('payment_id')
            ?? $request->input('reference')
            ?? $request->input('trx_id')
            ?? $request->input('invoice')
            ?? $request->input('id');
        if (! $identifier || ! is_string($identifier)) {
            Log::info('Payment success URL hit without transaction reference.', [
                'query' => $request->query(),
                'request_all' => $request->except(['_token', 'password']),
                'method' => $request->method(),
            ]);
            return $this->successResponse($request, false, 'Invalid return from payment. No transaction reference.', true);
        }

        $payment = Payment::with('project')->where('gateway_invoice_id', $identifier)->first();

        // If we don't have gateway_invoice_id stored (e.g. Create Charge doesn't return it), find payment via verify + metadata
        if (! $payment) {
            $verified = $this->uddoktaPay->verifyPayment($identifier);
            if ($verified && ($verified['status'] ?? '') === 'COMPLETED') {
                $metadata = $verified['metadata'] ?? [];
                $paymentId = $metadata['payment_id'] ?? null;
                if ($paymentId) {
                    $payment = Payment::with('project')->find($paymentId);
                    if ($payment) {
                        $payment->update([
                            'payment_status' => Payment::PAYMENT_STATUS_PAID,
                            'paid_at' => now(),
                            'paid_method' => Payment::PAID_METHOD_GATEWAY,
                            'status' => Payment::STATUS_COMPLETED,
                            'gateway_invoice_id' => $identifier,
                        ]);
                        if (! $payment->invoice) {
                            $this->invoiceService->generateInvoice($payment);
                        }
                        event(new PaymentSuccess($payment->fresh()));
                        $payment->refresh();
                    }
                }
            }
        }

        if (! $payment) {
            return $this->successResponse($request, false, 'Payment not found. If you completed the payment, it may still be processing. Please log in to your client portal or contact support.');
        }

        if ($payment->payment_status !== Payment::PAYMENT_STATUS_PAID) {
            $verified = $this->uddoktaPay->verifyPayment($identifier);
            if ($verified && ($verified['status'] ?? '') === 'COMPLETED') {
                $payment->update([
                    'payment_status' => Payment::PAYMENT_STATUS_PAID,
                    'paid_at' => now(),
                    'paid_method' => Payment::PAID_METHOD_GATEWAY,
                    'status' => Payment::STATUS_COMPLETED,
                    'gateway_invoice_id' => $identifier,
                ]);
                if (! $payment->invoice) {
                    $this->invoiceService->generateInvoice($payment);
                }
                event(new PaymentSuccess($payment->fresh()));
            } else {
                return $this->successResponse($request, false, 'Payment could not be verified yet. If amount was deducted, please contact support or try again shortly.');
            }
        }

        $user = $request->user();
        if ($user && $user->client && $payment->project->hasClientAccess($user->client->id)) {
            return redirect()->route('invoices.index')->with('success', 'Payment received. Your invoice is ready.');
        }

        return $this->successResponse($request, true, 'Payment successful! Log in to your client portal to view and download your invoice.');
    }

    /**
     * Show success or error message. For guests: show a page with login link. For logged-in: redirect to payments with flash.
     * When $showManualEntry is true, show a form so the user can paste their transaction/invoice ID.
     */
    private function successResponse(Request $request, bool $isSuccess, string $message, bool $showManualEntry = false): View|RedirectResponse
    {
        if ($request->user()) {
            $route = $isSuccess ? 'invoices.index' : 'client.payments.index';
            $key = $isSuccess ? 'success' : 'error';
            return redirect()->route($route)->with($key, $message);
        }
        return view('client-payments.return', [
            'success' => $isSuccess,
            'message' => $message,
            'showManualEntry' => $showManualEntry,
        ]);
    }

    /**
     * Cancel redirect (guest or logged-in). Show message and login link for guests.
     */
    public function cancel(Request $request): RedirectResponse|View
    {
        $message = 'Payment was cancelled.';
        if (! $request->user()) {
            return view('client-payments.return', [
                'success' => false,
                'message' => $message . ' Log in to your client portal to try again.',
            ]);
        }
        return redirect()->route('client.payments.index')->with('info', $message);
    }
}
