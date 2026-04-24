<?php

namespace App\Services;

use App\Models\AcdBalance;
use App\Models\BalanceTransaction;
use App\Models\SubscriptionSession;
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

    const HOLDING_MINUTES = 30; // Hold 30 menit untuk auto-complete

    // ─────────────────────────────────────────────────────────────
    // DISTRIBUSI NORMAL (auto-complete → hold 30 menit)
    // ─────────────────────────────────────────────────────────────
    public function distributeOrderEarning(Order $order): void
    {
        if (!$order->technician_id) {
            Log::warning('Cannot distribute: no technician assigned', ['order_id' => $order->id]);
            return;
        }

        $technician = Technician::find($order->technician_id);
        $bp         = BusinessPartner::find($order->bp_id);

        if (!$technician || !$bp) {
            Log::warning('Cannot distribute: technician or BP not found', [
                'order_id'      => $order->id,
                'technician_id' => $order->technician_id,
                'bp_id'         => $order->bp_id,
            ]);
            return;
        }

        $grade     = $technician->grade ?? 'beginner';
        $rates     = self::GRADE_RATES[$grade] ?? self::GRADE_RATES['beginner'];
        $total     = (float) $order->total_amount;
        $releaseAt = now()->addMinutes(self::HOLDING_MINUTES);

        $techShare = round($total * $rates['technician'] / 100, 2);
        $bpShare   = round($total * $rates['bp'] / 100, 2);
        $acdShare  = round($total * $rates['acd'] / 100, 2);

        DB::transaction(function () use ($order, $technician, $bp, $techShare, $bpShare, $acdShare, $releaseAt, $grade, $rates) {
            // Teknisi — hold
            $before = (float) $technician->balance_hold;
            $technician->increment('balance_hold', $techShare);
            BalanceTransaction::create([
                'owner_type'     => Technician::class,
                'owner_id'       => $technician->id,
                'order_id'       => $order->id,
                'type'           => 'hold',
                'amount'         => $techShare,
                'balance_before' => $before,
                'balance_after'  => $before + $techShare,
                'description'    => "Pendapatan order #{$order->id} (grade: {$grade}, {$rates['technician']}%) - menunggu release",
                'status'         => 'pending',
                'release_at'     => $releaseAt,
            ]);

            // BP — hold
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
                'description'    => "Pendapatan order #{$order->id} ({$rates['bp']}%) - menunggu release",
                'status'         => 'pending',
                'release_at'     => $releaseAt,
            ]);

            // ACD — langsung
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
        });

        Log::info('Balance distributed (hold)', ['order_id' => $order->id, 'tech_share' => $techShare]);
    }

    // ─────────────────────────────────────────────────────────────
    // DISTRIBUSI LANGSUNG (customer konfirmasi manual → tidak hold)
    // ─────────────────────────────────────────────────────────────
    public function distributeOrderEarningDirect(Order $order): void
    {
        if (!$order->technician_id) return;

        $technician = Technician::find($order->technician_id);
        $bp         = BusinessPartner::find($order->bp_id);
        if (!$technician || !$bp) return;

        $grade    = $technician->grade ?? 'beginner';
        $rates    = self::GRADE_RATES[$grade] ?? self::GRADE_RATES['beginner'];
        $total    = (float) $order->total_amount;

        $techShare = round($total * $rates['technician'] / 100, 2);
        $bpShare   = round($total * $rates['bp'] / 100, 2);
        $acdShare  = round($total * $rates['acd'] / 100, 2);

        DB::transaction(function () use ($order, $technician, $bp, $techShare, $bpShare, $acdShare, $grade, $rates) {
            // Teknisi — langsung masuk balance
            $before = (float) $technician->balance;
            $technician->increment('balance', $techShare);
            BalanceTransaction::create([
                'owner_type'     => Technician::class,
                'owner_id'       => $technician->id,
                'order_id'       => $order->id,
                'type'           => 'release',
                'amount'         => $techShare,
                'balance_before' => $before,
                'balance_after'  => $before + $techShare,
                'description'    => "Pendapatan order #{$order->id} (grade: {$grade}, {$rates['technician']}%) - dikonfirmasi customer",
                'status'         => 'completed',
                'release_at'     => null,
            ]);

            // BP — langsung
            $bpBefore = (float) $bp->balance;
            $bp->increment('balance', $bpShare);
            BalanceTransaction::create([
                'owner_type'     => BusinessPartner::class,
                'owner_id'       => $bp->id,
                'order_id'       => $order->id,
                'type'           => 'release',
                'amount'         => $bpShare,
                'balance_before' => $bpBefore,
                'balance_after'  => $bpBefore + $bpShare,
                'description'    => "Pendapatan order #{$order->id} ({$rates['bp']}%) - dikonfirmasi customer",
                'status'         => 'completed',
                'release_at'     => null,
            ]);

            // ACD — langsung
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
        });

        Log::info('Balance distributed (direct)', ['order_id' => $order->id, 'tech_share' => $techShare]);
    }

    // ─────────────────────────────────────────────────────────────
    // DISTRIBUSI RELOKASI — auto-complete (hold 30 menit)
    // ─────────────────────────────────────────────────────────────
    public function distributeRelocationEarning(Order $order): void
    {
        $this->_distributeRelocation($order, hold: true);
    }

    // ─────────────────────────────────────────────────────────────
    // DISTRIBUSI RELOKASI — customer konfirmasi (langsung)
    // ─────────────────────────────────────────────────────────────
    public function distributeRelocationEarningDirect(Order $order): void
    {
        $this->_distributeRelocation($order, hold: false);
    }

    // ─────────────────────────────────────────────────────────────
    // INTERNAL — logic distribusi relokasi
    // ─────────────────────────────────────────────────────────────
    private function _distributeRelocation(Order $order, bool $hold): void
    {
        $bp = BusinessPartner::find($order->bp_id);
        if (!$bp) return;

        $order->load('items.bpService.serviceType');

        $bongkarTotal = (float) $order->items
            ->filter(fn($i) => $i->bpService?->serviceType?->category === 'relokasi_bongkar')
            ->sum('subtotal');

        $pasangTotal = (float) $order->items
            ->filter(fn($i) => $i->bpService?->serviceType?->category === 'relokasi_pasang')
            ->sum('subtotal');

        // Kalau tidak ada item relokasi_bongkar/pasang (relokasi 1 lokasi pakai category 'relokasi')
        // fallback: bagi 50/50 dari total
        if ($bongkarTotal == 0 && $pasangTotal == 0) {
            $half = round((float) $order->total_amount / 2, 2);
            $bongkarTotal = $half;
            $pasangTotal  = $half;
        }

        $transportFee = (float) $order->transport_fee;
        $releaseAt    = $hold ? now()->addMinutes(self::HOLDING_MINUTES) : null;
        $type         = $hold ? 'hold' : 'release';
        $status       = $hold ? 'pending' : 'completed';

        DB::transaction(function () use (
            $order,
            $bp,
            $bongkarTotal,
            $pasangTotal,
            $transportFee,
            $releaseAt,
            $type,
            $status,
            $hold
        ) {
            // ─── Teknisi Bongkar ──────────────────────────────
            if ($order->technician_id && $bongkarTotal > 0) {
                $techBongkar = Technician::find($order->technician_id);
                if ($techBongkar) {
                    $grade = $techBongkar->grade ?? 'beginner';
                    $rates = self::GRADE_RATES[$grade] ?? self::GRADE_RATES['beginner'];

                    $techShare = round($bongkarTotal * $rates['technician'] / 100, 2);
                    $bpShare   = round($bongkarTotal * $rates['bp'] / 100, 2);
                    $acdShare  = round($bongkarTotal * $rates['acd'] / 100, 2);

                    // Teknisi bongkar
                    if ($hold) {
                        $before = (float) $techBongkar->balance_hold;
                        $techBongkar->increment('balance_hold', $techShare);
                    } else {
                        $before = (float) $techBongkar->balance;
                        $techBongkar->increment('balance', $techShare);
                    }
                    BalanceTransaction::create([
                        'owner_type'     => Technician::class,
                        'owner_id'       => $techBongkar->id,
                        'order_id'       => $order->id,
                        'type'           => $type,
                        'amount'         => $techShare,
                        'balance_before' => $before,
                        'balance_after'  => $before + $techShare,
                        'description'    => "Fee bongkar order #{$order->id} (grade: {$grade}, {$rates['technician']}%)",
                        'status'         => $status,
                        'release_at'     => $releaseAt,
                    ]);

                    // BP dari bongkar
                    $bpBefore = (float) $bp->balance;
                    $bp->increment('balance', $bpShare);
                    BalanceTransaction::create([
                        'owner_type'     => BusinessPartner::class,
                        'owner_id'       => $bp->id,
                        'order_id'       => $order->id,
                        'type'           => $type,
                        'amount'         => $bpShare,
                        'balance_before' => $bpBefore,
                        'balance_after'  => $bpBefore + $bpShare,
                        'description'    => "Fee bongkar order #{$order->id} ({$rates['bp']}%)",
                        'status'         => $status,
                        'release_at'     => $releaseAt,
                    ]);

                    // ACD dari bongkar
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
                        'release_at'     => null,
                    ]);
                }
            }

            // ─── Teknisi Pasang ───────────────────────────────
            $pasangTechId = $order->split_technician
                ? $order->second_technician_id
                : $order->technician_id;

            if ($pasangTechId && $pasangTotal > 0) {
                $techPasang = Technician::find($pasangTechId);
                if ($techPasang) {
                    $grade = $techPasang->grade ?? 'beginner';
                    $rates = self::GRADE_RATES[$grade] ?? self::GRADE_RATES['beginner'];

                    $techShare = round($pasangTotal * $rates['technician'] / 100, 2);
                    $bpShare   = round($pasangTotal * $rates['bp'] / 100, 2);
                    $acdShare  = round($pasangTotal * $rates['acd'] / 100, 2);

                    // Teknisi pasang
                    if ($hold) {
                        $before = (float) $techPasang->balance_hold;
                        $techPasang->increment('balance_hold', $techShare);
                    } else {
                        $before = (float) $techPasang->balance;
                        $techPasang->increment('balance', $techShare);
                    }
                    BalanceTransaction::create([
                        'owner_type'     => Technician::class,
                        'owner_id'       => $techPasang->id,
                        'order_id'       => $order->id,
                        'type'           => $type,
                        'amount'         => $techShare,
                        'balance_before' => $before,
                        'balance_after'  => $before + $techShare,
                        'description'    => "Fee pasang order #{$order->id} (grade: {$grade}, {$rates['technician']}%)",
                        'status'         => $status,
                        'release_at'     => $releaseAt,
                    ]);

                    // BP dari pasang
                    $bpBefore = (float) $bp->fresh()->balance;
                    $bp->increment('balance', $bpShare);
                    BalanceTransaction::create([
                        'owner_type'     => BusinessPartner::class,
                        'owner_id'       => $bp->id,
                        'order_id'       => $order->id,
                        'type'           => $type,
                        'amount'         => $bpShare,
                        'balance_before' => $bpBefore,
                        'balance_after'  => $bpBefore + $bpShare,
                        'description'    => "Fee pasang order #{$order->id} ({$rates['bp']}%)",
                        'status'         => $status,
                        'release_at'     => $releaseAt,
                    ]);

                    // ACD dari pasang
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
                        'release_at'     => null,
                    ]);
                }
            }

            // ─── Transport fee → BP langsung ──────────────────
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
                    'release_at'     => null,
                ]);
            }
        });

        Log::info('Relocation balance distributed', [
            'order_id'      => $order->id,
            'hold'          => $hold,
            'bongkar_total' => $bongkarTotal,
            'pasang_total'  => $pasangTotal,
            'transport_fee' => $transportFee,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // RELEASE saldo yang sudah melewati masa holding
    // ─────────────────────────────────────────────────────────────
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
    public function releaseSubscriptionSessionEarning(SubscriptionSession $session): void
    {
        $subscription = $session->subscription()->with('package')->first();

        if (!$session->technician_id) {
            Log::warning('Cannot distribute subscription session: no technician', [
                'session_id' => $session->id,
            ]);
            return;
        }

        $technician = Technician::find($session->technician_id);
        $bp         = BusinessPartner::find($subscription->bp_id);

        if (!$technician || !$bp) {
            Log::warning('Cannot distribute subscription session: technician or BP not found', [
                'session_id'    => $session->id,
                'technician_id' => $session->technician_id,
                'bp_id'         => $subscription->bp_id,
            ]);
            return;
        }

        $grade = $technician->grade ?? 'beginner';
        $rates = self::GRADE_RATES[$grade] ?? self::GRADE_RATES['beginner'];

        // Earning per sesi = total yang dibayar customer / jumlah sesi paket
        $earningPerSession = round(
            (float) $subscription->total_amount / $subscription->package->total_sessions,
            2
        );

        $techShare = round($earningPerSession * $rates['technician'] / 100, 2);
        $bpShare   = round($earningPerSession * $rates['bp'] / 100, 2);
        $acdShare  = round($earningPerSession * $rates['acd'] / 100, 2);

        DB::transaction(function () use (
            $session,
            $subscription,
            $technician,
            $bp,
            $techShare,
            $bpShare,
            $acdShare,
            $grade,
            $rates,
            $earningPerSession
        ) {
            $desc = "Sesi ke-{$session->session_number} langganan #{$subscription->id}";

            // ─── Teknisi — langsung (customer sudah konfirmasi, tidak perlu hold) ───
            $techBefore = (float) $technician->balance;
            $technician->increment('balance', $techShare);
            BalanceTransaction::create([
                'owner_type'     => Technician::class,
                'owner_id'       => $technician->id,
                'order_id'       => null,
                'type'           => 'release',
                'amount'         => $techShare,
                'balance_before' => $techBefore,
                'balance_after'  => $techBefore + $techShare,
                'description'    => "Pendapatan {$desc} (grade: {$grade}, {$rates['technician']}%)",
                'status'         => 'completed',
                'release_at'     => null,
            ]);

            // ─── BP — langsung ────────────────────────────────────────────────────
            $bpBefore = (float) $bp->balance;
            $bp->increment('balance', $bpShare);
            BalanceTransaction::create([
                'owner_type'     => BusinessPartner::class,
                'owner_id'       => $bp->id,
                'order_id'       => null,
                'type'           => 'release',
                'amount'         => $bpShare,
                'balance_before' => $bpBefore,
                'balance_after'  => $bpBefore + $bpShare,
                'description'    => "Pendapatan {$desc} ({$rates['bp']}%)",
                'status'         => 'completed',
                'release_at'     => null,
            ]);

            // ─── ACD — langsung ───────────────────────────────────────────────────
            $acd = AcdBalance::getInstance();
            $acd->increment('balance', $acdShare);
            $acd->increment('total_earned', $acdShare);
            BalanceTransaction::create([
                'owner_type'     => AcdBalance::class,
                'owner_id'       => $acd->id,
                'order_id'       => null,
                'type'           => 'earning',
                'amount'         => $acdShare,
                'balance_before' => (float) $acd->balance - $acdShare,
                'balance_after'  => (float) $acd->balance,
                'description'    => "Pendapatan ACD {$desc} ({$rates['acd']}%)",
                'status'         => 'completed',
                'release_at'     => null,
            ]);
        });

        Log::info('Subscription session balance distributed', [
            'session_id'        => $session->id,
            'subscription_id'   => $subscription->id,
            'session_number'    => $session->session_number,
            'earning_per_session' => $earningPerSession,
            'tech_share'        => $techShare,
            'bp_share'          => $bpShare,
            'acd_share'         => $acdShare,
        ]);
    }
}
