<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UddoktaPayService
{
    protected string $baseUrl;

    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.uddoktapay.base_url', ''), '/');
        $this->apiKey = config('services.uddoktapay.api_key', '');
    }

    /**
     * Create a charge and return payment_url and invoice_id (if provided).
     *
     * @return array{success: bool, payment_url?: string, invoice_id?: string, message?: string}
     */
    public function createCharge(Payment $payment, string $redirectUrl, string $cancelUrl, string $webhookUrl): array
    {
        if (! $this->apiKey) {
            Log::warning('UddoktaPay: API key not configured.');
            return ['success' => false, 'message' => 'UddoktaPay is not configured.'];
        }

        $project = $payment->project;
        $client = $project->client;

        $payload = [
            'full_name' => $client->name ?? 'Customer',
            'email' => $client->email ?? 'customer@example.com',
            'amount' => (float) $payment->amount,
            'metadata' => [
                'payment_id' => (string) $payment->id,
                'project_id' => (string) $project->id,
            ],
            'redirect_url' => $redirectUrl,
            'cancel_url' => $cancelUrl,
            'webhook_url' => $webhookUrl,
            'return_type' => 'GET',
        ];

        $response = Http::withHeaders([
            'RT-UDDOKTAPAY-API-KEY' => $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($this->baseUrl . '/checkout-v2', $payload);

        $data = $response->json();

        if (! $response->successful()) {
            Log::warning('UddoktaPay create charge failed.', ['response' => $data, 'payment_id' => $payment->id]);
            return [
                'success' => false,
                'message' => $data['message'] ?? $response->body(),
            ];
        }

        // Support common response keys: payment_url (UddoktaPay), checkout_url, redirect_url, url
        $paymentUrl = $data['payment_url'] ?? $data['checkout_url'] ?? $data['redirect_url'] ?? $data['url'] ?? null;
        if (empty($paymentUrl)) {
            Log::warning('UddoktaPay create charge: no payment URL in response.', ['response' => $data]);
            return [
                'success' => false,
                'message' => $data['message'] ?? 'No payment URL in response. Keys received: ' . implode(', ', array_keys($data ?? [])),
            ];
        }

        return [
            'success' => true,
            'payment_url' => $paymentUrl,
            'invoice_id' => $data['invoice_id'] ?? $data['invoice'] ?? null,
        ];
    }

    /**
     * Verify payment by invoice_id. Returns verification data or null on failure.
     *
     * @return array{status: string, amount?: float, metadata?: array}|null
     */
    public function verifyPayment(string $invoiceId): ?array
    {
        if (! $this->apiKey) {
            return null;
        }

        $response = Http::withHeaders([
            'RT-UDDOKTAPAY-API-KEY' => $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($this->baseUrl . '/verify-payment', [
            'invoice_id' => $invoiceId,
        ]);

        if (! $response->successful()) {
            Log::warning('UddoktaPay verify failed.', ['invoice_id' => $invoiceId, 'body' => $response->body()]);
            return null;
        }

        $data = $response->json();
        $status = $data['status'] ?? null;

        return [
            'status' => $status,
            'amount' => isset($data['amount']) ? (float) $data['amount'] : null,
            'metadata' => $data['metadata'] ?? [],
        ];
    }

    /**
     * Check if gateway is configured (base_url and api_key set).
     */
    public function isConfigured(): bool
    {
        return $this->baseUrl !== '' && $this->apiKey !== '';
    }
}
