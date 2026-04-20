<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\BalanceService;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoCompleteOrdersCommand extends Command
{
    protected $signature   = 'orders:auto-complete';
    protected $description = 'Auto-complete orders that have passed the 30 minute confirmation deadline';

    public function __construct(
        private BalanceService $balanceService,
        private NotificationService $notificationService
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $orders = Order::with(['user', 'technician.user'])
            ->where('status', 'waiting_confirmation')
            ->where('auto_complete_at', '<=', now())
            ->get();

        foreach ($orders as $order) {
            DB::transaction(function () use ($order) {
                $order->update([
                    'status'               => 'warranty',
                    'warranty_started_at'  => now(),
                    'warranty_expires_at'  => now()->addDays(7),
                    'auto_complete_at'     => null,
                ]);

                if ($order->order_type === 'relokasi') {
                    $this->balanceService->distributeRelocationEarning($order);
                } else {
                    $this->balanceService->distributeOrderEarning($order);
                }
            });

            // Notifikasi customer — masa garansi aktif
            if ($order->user->fcm_token) {
                $this->notificationService->notifyWarrantyActive(
                    $order->user->fcm_token,
                    $order->id
                );
            }

            $this->info("Order #{$order->id} → warranty aktif.");
        }

        $this->info("Done. {$orders->count()} orders diproses.");
    }
}
