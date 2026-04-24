<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriptionPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'type'             => 'hemat',
                'name'             => 'Paket Hemat',
                'interval_months'  => 6,
                'total_sessions'   => 2,
                'price_multiplier' => 0.90, // 10% lebih murah dari harga normal × sesi
                'description'      => 'Cuci AC 2x setahun, jadwal setiap 6 bulan sekali.',
                'is_active'        => true,
            ],
            [
                'type'             => 'rutin',
                'name'             => 'Paket Rutin',
                'interval_months'  => 3,
                'total_sessions'   => 4,
                'price_multiplier' => 0.85,
                'description'      => 'Cuci AC 4x setahun, jadwal setiap 3 bulan sekali.',
                'is_active'        => true,
            ],
            [
                'type'             => 'intensif',
                'name'             => 'Paket Intensif',
                'interval_months'  => 1,
                'total_sessions'   => 12,
                'price_multiplier' => 0.75,
                'description'      => 'Cuci AC 12x setahun, jadwal setiap bulan sekali.',
                'is_active'        => true,
            ],
        ];

        foreach ($packages as $package) {
            DB::table('subscription_packages')->updateOrInsert(
                ['type' => $package['type']],
                array_merge($package, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
