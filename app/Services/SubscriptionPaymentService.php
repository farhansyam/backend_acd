<?php

namespace App\Services;

use App\Models\Subscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SubscriptionPaymentService
{
    private string $apiKey;
    private string $privateKey;
    private string $merchantCode;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey       = config('tripay.api_key');
        $this->privateKey   = config('tripay.private_key');
        $this->merchantCode = config('tripay.merchant_code');
        $this->baseUrl      = config('tripay.base_url', 'https://tripay.co.id/api-sandbox');
    }

    public function createTransaction(Subscription $subscription, string $paymentMethod): array
    {
        $subscription->load(['user', 'items.bpService.serviceType', 'package']);

        $merchantRef = 'SUB-' . strtoupper(uniqid());
        $amount      = (int) $subscription->total_amount;

        // Signature
        $signature = hash_hmac(
            'sha256',
            $this->merchantCode . $merchantRef . $amount,
            $this->privateKey
        );

        // Order items untuk Tripay
        $orderItems = $subscription->items->map(fn($item) => [
            'sku'      => 'SUB-ITEM-' . $item->id,
            'name'     => $item->bpService->serviceType->name . ' (Langganan × ' . $subscription->package->total_sessions . ' sesi)',
            'price'    => (int) $item->unit_price,
            'quantity' => $item->quantity,
        ])->toArray();

        $payload = [
            'method'         => $paymentMethod,
            'merchant_ref'   => $merchantRef,
            'amount'         => $amount,
            'customer_name'  => $subscription->user->name,
            'customer_email' => $subscription->user->email,
            'order_items'    => $orderItems,
            'callback_url'   => route('tripay.subscription.callback'),
            'return_url'     => config('app.url') . '/subscription-success',
            'expired_time'   => now()->addHours(24)->timestamp,
            'signature'      => $signature,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->post($this->baseUrl . '/transaction/create', $payload);

        if (!$response->successful() || !($response->json('success'))) {
            Log::error('Tripay subscription error', [
                'subscription_id' => $subscription->id,
                'response'        => $response->json(),
            ]);
            throw new \Exception('Gagal membuat transaksi pembayaran: ' . $response->json('message'));
        }

        $data = $response->json('data');

        // Simpan merchant_ref ke subscription
        $subscription->update(['tripay_reference' => $merchantRef]);

        return [
            'reference'   => $merchantRef,
            'payment_url' => $data['checkout_url'] ?? null,
        ];
    }

    /**
     * Handle Tripay callback untuk subscription payment.
     * Dipanggil dari PaymentController atau route callback tersendiri.
     */
    public function handleCallback(array $payload): void
    {
        $reference = $payload['merchant_ref'] ?? null;

        if (!$reference || !str_starts_with($reference, 'SUB-')) {
            return; // Bukan subscription, abaikan
        }

        $subscription = Subscription::where('tripay_reference', $reference)->first();
        if (!$subscription || $subscription->payment_status === 'paid') {
            return;
        }

        $isPaid = in_array($payload['status'] ?? '', ['PAID', 'SETTLED']);

        if ($isPaid) {
            $subscription->update([
                'payment_status' => 'paid',
                'payment_method' => $payload['payment_method'] ?? $subscription->payment_method,
                'paid_at'        => now(),
                // status tetap 'pending' sampai customer set jadwal
            ]);

            Log::info("Subscription #{$subscription->id} berhasil dibayar via Tripay.");
        }
    }
}
