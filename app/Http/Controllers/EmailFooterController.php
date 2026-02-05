<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailFooterController extends Controller
{
    public function index(): View
    {
        $footer = [
            'email' => Setting::get('footer_email', config('mail.footer.email')),
            'phone' => Setting::get('footer_phone', config('mail.footer.phone')),
            'website' => Setting::get('footer_website', config('mail.footer.website')),
            'facebook' => Setting::get('footer_facebook', config('mail.footer.facebook')),
            'whatsapp' => Setting::get('footer_whatsapp', config('mail.footer.whatsapp')),
            'tagline' => Setting::get('footer_tagline', config('mail.footer.tagline')),
        ];

        return view('email-footer.index', compact('footer'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'footer_email' => ['nullable', 'string', 'max:255'],
            'footer_phone' => ['nullable', 'string', 'max:50'],
            'footer_website' => ['nullable', 'string', 'url', 'max:255'],
            'footer_facebook' => ['nullable', 'string', 'url', 'max:255'],
            'footer_whatsapp' => ['nullable', 'string', 'max:50'],
            'footer_tagline' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value ?? '');
        }

        return redirect()->route('email-footer.index')
            ->with('success', 'Email footer updated. Changes will appear on all outgoing notification emails.');
    }
}
