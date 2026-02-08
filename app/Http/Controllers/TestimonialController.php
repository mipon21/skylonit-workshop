<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function index(): View
    {
        $testimonials = Testimonial::orderByDesc('created_at')->paginate(20);
        return view('testimonials.index', compact('testimonials'));
    }

    public function create(): View
    {
        return view('testimonials.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_name' => ['required', 'string', 'max:255'],
            'feedback' => ['required', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('testimonials', 'public');
            $validated['photo'] = 'storage/' . $path;
        } else {
            $validated['photo'] = null;
        }
        Testimonial::create($validated);
        return redirect()->route('testimonials.index')->with('success', 'Testimonial created.');
    }

    public function edit(Testimonial $testimonial): View
    {
        return view('testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $validated = $request->validate([
            'client_name' => ['required', 'string', 'max:255'],
            'feedback' => ['required', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['photo'] = $testimonial->photo;

        if ($request->boolean('remove_photo')) {
            $this->deleteTestimonialPhotoIfExists($testimonial->photo);
            $validated['photo'] = null;
        }
        if ($request->hasFile('photo')) {
            $this->deleteTestimonialPhotoIfExists($testimonial->photo);
            $path = $request->file('photo')->store('testimonials', 'public');
            $validated['photo'] = 'storage/' . $path;
        }

        $testimonial->update($validated);
        return redirect()->route('testimonials.index')->with('success', 'Testimonial updated.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $this->deleteTestimonialPhotoIfExists($testimonial->photo);
        $testimonial->delete();
        return redirect()->route('testimonials.index')->with('success', 'Testimonial deleted.');
    }

    private function deleteTestimonialPhotoIfExists(?string $path): void
    {
        if (! $path || str_starts_with($path, 'http')) {
            return;
        }
        $storagePath = preg_replace('#^storage/#', '', $path);
        if ($storagePath && Storage::disk('public')->exists($storagePath)) {
            Storage::disk('public')->delete($storagePath);
        }
    }
}
