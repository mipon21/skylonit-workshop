<?php

namespace App\Services;

use App\Models\ClientDevice;
use App\Models\ClientNotification;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    private const FCM_LEGACY_URL = 'https://fcm.googleapis.com/fcm/send';

    private const FCM_V1_SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    /**
     * Send a push notification to all devices of the given client.
     * Uses FCM HTTP v1 API when service account is configured; otherwise legacy if server key is set.
     * No-op if FCM is not configured or no devices; failures are logged only.
     */
    public function sendToClientDevices(ClientNotification $notification): void
    {
        $tokens = ClientDevice::where('client_id', $notification->client_id)
            ->pluck('fcm_token')
            ->filter()
            ->values()
            ->all();

        if (empty($tokens)) {
            return;
        }

        $payload = $this->buildPayload($notification);

        if ($this->useV1Api()) {
            $accessToken = $this->getV1AccessToken();
            if ($accessToken) {
                foreach ($tokens as $token) {
                    $this->sendV1($token, $payload, $accessToken);
                }
            }
        } else {
            $serverKey = config('fcm.server_key');
            if (! empty($serverKey)) {
                foreach ($tokens as $token) {
                    $this->sendLegacy($token, $payload, $serverKey);
                }
            }
        }
    }

    private function useV1Api(): bool
    {
        $projectId = config('fcm.project_id');
        $clientEmail = config('fcm.client_email');
        $privateKey = config('fcm.private_key');

        return ! empty($projectId) && ! empty($clientEmail) && ! empty($privateKey);
    }

    /**
     * Get OAuth2 access token for FCM (cached for ~55 minutes; token TTL is 1 hour).
     */
    private function getV1AccessToken(): ?string
    {
        $cacheKey = 'fcm_v1_access_token';

        $token = Cache::get($cacheKey);
        if (is_string($token) && $token !== '') {
            return $token;
        }

        try {
            $client = new GoogleClient;
            $client->setAuthConfig($this->getServiceAccountConfig());
            $client->addScope(self::FCM_V1_SCOPE);
            $result = $client->fetchAccessTokenWithAssertion();
            $accessToken = $result['access_token'] ?? null;
            if (is_string($accessToken) && $accessToken !== '') {
                Cache::put($cacheKey, $accessToken, 55 * 60); // token TTL ~1 hour
                return $accessToken;
            }
        } catch (\Throwable $e) {
            Log::error('FCM v1 access token failed', ['message' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * @return array{type: string, client_email: string, private_key: string}
     */
    private function getServiceAccountConfig(): array
    {
        $key = config('fcm.private_key');
        $key = is_string($key) ? str_replace('\n', "\n", $key) : $key;

        return [
            'type' => 'service_account',
            'client_email' => config('fcm.client_email'),
            'private_key' => $key,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function sendV1(string $token, array $payload, string $accessToken): void
    {
        $projectId = config('fcm.project_id');
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $data = [
            'notification_id' => (string) $payload['notification_id'],
            'type' => $payload['type'],
            'title' => $payload['title'],
            'message' => $payload['message'] ?? '',
            'project_id' => $payload['project_id'] ? (string) $payload['project_id'] : '',
            'project_name' => $payload['project_name'] ?? '',
            'payment_id' => $payload['payment_id'] ? (string) $payload['payment_id'] : '',
            'payment_link' => $payload['payment_link'] ?? '',
            'payment_status' => $payload['payment_status'] ?? '',
            'amount' => $payload['amount'] !== null ? (string) $payload['amount'] : '',
            'invoice_id' => $payload['invoice_id'] ? (string) $payload['invoice_id'] : '',
            'action_url' => $payload['action_url'] ?? '',
        ];

        $message = [
            'token' => $token,
            'notification' => [
                'title' => $payload['title'],
                'body' => $payload['message'] ?? '',
            ],
            'data' => $data,
            'webpush' => [
                'fcm_options' => [
                    'link' => $payload['action_url'] ?? '',
                ],
            ],
        ];

        $body = ['message' => $message];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($url, $body);

            if (! $response->successful()) {
                Log::warning('FCM v1 send failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('FCM v1 send exception', ['message' => $e->getMessage()]);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function sendLegacy(string $token, array $payload, string $serverKey): void
    {
        $body = [
            'to' => $token,
            'notification' => [
                'title' => $payload['title'],
                'body' => $payload['message'] ?? '',
                'click_action' => $payload['action_url'] ?? null,
            ],
            'data' => [
                'notification_id' => (string) $payload['notification_id'],
                'type' => $payload['type'],
                'title' => $payload['title'],
                'message' => $payload['message'] ?? '',
                'project_id' => $payload['project_id'] ? (string) $payload['project_id'] : '',
                'project_name' => $payload['project_name'] ?? '',
                'payment_id' => $payload['payment_id'] ? (string) $payload['payment_id'] : '',
                'payment_link' => $payload['payment_link'] ?? '',
                'payment_status' => $payload['payment_status'] ?? '',
                'amount' => $payload['amount'] !== null ? (string) $payload['amount'] : '',
                'invoice_id' => $payload['invoice_id'] ? (string) $payload['invoice_id'] : '',
                'action_url' => $payload['action_url'] ?? '',
            ],
            'priority' => 'high',
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post(self::FCM_LEGACY_URL, $body);

            if (! $response->successful()) {
                Log::warning('FCM send failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('FCM send exception', ['message' => $e->getMessage()]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayload(ClientNotification $notification): array
    {
        $notification->loadMissing(['project', 'payment.invoice', 'invoice']);

        $actionUrl = $notification->link;
        if (! $actionUrl && $notification->invoice) {
            $actionUrl = route('invoices.view', $notification->invoice);
        }
        if (! $actionUrl && $notification->payment && $notification->payment->invoice) {
            $actionUrl = route('invoices.view', $notification->payment->invoice);
        }
        if (! $actionUrl) {
            $actionUrl = route('client.payments.index');
        }

        $payload = [
            'notification_id' => $notification->id,
            'type' => $notification->type,
            'title' => $notification->title,
            'message' => $notification->message ?? '',
            'project_id' => $notification->project_id,
            'project_name' => $notification->project?->project_name ?? '',
            'payment_id' => $notification->payment_id,
            'payment_link' => $notification->payment?->payment_link ?? '',
            'payment_status' => $notification->payment?->payment_status ?? '',
            'amount' => $notification->payment ? $notification->payment->amount : null,
            'invoice_id' => $notification->invoice_id,
            'action_url' => $actionUrl,
        ];

        return $payload;
    }
}
