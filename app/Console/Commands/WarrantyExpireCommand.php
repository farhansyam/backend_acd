<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class WarrantyExpireCommand extends Command
{
    protected $signature   = 'orders:expire-warranty';
    protected $description = 'Expire warranty orders dan kirim notifikasi';

    public function __construct(private NotificationService $notificationService)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        // ─── H-1: notif customer garansi hampir habis ─────────
        $expiringSoon = Order::with(['user'])
            ->where('status', 'warranty')
            ->whereBetween('warranty_expires_at', [now(), now()->addDay()])
            ->get();

        foreach ($expiringSoon as $order) {
            if ($order->user?->fcm_token) {
                $this->notificationService->notifyWarrantyExpiringSoon(
                    $order->user->fcm_token,
                    $order->id
                );
            }
            $this->info("Order #{$order->id} — notif H-1 garansi dikirim.");
        }

        // ─── Expired: ubah status + notif teknisi ─────────────
        $expired = Order::with(['user', 'technician.user', 'secondTechnician.user'])
            ->where('status', 'warranty')
            ->where('warranty_expires_at', '<=', now())
            ->get();

        foreach ($expired as $order) {
            $order->update(['status' => 'completed']);

            // Notif customer
            if ($order->user?->fcm_token) {
                $this->notificationService->notifyOrderCompleted(
                    $order->user->fcm_token,
                    $order->id
                );
            }

            // Notif teknisi bongkar / tunggal
            if ($order->technician?->user?->fcm_token) {
                $this->notificationService->notifyWarrantyExpired(
                    $order->technician->user->fcm_token,
                    $order->id
                );
            }

            // Notif teknisi pasang (relokasi beda lokasi)
            if ($order->split_technician && $order->secondTechnician?->user?->fcm_token) {
                $this->notificationService->notifyWarrantyExpired(
                    $order->secondTechnician->user->fcm_token,
                    $order->id
                );
            }

            $this->info("Order #{$order->id} warranty expired → completed.");
        }

        $this->info("Done. Expiring soon: {$expiringSoon->count()}, Expired: {$expired->count()}.");
    }
}
