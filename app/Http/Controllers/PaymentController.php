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
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'in:' . implode(',', Payment::PAYMENT_METHODS)],
            'payment_date' => ['nullable', 'date'],
            'status' => ['required', 'in:upcoming,due,completed'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);
        $project->payments()->create($validated);
        return redirect()->route('projects.show', $project)->withFragment('payments')->with('success', 'Payment added.');
    }

    public function destroy(Project $project, Payment $payment): RedirectResponse
    {
        $payment->delete();
        return redirect()->route('projects.show', $project)->withFragment('payments')->with('success', 'Payment removed.');
    }
}
