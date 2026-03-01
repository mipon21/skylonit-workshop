<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $isClient = $user->isClient();
        $isDeveloper = $user->isDeveloper();
        $isSales = $user->isSales();
        $client = $isClient ? $user->client : null;
        $paymentMethods = ($isDeveloper || $isSales) ? $user->paymentMethods : collect();

        $logoPath = Setting::get('app_logo');
        $currentLogoUrl = $logoPath ? asset('storage/' . $logoPath) : null;
        $faviconPath = Setting::get('app_favicon');
        $currentFaviconUrl = $faviconPath ? asset('storage/' . $faviconPath) : null;

        $themeColors = [];
        if ($user->isAdmin()) {
            $themeColors = [
                'primary' => Setting::get('theme_primary_color'),
                'secondary' => Setting::get('theme_secondary_color'),
                'button_bg' => Setting::get('theme_button_bg'),
                'button_hover_bg' => Setting::get('theme_button_hover_bg'),
                'button_text' => Setting::get('theme_button_text'),
                'sidebar_active_bg' => Setting::get('theme_sidebar_active_bg'),
                'sidebar_active_text' => Setting::get('theme_sidebar_active_text'),
            ];
        }

        return view('profile.edit', [
            'user' => $user,
            'client' => $client,
            'isClient' => $isClient,
            'isDeveloper' => $isDeveloper,
            'isSales' => $isSales,
            'paymentMethods' => $paymentMethods,
            'currentLogoUrl' => $currentLogoUrl,
            'currentFaviconUrl' => $currentFaviconUrl,
            'themeColors' => $themeColors,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->isClient() && $user->client) {
            // Clients may only update name and address; phone, email, fb_link, kyc are read-only (admin-managed).
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'address' => ['nullable', 'string'],
            ]);
            $user->client->update([
                'name' => $validated['name'],
                'address' => $validated['address'] ?? null,
            ]);
            $user->name = $validated['name'];
            $user->save();
        } else {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', \Illuminate\Validation\Rule::unique(\App\Models\User::class)->ignore($user->id)],
            ]);
            $user->fill($validated);
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }
            $user->save();
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's theme preference (light/dark). JSON for SPA-style toggle.
     */
    public function updateTheme(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'theme' => ['required', 'string', 'in:light,dark'],
        ]);
        $request->user()->update(['theme_preference' => $validated['theme']]);
        if ($request->wantsJson()) {
            return response()->json(['theme' => $validated['theme']]);
        }
        return Redirect::back();
    }

    /**
     * Update the site logo (shown on login and in sidebar).
     */
    public function updateLogo(Request $request): RedirectResponse
    {
        if ($request->user()->isClient()) {
            abort(403, 'Admin only.');
        }
        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpeg,jpg,png,gif,webp,svg', 'max:2048'],
        ]);

        $file = $request->file('logo');
        $path = $file->store('logos', 'public');

        $oldPath = Setting::get('app_logo');
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        Setting::set('app_logo', $path);

        return Redirect::route('profile.edit')->with('status', 'logo-updated');
    }

    /**
     * Update the site favicon (browser tab icon).
     */
    public function updateFavicon(Request $request): RedirectResponse
    {
        if ($request->user()->isClient()) {
            abort(403, 'Admin only.');
        }
        $request->validate([
            'favicon' => ['required', 'file', 'mimes:ico,png,gif,svg', 'max:1024'],
        ]);

        $file = $request->file('favicon');
        $path = $file->store('favicons', 'public');

        $oldPath = Setting::get('app_favicon');
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        Setting::set('app_favicon', $path);

        return Redirect::route('profile.edit')->with('status', 'favicon-updated');
    }

    /**
     * Update theme colors (admin only). Applies globally via CSS variable overrides.
     */
    public function updateThemeColors(Request $request): RedirectResponse
    {
        if (!$request->user()->isAdmin()) {
            abort(403, 'Admin only.');
        }

        $keys = [
            'theme_primary_color',
            'theme_secondary_color',
            'theme_button_bg',
            'theme_button_hover_bg',
            'theme_button_text',
            'theme_sidebar_active_bg',
            'theme_sidebar_active_text',
        ];

        $validated = $request->validate([
            'primary' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'button_bg' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'button_hover_bg' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'button_text' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sidebar_active_bg' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sidebar_active_text' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $map = [
            'primary' => 'theme_primary_color',
            'secondary' => 'theme_secondary_color',
            'button_bg' => 'theme_button_bg',
            'button_hover_bg' => 'theme_button_hover_bg',
            'button_text' => 'theme_button_text',
            'sidebar_active_bg' => 'theme_sidebar_active_bg',
            'sidebar_active_text' => 'theme_sidebar_active_text',
        ];

        foreach ($map as $input => $key) {
            $value = $validated[$input] ?? '';
            Setting::set($key, $value !== '' ? $value : '');
        }

        return Redirect::route('profile.edit')->with('status', 'theme-colors-updated');
    }

    /**
     * Reset theme colors to defaults (admin only).
     */
    public function resetThemeColors(Request $request): RedirectResponse
    {
        if (!$request->user()->isAdmin()) {
            abort(403, 'Admin only.');
        }
        $keys = [
            'theme_primary_color', 'theme_secondary_color', 'theme_button_bg', 'theme_button_hover_bg',
            'theme_button_text', 'theme_sidebar_active_bg', 'theme_sidebar_active_text',
        ];
        foreach ($keys as $key) {
            Setting::set($key, '');
        }
        return Redirect::route('profile.edit')->with('status', 'theme-colors-reset');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        // Client: unlink so the Client record remains for admin (user_id becomes null via FK)
        if ($user->isClient() && $user->client) {
            $user->client->update(['user_id' => null]);
        }
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
