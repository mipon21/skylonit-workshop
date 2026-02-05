<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
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
            'status' => ['required', 'in:upcoming,due,completed'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);
        $project->payments()->create($validated);
        return redirect()->route('projects.show', $project)->withFragment('payments')->with('success', 'Payment added.');
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
}
