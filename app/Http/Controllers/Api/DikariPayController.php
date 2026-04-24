<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DikaripayTransaction;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DikariPayController extends Controller
{
    const MIN_TOPUP = 10000;

    private function tripayBaseUrl(): string
    {
        return config('tripay.sandbox')
            ? 'https://tripay.co.id/api-sandbox'
            : 'https://tripay.co.id/api';
    }

    private function tripayHeaders(): array
    {
        return ['Authorization' => 'Bearer ' . config('tripay.api_key')];
    }

    // ─── GET saldo & riwayat ──────────────────────────────────
    public function balance(Request $request)
    {
        $user = $request->user();

        $transactions = DikaripayTransaction::where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderByDesc('created_at')
            ->take(20)
            ->get()
            ->map(fn($t) => [
                'id'          => $t->id,
                'type'        => $t->type,
                'amount'      => (float) $t->amount,
                'balance_after' => (float) $t->balance_after,
                'description' => $t->description,
                'created_at'  => $t->created_at->format('Y-m-d H:i'),
            ]);

        return response()->json([
            'balance'      => (float) $user->balance,
            'transactions' => $transactions,
        ]);
    }

    // ─── POST topup DikariPay via Tripay ─────────────────────
    public function topup(Request $request)
    {
        $request->validate([
            'amount'         => "required|integer|min:" . self::MIN_TOPUP,
            'payment_method' => 'required|string',
        ]);

        $user   = $request->user();
        $amount = (int) $request->amount;

        // Buat transaksi pending dulu
        $transaction = DikaripayTransaction::create([
            'user_id'        => $user->id,
            'type'           => 'topup',
            'amount'         => $amount,
            'balance_before' => (float) $user->balance,
            'balance_after'  => (float) $user->balance + $amount,
            'payment_method' => $request->payment_method,
            'status'         => 'pending',
            'description'    => "Topup DikariPay Rp " . number_format($amount, 0, ',', '.'),
        ]);

        // Buat merchant ref khusus topup
        $merchantRef = 'TOPUP-' . $user->id . '-' . $transaction->id . '-' . time();

        // Signature Tripay
        $signature = hash_hmac(
            'sha256',
            config('tripay.merchant_code') . $merchantRef . $amount,
            config('tripay.private_key')
        );

        // Kirim ke Tripay
        $tripayResponse = Http::withHeaders($this->tripayHeaders())
            ->post($this->tripayBaseUrl() . '/transaction/create', [
                'method'       => $request->payment_method,
                'merchant_ref' => $merchantRef,
                'amount'       => $amount,
                'customer_name'  => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => '',
                'order_items'  => [[
                    'name'     => 'Topup DikariPay',
                    'price'    => $amount,
                    'quantity' => 1,
                ]],
                'callback_url'  => env('TRIPAY_CALLBACK_URL'),
                'return_url'    => config('tripay.return_url'),
                'expired_time'  => time() + (24 * 60 * 60),
                'signature'     => $signature,
            ]);

        if (!$tripayResponse->ok()) {
            $transaction->update(['status' => 'failed']);
            Log::error('Tripay topup error', $tripayResponse->json());
            return response()->json([
                'message' => 'Gagal membuat transaksi topup.',
            ], 500);
        }

        $tripayData = $tripayResponse->json('data');

        // Simpan reference Tripay
        $transaction->update([
            'tripay_reference' => $tripayData['reference'],
        ]);

        return response()->json([
            'message'     => 'Topup berhasil dibuat.',
            'payment_url' => $tripayData['checkout_url'],
            'reference'   => $tripayData['reference'],
            'amount'      => $amount,
        ]);
    }

    // ─── POST bayar order pakai DikariPay ────────────────────
    public function payOrder(Request $request)
    {
        $request->validate([
            'order_id'   => 'required|exists:orders,id',
            'coupon_code' => 'nullable|string',
        ]);

        $user  = $request->user();
        $order = Order::where('id', $request->order_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        abort_if($order->payment_status === 'paid', 422, 'Order sudah dibayar.');

        // Hitung total dengan diskon kupon jika ada
        $totalAmount    = (float) $order->total_amount;
        $discountAmount = 0;
        $couponId       = null;

        if ($request->filled('coupon_code')) {
            $coupon = \App\Models\Coupon::where('code', strtoupper($request->coupon_code))->first();
            if ($coupon) {
                $result = $coupon->isValidForUser($user->id, $totalAmount);
                if ($result['valid']) {
                    $discountAmount = $result['discount_amount'];
                    $couponId       = $coupon->id;
                }
            }
        }

        $finalTotal = max(1000, $totalAmount - $discountAmount);

        // Cek saldo cukup
        if ((float) $user->balance < $finalTotal) {
            return response()->json([
                'message'          => 'Saldo DikariPay tidak mencukupi.',
                'balance'          => (float) $user->balance,
                'required'         => $finalTotal,
                'shortage'         => $finalTotal - (float) $user->balance,
            ], 422);
        }

        return DB::transaction(function () use (
            $user,
            $order,
            $finalTotal,
            $discountAmount,
            $couponId
        ) {
            $balanceBefore = (float) $user->balance;

            // Potong saldo
            $user->decrement('balance', $finalTotal);

            // Catat transaksi DikariPay
            DikaripayTransaction::create([
                'user_id'        => $user->id,
                'type'           => 'payment',
                'amount'         => $finalTotal,
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceBefore - $finalTotal,
                'order_id'       => $order->id,
                'status'         => 'completed',
                'description'    => "Pembayaran order #{$order->id}",
            ]);

            // Catat kupon
            if ($couponId) {
                \App\Models\CouponUsage::create([
                    'coupon_id'       => $couponId,
                    'user_id'         => $user->id,
                    'order_id'        => $order->id,
                    'discount_amount' => $discountAmount,
                ]);
            }

            // Update order

            $nextStatus = ($order->is_perbaikan && $order->perbaikan_phase === 'phase2')
                ? 'in_progress'
                : 'confirmed';

            $order->update([
                'coupon_id'       => $couponId,
                'discount_amount' => $discountAmount,
                'total_amount'    => $finalTotal,
                'payment_method'  => 'DIKARIPAY',
                'payment_status'  => 'paid',
                'paid_at'         => now(),
                'status'          => $nextStatus,
            ]);

            // Notif teknisi jika fase 2
            if ($order->is_perbaikan && $order->perbaikan_phase === 'phase2') {
                $technician = $order->technician?->user;
                if ($technician?->fcm_token) {
                    app(\App\Services\NotificationService::class)->notifyPhase2Confirmed(
                        $technician->fcm_token,
                        (int) $order->id
                    );
                }
            }

            return response()->json([
                'message'       => 'Pembayaran dengan DikariPay berhasil!',
                'balance_after' => (float) $user->fresh()->balance,
                'order_id'      => $order->id,
            ]);
        });
    }

    // ─── POST callback Tripay untuk topup ────────────────────
    public function topupCallback(Request $request)
    {
        $callbackSignature = $request->server('HTTP_X_CALLBACK_SIGNATURE');
        $json              = $request->getContent();
        $signature         = hash_hmac('sha256', $json, config('tripay.private_key'));

        if ($callbackSignature !== $signature) {
            return response()->json(['message' => 'Invalid signature.'], 400);
        }

        $data        = $request->all();
        $merchantRef = $data['merchant_ref'] ?? null;

        if (!$merchantRef || !str_starts_with($merchantRef, 'TOPUP-')) {
            return response()->json(['message' => 'Invalid merchant ref.'], 400);
        }

        // Format: TOPUP-{user_id}-{transaction_id}-{timestamp}
        $parts         = explode('-', $merchantRef);
        $transactionId = $parts[2] ?? null;

        $transaction = DikaripayTransaction::find($transactionId);
        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found.'], 404);
        }

        if ($data['status'] === 'PAID' && $transaction->status === 'pending') {
            DB::transaction(function () use ($transaction) {
                $user          = $transaction->user;
                $balanceBefore = (float) $user->balance;
                $amount        = (float) $transaction->amount;

                // Tambah saldo
                $user->increment('balance', $amount);

                // Update transaksi
                $transaction->update([
                    'status'         => 'completed',
                    'balance_before' => $balanceBefore,
                    'balance_after'  => $balanceBefore + $amount,
                ]);

                Log::info('DikariPay topup completed', [
                    'user_id'        => $user->id,
                    'amount'         => $amount,
                    'balance_after'  => $balanceBefore + $amount,
                ]);
            });
        } elseif ($data['status'] === 'EXPIRED' || $data['status'] === 'FAILED') {
            $transaction->update(['status' => 'failed']);
        }

        return response()->json(['success' => true]);
    }
}
