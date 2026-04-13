<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class WarrantyExpireCommand extends Command
{
    protected $signature   = 'orders:expire-warranty';
    protected $description = 'Ubah status order warranty yang sudah habis menjadi completed';

    public function handle(): void
    {
        $expired = Order::where('status', 'warranty')
            ->where('warranty_expires_at', '<=', now())
            ->get();

        foreach ($expired as $order) {
            $order->update(['status' => 'completed']);
            $this->info("Order #{$order->id} warranty expired → completed");
        }

        $this->info("Total: {$expired->count()} order diproses.");
    }
}
