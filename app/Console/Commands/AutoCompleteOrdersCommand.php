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
    protected $description = 'Auto-complete orders that have passed the confirmation deadline';

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
                    'status'           => 'completed',
                    'auto_complete_at' => null,
                ]);

                $this->balanceService->distributeOrderEarning($order);
            });

            // Notifikasi customer
            if ($order->user->fcm_token) {
                $this->notificationService->notifyOrderCompleted(
                    $order->user->fcm_token,
                    $order->id
                );
            }

            $this->info("Auto-completed order #{$order->id}");
        }

        $this->info("Done. {$orders->count()} orders auto-completed.");
    }
}
