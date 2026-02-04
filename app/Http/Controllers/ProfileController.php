<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
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
        $logoPath = Setting::get('app_logo');
        $currentLogoUrl = $logoPath ? asset('storage/' . $logoPath) : null;
        $faviconPath = Setting::get('app_favicon');
        $currentFaviconUrl = $faviconPath ? asset('storage/' . $faviconPath) : null;

        return view('profile.edit', [
            'user' => $request->user(),
            'currentLogoUrl' => $currentLogoUrl,
            'currentFaviconUrl' => $currentFaviconUrl,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the site logo (shown on login and in sidebar).
     */
    public function updateLogo(Request $request): RedirectResponse
    {
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
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
