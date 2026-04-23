<?php
// app/Services/SurveyBalanceService.php

namespace App\Services;

use App\Models\AcdBalance;
use App\Models\BalanceTransaction;
use App\Models\BusinessPartner;
use App\Models\Order;
use App\Models\Technician;

class SurveyBalanceService
{
    public function release(Order $order): void
    {
        // Tentukan order survey & fase2
        if ($order->perbaikan_phase === 'survey') {
            $surveyOrder = $order;
            $phase2Order = $order->phase2_order_id
                ? Order::find($order->phase2_order_id)
                : null;
        } else {
            $surveyOrder = Order::find($order->survey_order_id);
            $phase2Order = $order;
        }

        if (!$surveyOrder) return;

        $totalAmount = (float) $surveyOrder->total_amount
            + (float) ($phase2Order?->total_amount ?? 0);

        /** @var \App\Models\Technician $technician */
        $technician = Technician::find($surveyOrder->technician_id);
        if (!$technician) return;

        /** @var \App\Models\BusinessPartner $bp */
        $bp = BusinessPartner::find($surveyOrder->bp_id);
        if (!$bp) return;

        $techPercent = $this->getTechPercent($technician->grade);
        $bpPercent   = 100 - $techPercent - 15;

        $techEarning = round($totalAmount * ($techPercent / 100), 2);
        $bpEarning   = round($totalAmount * ($bpPercent / 100), 2);
        $acdEarning  = round($totalAmount * 0.15, 2);

        $label = $phase2Order
            ? "Pendapatan Service Perbaikan (survey + fase2) order #{$surveyOrder->id}"
            : "Pendapatan Survey Perbaikan order #{$surveyOrder->id}";

        // ─── Release ke teknisi ───────────────────────────────
        $techUser = $technician->user;
        BalanceTransaction::create([
            'owner_type'     => Technician::class,
            'owner_id'       => $technician->id,
            'order_id'       => $surveyOrder->id,
            'type'           => 'earning',
            'amount'         => $techEarning,
            'balance_before' => (float) $techUser->balance,
            'balance_after'  => (float) $techUser->balance + $techEarning,
            'description'    => $label . " (grade: {$technician->grade}, {$techPercent}%)",
            'status'         => 'completed',
        ]);
        $techUser->increment('balance', $techEarning);

        // Notif teknisi saldo masuk
        if ($techUser->fcm_token) {
            app(NotificationService::class)->notifyBalanceReleased(
                $techUser->fcm_token,
                $techEarning,
                $surveyOrder->id
            );
        }

        // ─── Release ke BP ────────────────────────────────────
        BalanceTransaction::create([
            'owner_type'     => BusinessPartner::class,
            'owner_id'       => $bp->id,
            'order_id'       => $surveyOrder->id,
            'type'           => 'earning',
            'amount'         => $bpEarning,
            'balance_before' => (float) $bp->balance,
            'balance_after'  => (float) $bp->balance + $bpEarning,
            'description'    => $label . " ({$bpPercent}%)",
            'status'         => 'completed',
        ]);
        $bp->increment('balance', $bpEarning);

        // ─── Release ke ACD ───────────────────────────────────
        /** @var \App\Models\AcdBalance $acdBalance */
        $acdBalance = AcdBalance::first();
        if ($acdBalance) {
            BalanceTransaction::create([
                'owner_type'     => AcdBalance::class,
                'owner_id'       => $acdBalance->id,
                'order_id'       => $surveyOrder->id,
                'type'           => 'earning',
                'amount'         => $acdEarning,
                'balance_before' => (float) $acdBalance->balance,
                'balance_after'  => (float) $acdBalance->balance + $acdEarning,
                'description'    => $label . ' (ACD 15%)',
                'status'         => 'completed',
            ]);
            $acdBalance->increment('balance', $acdEarning);
        }

        // ─── Garansi — hanya jika ada fase 2 ─────────────────
        if ($phase2Order) {
            $warrantyDays  = 7;
            $warrantyStart = now();
            $warrantyEnd   = now()->addDays($warrantyDays);

            $phase2Order->update([
                'warranty_started_at' => $warrantyStart,
                'warranty_expires_at' => $warrantyEnd,
                'status'              => 'warranty',
            ]);

            // Notif customer garansi aktif
            $customerFcm = $phase2Order->user?->fcm_token;
            if ($customerFcm) {
                app(NotificationService::class)->notifyWarrantyActive(
                    $customerFcm,
                    (int) $phase2Order->id
                );
            }
        }
    }

    private function getTechPercent(string $grade): int
    {
        return match ($grade) {
            'medium' => 65,
            'expert' => 75,
            default  => 55, // beginner
        };
    }
}
