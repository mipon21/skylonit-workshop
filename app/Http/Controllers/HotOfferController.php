<?php

namespace App\Http\Controllers;

use App\Models\HotOffer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HotOfferController extends Controller
{
    public function index(): View
    {
        $hotOffers = HotOffer::orderByDesc('created_at')->paginate(20);
        return view('hot-offers.index', compact('hotOffers'));
    }

    public function create(): View
    {
        return view('hot-offers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'cta_text' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        HotOffer::create($validated);
        return redirect()->route('hot-offers.index')->with('success', 'Hot offer created.');
    }

    public function edit(HotOffer $hotOffer): View
    {
        return view('hot-offers.edit', compact('hotOffer'));
    }

    public function update(Request $request, HotOffer $hotOffer): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'cta_text' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $hotOffer->update($validated);
        return redirect()->route('hot-offers.index')->with('success', 'Hot offer updated.');
    }

    public function destroy(HotOffer $hotOffer): RedirectResponse
    {
        $hotOffer->delete();
        return redirect()->route('hot-offers.index')->with('success', 'Hot offer deleted.');
    }
}
