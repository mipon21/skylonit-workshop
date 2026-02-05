<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Project;
use App\Services\UddoktaPayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected UddoktaPayService $uddoktaPay)
    {
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
        // Admin can also skip the link and use "Mark as Paid (Cash)" instead.
        $validated['payment_status'] = Payment::PAYMENT_STATUS_DUE;
        $validated['gateway'] = Payment::GATEWAY_UDDOKTAPAY;
        $validated['status'] = Payment::STATUS_DUE;

        $project->payments()->create($validated);

        return redirect()->route('projects.show', $project)->withFragment('payments')
            ->with('success', 'Payment added as DUE. Use "Generate Payment Link" when you need a link to share, or "Mark as Paid (Cash)" for offline payment.');
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

        $redirectUrl = url('/client/payment/success');
        $cancelUrl = url('/client/payment/cancel');
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
}
