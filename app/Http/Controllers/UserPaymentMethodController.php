<?php

namespace App\Http\Controllers;

use App\Models\UserPaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPaymentMethodController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if (! Auth::user()->isDeveloper() && ! Auth::user()->isSales()) {
            abort(403, 'Only developers and sales can add payment methods here.');
        }
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:bank,mobile_banking,other'],
            'label' => ['nullable', 'string', 'max:255'],
            'details' => ['nullable', 'string', 'max:2000'],
        ]);
        $validated['user_id'] = Auth::id();
        $validated['sort_order'] = Auth::user()->paymentMethods()->max('sort_order') + 1;
        UserPaymentMethod::create($validated);
        return redirect()->route('profile.edit')->with('status', 'payment-method-added');
    }

    public function update(Request $request, UserPaymentMethod $userPaymentMethod): RedirectResponse
    {
        if ($userPaymentMethod->user_id !== Auth::id()) {
            abort(403);
        }
        if (! Auth::user()->isDeveloper() && ! Auth::user()->isSales()) {
            abort(403);
        }
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:bank,mobile_banking,other'],
            'label' => ['nullable', 'string', 'max:255'],
            'details' => ['nullable', 'string', 'max:2000'],
        ]);
        $userPaymentMethod->update($validated);
        return redirect()->route('profile.edit')->with('status', 'payment-method-updated');
    }

    public function destroy(UserPaymentMethod $userPaymentMethod): RedirectResponse
    {
        if ($userPaymentMethod->user_id !== Auth::id()) {
            abort(403);
        }
        if (! Auth::user()->isDeveloper() && ! Auth::user()->isSales()) {
            abort(403);
        }
        $userPaymentMethod->delete();
        return redirect()->route('profile.edit')->with('status', 'payment-method-removed');
    }
}
