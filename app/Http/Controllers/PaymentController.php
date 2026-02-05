<?php

namespace App\Http\Controllers;

use App\Events\PaymentSuccess;
use App\Jobs\SendTemplateMailJob;
use App\Models\Payment;
use App\Models\Project;
use App\Services\InvoiceService;
use App\Services\UddoktaPayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected UddoktaPayService $uddoktaPay,
        protected InvoiceService $invoiceService
    ) {
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        if (config('payment.lock_after_final') && $project->hasFinalPayment()) {
            return redirect()->route('projects.show', $project)->withFragment('payments')
                ->with('error', 'Cannot add more payments after Final Payment.');
        }

        $available = array_keys($project->availablePaymentTypes());
        $validated = $request->validate([
            'payment_type' => ['required', 'string', 'in:' . implode(',', $available)],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'in:' . implode(',', Payment::PAYMENT_METHODS)],
            'payment_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        // Create as DUE only. Link is created on demand when admin clicks "Generate Payment Link".
        // After link is generated, use "Send Email" on that payment to email the link to the client.
        $validated['payment_status'] = Payment::PAYMENT_STATUS_DUE;
        $validated['gateway'] = Payment::GATEWAY_UDDOKTAPAY;
        $validated['status'] = Payment::STATUS_DUE;

        $project->payments()->create($validated);

        return redirect()->route('projects.show', $project)->withFragment('payments')
            ->with('success', 'Payment added as DUE. Generate Payment Link, then use "Send Email" to email the link to the client, or "Mark as Paid (Cash)" for offline payment.');
    }

    public function update(Request $request, Project $project, Payment $payment): RedirectResponse
    {
        if ($payment->project_id !== $project->id) {
            abort(404);
        }
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'in:' . implode(',', Payment::PAYMENT_METHODS)],
            'payment_date' => ['nullable', 'date'],
            'status' => ['required', 'in:upcoming,due,completed'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);
        $payment->update($validated);
        return redirect()->route('projects.show', $project)->withFragment('payments')->with('success', 'Payment updated.');
    }

    public function destroy(Project $project, Payment $payment): RedirectResponse
    {
        if ($payment->project_id !== $project->id) {
            abort(404);
        }
        if (config('payment.lock_after_final') && $project->hasFinalPayment()) {
            return redirect()->route('projects.show', $project)->withFragment('payments')
                ->with('error', 'Cannot remove payments when Final Payment exists.');
        }
        $payment->delete();
        return redirect()->route('projects.show', $project)->withFragment('payments')->with('success', 'Payment removed.');
    }

    /**
     * Mark a DUE payment as paid (cash/offline). Generates invoice immediately.
     */
    public function markAsPaidCash(Project $project, Payment $payment): RedirectResponse
    {
        if ($payment->project_id !== $project->id) {
            abort(404);
        }
        if ($payment->payment_status !== Payment::PAYMENT_STATUS_DUE) {
            return redirect()->route('projects.show', $project)->withFragment('payments')
                ->with('error', 'Only DUE payments can be marked as paid.');
        }

        $payment->update([
            'payment_status' => Payment::PAYMENT_STATUS_PAID,
            'gateway' => Payment::GATEWAY_MANUAL,
            'paid_method' => Payment::PAID_METHOD_CASH,
            'paid_at' => now(),
            'status' => Payment::STATUS_COMPLETED,
        ]);

        if (! $payment->invoice) {
            $this->invoiceService->generateInvoice($payment);
        }
        event(new PaymentSuccess($payment->fresh()));

        return redirect()->route('projects.show', $project)->withFragment('payments')
            ->with('success', 'Payment marked as paid (cash). Invoice generated.');
    }

    /**
     * Generate UddoktaPay payment link for an existing DUE payment that has no link yet.
     */
    public function generateLink(Project $project, Payment $payment): RedirectResponse
    {
        if ($payment->project_id !== $project->id) {
            abort(404);
        }
        if ($payment->payment_status !== Payment::PAYMENT_STATUS_DUE) {
            return redirect()->route('projects.show', $project)->withFragment('payments')
                ->with('error', 'Only DUE payments can have a payment link generated.');
        }
        if ($payment->payment_link) {
            return redirect()->route('projects.show', $project)->withFragment('payments')
                ->with('info', 'This payment already has a link. Use "Copy Payment Link" to copy it.');
        }
        if (! $this->uddoktaPay->isConfigured()) {
            return redirect()->route('projects.show', $project)->withFragment('payments')
                ->with('error', 'UddoktaPay is not configured. Set UDDOKTAPAY_API_KEY in .env and try again.');
        }

        // Gateway must redirect here after payment (guest-friendly). UddoktaPay appends ?invoice_id=...
        $redirectUrl = route('client.payment.success');
        $cancelUrl = route('client.payment.cancel');
        $webhookUrl = url('/api/uddoktapay/webhook');

        $result = $this->uddoktaPay->createCharge($payment, $redirectUrl, $cancelUrl, $webhookUrl);

        if ($result['success']) {
            $payment->update([
                'payment_link' => $result['payment_url'],
                'gateway_invoice_id' => $result['invoice_id'] ?? null,
            ]);
            return redirect()->route('projects.show', $project)->withFragment('payments')
                ->with('success', 'Payment link generated. Use "Copy Payment Link" to share with the client.');
        }

        return redirect()->route('projects.show', $project)->withFragment('payments')
            ->with('error', 'Could not generate link: ' . ($result['message'] ?? 'Unknown error.'));
    }

    /**
     * Send payment link email to client. Only for DUE payments that have a payment link.
     * Can be used multiple times. Hidden once payment is paid.
     */
    public function sendPaymentLinkEmail(Project $project, Payment $payment): RedirectResponse
    {
        if ($payment->project_id !== $project->id) {
            abort(404);
        }
        if ($payment->payment_status !== Payment::PAYMENT_STATUS_DUE || ! $payment->payment_link) {
            return redirect()->route('projects.show', $project)->withFragment('payments')
                ->with('error', 'Payment link email can only be sent for DUE payments that have a payment link.');
        }

        $payment->load(['project.client']);
        $client = $payment->project->client;
        if (! $client) {
            return redirect()->route('projects.show', $project)->withFragment('payments')
                ->with('error', 'Project has no client.');
        }
        $email = $client->user?->email ?? $client->email;
        if (! $email) {
            return redirect()->route('projects.show', $project)->withFragment('payments')
                ->with('error', 'Client has no email address.');
        }

        SendTemplateMailJob::dispatch(
            'client_payment_created',
            $email,
            [
                'client_name' => $client->name,
                'client_email' => $email,
                'project_name' => $payment->project->project_name,
                'project_code' => $payment->project->project_code ?? '',
                'payment_amount' => number_format($payment->amount, 2),
                'payment_link' => $payment->payment_link,
                'login_url' => route('login'),
            ]
        );

        return redirect()->route('projects.show', $project)->withFragment('payments')
            ->with('success', 'Payment link email queued. It will be sent if the template is enabled.');
    }
}
