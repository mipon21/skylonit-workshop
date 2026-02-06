<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadController extends Controller
{
    /**
     * Contact / lead form page (guest).
     */
    public function create(): View
    {
        return view('guest.contact');
    }

    /**
     * Store lead from contact form.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'interested_project_type' => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        Lead::create($validated);

        return redirect()->route('guest.contact')->with('success', 'Thank you! We will get back to you soon.');
    }
}
