<?php

namespace App\Services;

use App\Models\AcdBalance;
use App\Models\BalanceTransaction;
use App\Models\Order;
use App\Models\Technician;
use App\Models\BusinessPartner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BalanceService
{
    // ─── Persentase per grade ─────────────────────────────────
    const GRADE_RATES = [
        'beginner' => ['technician' => 55, 'bp' => 30, 'acd' => 15],
        'medium'   => ['technician' => 65, 'bp' => 20, 'acd' => 15],
        'pro'      => ['technician' => 70, 'bp' => 15, 'acd' => 15],
    ];

    const HOLDING_HOURS = 2;

    /**
     * Distribusi saldo setelah order completed
     * Saldo masuk dalam status 'hold' dulu, release setelah 24 jam
     */
    public function distributeOrderEarning(Order $order): void
    {
        if (!$order->technician_id) {
            Log::warning('Cannot distribute: no technician assigned', [
                'order_id' => $order->id,
            ]);
            return;
        }

        $technician = Technician::find($order->technician_id);
        $bp = BusinessPartner::find($order->bp_id);

        if (!$technician || !$bp) {
            Log::warning('Cannot distribute: technician or BP not found', [
                'order_id'       => $order->id,
                'technician_id'  => $order->technician_id,
                'bp_id'          => $order->bp_id,
            ]);
            return;
        }

        $grade = $technician->grade ?? 'beginner';
        $rates = self::GRADE_RATES[$grade] ?? self::GRADE_RATES['beginner'];

        $totalAmount = (float) $order->total_amount;
        $releaseAt   = now()->addHours(self::HOLDING_HOURS);

        $technicianShare = round($totalAmount * $rates['technician'] / 100, 2);
        $bpShare         = round($totalAmount * $rates['bp'] / 100, 2);
        $acdShare        = round($totalAmount * $rates['acd'] / 100, 2);

        DB::transaction(function () use (
            $order,
            $technician,
            $bp,
            $technicianShare,
            $bpShare,
            $acdShare,
            $releaseAt,
            $grade,
            $rates
        ) {
            // ─── Teknisi ─────────────────────────────────────
            $techBalanceBefore = (float) $technician->balance_hold;
            $technician->increment('balance_hold', $technicianShare);

            BalanceTransaction::create([
                'owner_type'     => Technician::class,
                'owner_id'       => $technician->id,
                'order_id'       => $order->id,
                'type'           => 'hold',
                'amount'         => $technicianShare,
                'balance_before' => $techBalanceBefore,
                'balance_after'  => $techBalanceBefore + $technicianShare,
                'description'    => "Pendapatan order #{$order->id} (grade: {$grade}, {$rates['technician']}%) - menunggu release",
                'status'         => 'pending',
                'release_at'     => $releaseAt,
            ]);

            // ─── BP ───────────────────────────────────────────
            $bpBalanceBefore = (float) $bp->balance;
            $bp->increment('balance', $bpShare);

            BalanceTransaction::create([
                'owner_type'     => BusinessPartner::class,
                'owner_id'       => $bp->id,
                'order_id'       => $order->id,
                'type'           => 'hold',
                'amount'         => $bpShare,
                'balance_before' => $bpBalanceBefore,
                'balance_after'  => $bpBalanceBefore + $bpShare,
                'description'    => "Pendapatan order #{$order->id} ({$rates['bp']}%) - menunggu release",
                'status'         => 'pending',
                'release_at'     => $releaseAt,
            ]);

            // ─── ACD ──────────────────────────────────────────
            $acd = AcdBalance::getInstance();
            $acd->increment('balance', $acdShare);
            $acd->increment('total_earned', $acdShare);

            BalanceTransaction::create([
                'owner_type'     => AcdBalance::class,
                'owner_id'       => $acd->id,
                'order_id'       => $order->id,
                'type'           => 'earning',
                'amount'         => $acdShare,
                'balance_before' => (float) $acd->balance - $acdShare,
                'balance_after'  => (float) $acd->balance,
                'description'    => "Pendapatan ACD order #{$order->id} ({$rates['acd']}%)",
                'status'         => 'completed',
                'release_at'     => null,
            ]);

            Log::info('Balance distributed', [
                'order_id'         => $order->id,
                'grade'            => $grade,
                'total'            => $order->total_amount,
                'technician_share' => $technicianShare,
                'bp_share'         => $bpShare,
                'acd_share'        => $acdShare,
                'release_at'       => $releaseAt,
            ]);
        });
    }

    /**
     * Release saldo yang sudah melewati masa holding
     * Dipanggil via scheduled command
     */
    public function releaseHeldBalances(): void
    {
        $pendingTransactions = BalanceTransaction::where('status', 'pending')
            ->where('type', 'hold')
            ->where('release_at', '<=', now())
            ->get();

        foreach ($pendingTransactions as $transaction) {
            DB::transaction(function () use ($transaction) {
                $owner = $transaction->owner;

                if ($owner instanceof Technician) {
                    $balanceBefore = (float) $owner->balance;
                    $owner->decrement('balance_hold', (float) $transaction->amount);
                    $owner->increment('balance', (float) $transaction->amount);

                    BalanceTransaction::create([
                        'owner_type'     => Technician::class,
                        'owner_id'       => $owner->id,
                        'order_id'       => $transaction->order_id,
                        'type'           => 'release',
                        'amount'         => $transaction->amount,
                        'balance_before' => $balanceBefore,
                        'balance_after'  => $balanceBefore + (float) $transaction->amount,
                        'description'    => "Saldo dirilis dari order #{$transaction->order_id}",
                        'status'         => 'completed',
                    ]);
                } elseif ($owner instanceof BusinessPartner) {
                    BalanceTransaction::create([
                        'owner_type'     => BusinessPartner::class,
                        'owner_id'       => $owner->id,
                        'order_id'       => $transaction->order_id,
                        'type'           => 'release',
                        'amount'         => $transaction->amount,
                        'balance_before' => (float) $owner->balance - (float) $transaction->amount,
                        'balance_after'  => (float) $owner->balance,
                        'description'    => "Saldo dirilis dari order #{$transaction->order_id}",
                        'status'         => 'completed',
                    ]);
                }

                $transaction->update(['status' => 'completed']);
            });
        }

        Log::info('Released held balances', ['count' => $pendingTransactions->count()]);
    }

    /**
     * Distribusi saldo untuk order relokasi (bisa 1 atau 2 teknisi)
     */
    public function distributeRelocationEarning(Order $order): void
    {
        $bp = BusinessPartner::find($order->bp_id);
        if (!$bp) return;

        $releaseAt = now()->addHours(self::HOLDING_HOURS);

        // Pisah items berdasarkan category
        $order->load('items.bpService.serviceType');

        $bongkarItems = $order->items->filter(fn($i) =>
        $i->bpService?->serviceType?->category === 'relokasi_bongkar');
        $pasangItems  = $order->items->filter(fn($i) =>
        $i->bpService?->serviceType?->category === 'relokasi_pasang');

        $bongkarTotal = $bongkarItems->sum('subtotal');
        $pasangTotal  = $pasangItems->sum('subtotal');

        // Transport fee masuk ke BP saja (tidak dibagi ke teknisi)
        $transportFee = (float) $order->transport_fee;

        DB::transaction(function () use (
            $order,
            $bp,
            $bongkarTotal,
            $pasangTotal,
            $transportFee,
            $releaseAt
        ) {
            // ─── Teknisi Bongkar ──────────────────────────────────
            if ($order->technician_id && $bongkarTotal > 0) {
                $techBongkar = Technician::find($order->technician_id);
                if ($techBongkar) {
                    $grade = $techBongkar->grade ?? 'beginner';
                    $rates = self::GRADE_RATES[$grade] ?? self::GRADE_RATES['beginner'];

                    $techShare = round($bongkarTotal * $rates['technician'] / 100, 2);
                    $bpShare   = round($bongkarTotal * $rates['bp'] / 100, 2);
                    $acdShare  = round($bongkarTotal * $rates['acd'] / 100, 2);

                    $before = (float) $techBongkar->balance_hold;
                    $techBongkar->increment('balance_hold', $techShare);
                    BalanceTransaction::create([
                        'owner_type'     => Technician::class,
                        'owner_id'       => $techBongkar->id,
                        'order_id'       => $order->id,
                        'type'           => 'hold',
                        'amount'         => $techShare,
                        'balance_before' => $before,
                        'balance_after'  => $before + $techShare,
                        'description'    => "Fee bongkar order #{$order->id} (grade: {$grade}, {$rates['technician']}%)",
                        'status'         => 'pending',
                        'release_at'     => $releaseAt,
                    ]);

                    // BP share dari bongkar
                    $bpBefore = (float) $bp->balance;
                    $bp->increment('balance', $bpShare);
                    BalanceTransaction::create([
                        'owner_type'     => BusinessPartner::class,
                        'owner_id'       => $bp->id,
                        'order_id'       => $order->id,
                        'type'           => 'hold',
                        'amount'         => $bpShare,
                        'balance_before' => $bpBefore,
                        'balance_after'  => $bpBefore + $bpShare,
                        'description'    => "Fee bongkar order #{$order->id} ({$rates['bp']}%)",
                        'status'         => 'pending',
                        'release_at'     => $releaseAt,
                    ]);

                    // ACD share dari bongkar
                    $acd = AcdBalance::getInstance();
                    $acd->increment('balance', $acdShare);
                    $acd->increment('total_earned', $acdShare);
                    BalanceTransaction::create([
                        'owner_type'     => AcdBalance::class,
                        'owner_id'       => $acd->id,
                        'order_id'       => $order->id,
                        'type'           => 'earning',
                        'amount'         => $acdShare,
                        'balance_before' => (float) $acd->balance - $acdShare,
                        'balance_after'  => (float) $acd->balance,
                        'description'    => "ACD fee bongkar order #{$order->id}",
                        'status'         => 'completed',
                    ]);
                }
            }

            // ─── Teknisi Pasang ───────────────────────────────────
            $pasangTechId = $order->split_technician
                ? $order->second_technician_id
                : $order->technician_id; // sama kalau 1 teknisi

            if ($pasangTechId && $pasangTotal > 0) {
                $techPasang = Technician::find($pasangTechId);
                if ($techPasang) {
                    $grade = $techPasang->grade ?? 'beginner';
                    $rates = self::GRADE_RATES[$grade] ?? self::GRADE_RATES['beginner'];

                    $techShare = round($pasangTotal * $rates['technician'] / 100, 2);
                    $bpShare   = round($pasangTotal * $rates['bp'] / 100, 2);
                    $acdShare  = round($pasangTotal * $rates['acd'] / 100, 2);

                    $before = (float) $techPasang->balance_hold;
                    $techPasang->increment('balance_hold', $techShare);
                    BalanceTransaction::create([
                        'owner_type'     => Technician::class,
                        'owner_id'       => $techPasang->id,
                        'order_id'       => $order->id,
                        'type'           => 'hold',
                        'amount'         => $techShare,
                        'balance_before' => $before,
                        'balance_after'  => $before + $techShare,
                        'description'    => "Fee pasang order #{$order->id} (grade: {$grade}, {$rates['technician']}%)",
                        'status'         => 'pending',
                        'release_at'     => $releaseAt,
                    ]);

                    // BP share dari pasang
                    $bpBefore = (float) $bp->fresh()->balance;
                    $bp->increment('balance', $bpShare);
                    BalanceTransaction::create([
                        'owner_type'     => BusinessPartner::class,
                        'owner_id'       => $bp->id,
                        'order_id'       => $order->id,
                        'type'           => 'hold',
                        'amount'         => $bpShare,
                        'balance_before' => $bpBefore,
                        'balance_after'  => $bpBefore + $bpShare,
                        'description'    => "Fee pasang order #{$order->id} ({$rates['bp']}%)",
                        'status'         => 'pending',
                        'release_at'     => $releaseAt,
                    ]);

                    // ACD share dari pasang
                    $acd = AcdBalance::getInstance();
                    $acd->increment('balance', $acdShare);
                    $acd->increment('total_earned', $acdShare);
                    BalanceTransaction::create([
                        'owner_type'     => AcdBalance::class,
                        'owner_id'       => $acd->id,
                        'order_id'       => $order->id,
                        'type'           => 'earning',
                        'amount'         => $acdShare,
                        'balance_before' => (float) $acd->balance - $acdShare,
                        'balance_after'  => (float) $acd->balance,
                        'description'    => "ACD fee pasang order #{$order->id}",
                        'status'         => 'completed',
                    ]);
                }
            }

            // ─── Transport fee → BP ───────────────────────────────
            if ($transportFee > 0) {
                $bpBefore = (float) $bp->fresh()->balance;
                $bp->increment('balance', $transportFee);
                BalanceTransaction::create([
                    'owner_type'     => BusinessPartner::class,
                    'owner_id'       => $bp->id,
                    'order_id'       => $order->id,
                    'type'           => 'earning',
                    'amount'         => $transportFee,
                    'balance_before' => $bpBefore,
                    'balance_after'  => $bpBefore + $transportFee,
                    'description'    => "Biaya transportasi order #{$order->id}",
                    'status'         => 'completed',
                ]);
            }
        });
    }
}
