<?php

namespace App\Http\Controllers;

use App\Models\ClientDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientDeviceController extends Controller
{
    /**
     * Register this device's FCM token for the authenticated client.
     * Client can only register tokens for their own client record.
     */
    public function register(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->isClient() || ! $user->client) {
            return response()->json(['message' => 'Client account required.'], 403);
        }

        $validated = $request->validate([
            'fcm_token' => ['required', 'string', 'max:512'],
            'platform' => ['required', 'string', Rule::in([
                ClientDevice::PLATFORM_WEB,
                ClientDevice::PLATFORM_ANDROID,
                ClientDevice::PLATFORM_IOS,
            ])],
        ]);

        $clientId = $user->client->id;

        $device = ClientDevice::updateOrCreate(
            [
                'client_id' => $clientId,
                'fcm_token' => $validated['fcm_token'],
            ],
            [
                'platform' => $validated['platform'],
                'last_seen_at' => now(),
            ]
        );

        return response()->json([
            'ok' => true,
            'device_id' => $device->id,
        ]);
    }

    /**
     * Optional: unregister current FCM token (e.g. on logout).
     */
    public function unregister(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->isClient() || ! $user->client) {
            return response()->json(['message' => 'Client account required.'], 403);
        }

        $validated = $request->validate([
            'fcm_token' => ['required', 'string', 'max:512'],
        ]);

        ClientDevice::where('client_id', $user->client->id)
            ->where('fcm_token', $validated['fcm_token'])
            ->delete();

        return response()->json(['ok' => true]);
    }
}
