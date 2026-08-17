<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaystackService
{
    private string $secretKey;
    private string $baseUrl = 'https://api.paystack.co';

    public function __construct()
    {
        $this->secretKey = (string) config('services.paystack.secret_key');
    }

    public function initialize(Order $order, string $email): array
    {
        if ($this->secretKey === '') {
            throw new RuntimeException('Paystack secret key is not configured.');
        }

        $reference = 'PSTK-' . $order->order_number;
        $amount = (int) round(((float) $order->total) * 100);

        $response = Http::withToken($this->secretKey)
            ->acceptJson()
            ->post($this->baseUrl . '/transaction/initialize', [
                'email' => $email,
                'amount' => $amount,
                'currency' => $order->currency,
                'reference' => $reference,
                'callback_url' => route('checkout.paystack.callback'),
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ],
            ]);

        if ($response->failed() || !$response->json('status')) {
            throw new RuntimeException(
                $response->json('message') ?: 'Unable to initialize Paystack transaction.'
            );
        }

        $data = $response->json('data');

        $order->update([
            'payment_provider' => 'paystack',
            'payment_reference' => $data['reference'] ?? $reference,
        ]);

        return $data;
    }

    public function verify(string $reference): array
    {
        if ($this->secretKey === '') {
            throw new RuntimeException('Paystack secret key is not configured.');
        }

        $response = Http::withToken($this->secretKey)
            ->acceptJson()
            ->get($this->baseUrl . '/transaction/verify/' . urlencode($reference));

        if ($response->failed() || !$response->json('status')) {
            throw new RuntimeException(
                $response->json('message') ?: 'Unable to verify Paystack transaction.'
            );
        }

        return $response->json('data');
    }

    public function validWebhookSignature(string $payload, ?string $signature): bool
    {
        if (!$signature || $this->secretKey === '') {
            return false;
        }

        $expected = hash_hmac('sha512', $payload, $this->secretKey);

        return hash_equals($expected, $signature);
    }
}
