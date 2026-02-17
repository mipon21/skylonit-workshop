<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public API: submit contact/lead form (same as Guest Portal contact form).
 * Used by the SKYLON-IT marketing site (skf) to send leads to the WorkShop.
 */
class PublicContactController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'interested_project_type' => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        Lead::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Thank you! We will get back to you soon.',
        ], 201);
    }
}
