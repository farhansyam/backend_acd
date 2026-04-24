<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    // ─── Konfigurasi Tripay ───────────────────────────────────
    private function tripayBaseUrl(): string
    {
        return config('tripay.sandbox')
            ? 'https://tripay.co.id/api-sandbox'
            : 'https://tripay.co.id/api';
    }

    private function tripayHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . config('tripay.api_key'),
        ];
    }

    // ─── GET metode pembayaran dari Tripay ────────────────────
    public function getPaymentChannels()
    {
        $response = Http::withHeaders($this->tripayHeaders())
            ->get($this->tripayBaseUrl() . '/merchant/payment-channel');

        if (!$response->ok()) {
            return response()->json([
                'message' => 'Gagal memuat metode pembayaran.',
            ], 500);
        }

        $channels = collect($response->json('data'))
            ->where('active', true)
            ->map(fn($c) => [
                'code'          => $c['code'],
                'name'          => $c['name'],
                'group'         => $c['group'],
                'fee_flat'      => $c['fee_flat'] ?? 0,
                'fee_percent'   => $c['fee_percent'] ?? 0,
                'icon_url'      => $c['icon_url'] ?? null,
                'minimum_fee'   => $c['minimum_fee'] ?? 0,
                'maximum_fee'   => $c['maximum_fee'] ?? 0,
            ])
            ->values();

        return response()->json(['channels' => $channels]);
    }

    // ─── POST validasi kupon ──────────────────────────────────
    public function validateCoupon(Request $request)
    {
        $request->validate([
            'code'       => 'required|string',
            'order_total' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', strtoupper($request->code))
            ->first();

        if (!$coupon) {
            return response()->json([
                'valid'   => false,
                'message' => 'Kode kupon tidak ditemukan.',
            ]);
        }

        $result = $coupon->isValidForUser(
            $request->user()->id,
            (float) $request->order_total
        );

        return response()->json([
            ...$result,
            'coupon' => $result['valid'] ? [
                'id'               => $coupon->id,
                'code'             => $coupon->code,
                'name'             => $coupon->name,
                'discount_percent' => (float) $coupon->discount_percent,
                'discount_amount'  => $result['discount_amount'] ?? 0,
            ] : null,
        ]);
    }

    // ─── POST buat transaksi Tripay ───────────────────────────
    public function createTransaction(Request $request)
    {
        $request->validate([
            'order_id'       => 'required|exists:orders,id',
            'payment_method' => 'required|string',
            'coupon_code'    => 'nullable|string',
        ]);

        $order = Order::with(['items.bpService.serviceType', 'user'])
            ->where('id', $request->order_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Pastikan belum dibayar
        if ($order->payment_status === 'paid') {
            return response()->json(['message' => 'Order sudah dibayar.'], 422);
        }

        return DB::transaction(function () use ($request, $order) {
            $discountAmount = 0;
            $couponId = null;
            $totalAmount = (float) $order->total_amount;

            // Proses kupon
            if ($request->filled('coupon_code')) {
                $coupon = Coupon::where('code', strtoupper($request->coupon_code))->first();
                if ($coupon) {
                    $result = $coupon->isValidForUser($request->user()->id, $totalAmount);
                    if ($result['valid']) {
                        $discountAmount = $result['discount_amount'];
                        $couponId = $coupon->id;
                    }
                }
            }

            $finalTotal = max(1000, $totalAmount - $discountAmount); // min Rp 1.000

            // Buat order items untuk Tripay
            $tripayItems = $order->items->map(fn($item) => [
                'name'     => $item->bpService->serviceType->name ?? 'Layanan',
                'price'    => (int) $item->unit_price - (int) $item->discount,
                'quantity' => $item->quantity,
            ])->toArray();

            // Tambah biaya apartemen jika ada
            if ($order->apartment_surcharge > 0) {
                $tripayItems[] = [
                    'name'     => 'Biaya Apartemen',
                    'price'    => (int) $order->apartment_surcharge,
                    'quantity' => 1,
                ];
            }

            // Kurangi diskon kupon sebagai item negatif
            if ($discountAmount > 0) {
                $tripayItems[] = [
                    'name'     => 'Diskon Kupon',
                    'price'    => -(int) $discountAmount,
                    'quantity' => 1,
                ];
            }

            // Buat signature Tripay
            $merchantRef = 'ORDER-' . $order->id . '-' . time();
            $signature = hash_hmac(
                'sha256',
                config('tripay.merchant_code') . $merchantRef . (int) $finalTotal,
                config('tripay.private_key')
            );
            $signatureStr = config('tripay.merchant_code') . $merchantRef . (int) $finalTotal;

            \Log::info('Tripay signature debug', [
                'merchant_code' => config('tripay.merchant_code'),
                'merchant_ref'  => $merchantRef,
                'amount'        => (int) $finalTotal,
                'signature_str' => $signatureStr,
                'private_key'   => substr(config('tripay.private_key'), 0, 10) . '...', // jangan log full key
            ]);

            $signature = hash_hmac('sha256', $signatureStr, config('tripay.private_key'));

            // Kirim ke Tripay
            $tripayResponse = Http::withHeaders($this->tripayHeaders())
                ->post($this->tripayBaseUrl() . '/transaction/create', [
                    'method'         => $request->payment_method,
                    'merchant_ref'   => $merchantRef,
                    'amount'         => (int) $finalTotal,
                    'customer_name'  => $order->user->name,
                    'customer_email' => $order->user->email,
                    'customer_phone' => $order->phone?->phone_number ?? '',
                    'order_items'    => $tripayItems,
                    'callback_url'   => route('tripay.callback'),
                    'return_url'     => config('tripay.return_url'),
                    'expired_time'   => time() + (24 * 60 * 60), // 24 jam
                    'signature'      => $signature,
                ]);

            if (!$tripayResponse->ok()) {
                Log::error('Tripay error', $tripayResponse->json());
                return response()->json([
                    'message' => 'Gagal membuat transaksi pembayaran. Coba lagi.',
                ], 500);
            }

            $tripayData = $tripayResponse->json('data');

            // Update order
            $order->update([
                'coupon_id'          => $couponId,
                'discount_amount'    => $discountAmount,
                'total_amount'       => $finalTotal,
                'payment_method'     => $request->payment_method,
                'payment_status'     => 'unpaid',
                'tripay_reference'   => $tripayData['reference'],
                'tripay_payment_url' => $tripayData['checkout_url'],
            ]);

            // Catat pemakaian kupon
            if ($couponId) {
                CouponUsage::create([
                    'coupon_id'       => $couponId,
                    'user_id'         => $request->user()->id,
                    'order_id'        => $order->id,
                    'discount_amount' => $discountAmount,
                ]);
            }

            return response()->json([
                'message'      => 'Transaksi berhasil dibuat.',
                'payment_url'  => $tripayData['checkout_url'],
                'reference'    => $tripayData['reference'],
                'expired_time' => $tripayData['expired_time'],
                'total_amount' => $finalTotal,
            ]);
        });
    }

    // ─── POST callback dari Tripay (webhook) ──────────────────
    // ─── POST callback dari Tripay (webhook) ──────────────────
    public function callback(Request $request)
    {
        $callbackSignature = $request->server('HTTP_X_CALLBACK_SIGNATURE');
        $json = $request->getContent();
        $signature = hash_hmac('sha256', $json, config('tripay.private_key'));

        Log::info('Callback received', [
            'has_signature'      => !empty($callbackSignature),
            'callback_signature' => $callbackSignature,
            'our_signature'      => $signature,
            'match'              => $callbackSignature === $signature,
            'raw_body'           => substr($json, 0, 200),
        ]);

        if ($callbackSignature !== $signature) {
            Log::warning('Callback signature mismatch');
            return response()->json(['message' => 'Invalid signature.'], 400);
        }

        $data = $request->all();
        $merchantRef = $data['merchant_ref'] ?? null;

        if (!$merchantRef) {
            return response()->json(['message' => 'Invalid data.'], 400);
        }

        $parts = explode('-', $merchantRef);
        $orderId = $parts[1] ?? null;

        $order = Order::find($orderId);
        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        if ($data['status'] === 'PAID') {
            // Fase 2 perbaikan → langsung in_progress (teknisi sudah di lokasi)
            $nextStatus = ($order->is_perbaikan && $order->perbaikan_phase === 'phase2')
                ? 'in_progress'
                : 'confirmed';

            $result = $order->update([
                'payment_status' => 'paid',
                'paid_at'        => now(),
                'status'         => $nextStatus,
            ]);

            // Notif teknisi jika fase 2 langsung in_progress
            if ($order->is_perbaikan && $order->perbaikan_phase === 'phase2') {
                $technician = $order->technician?->user;
                if ($technician?->fcm_token) {
                    app(\App\Services\NotificationService::class)->notifyPhase2Confirmed(
                        $technician->fcm_token,
                        (int) $order->id
                    );
                }
            }

            Log::info('Order updated', [
                'order_id' => $order->id,
                'status'   => $nextStatus,
                'result'   => $result,
            ]);
        } elseif ($data['status'] === 'EXPIRED') {
            $order->update(['payment_status' => 'expired']);
        } elseif ($data['status'] === 'FAILED') {
            $order->update(['payment_status' => 'failed']);
        }

        return response()->json(['success' => true]);
    }
}
