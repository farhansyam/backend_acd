<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `service_types`
            MODIFY COLUMN `category` ENUM(
                'cuci_reguler',
                'pasang_baru',
                'unit',
                'relokasi',
                'relokasi_pasang',
                'relokasi_bongkar',
                'service_perbaikan_survey',
                'service_perbaikan_service',
                'cuci_langganan'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE `service_types`
            MODIFY COLUMN `category` ENUM(
                'cuci_reguler',
                'pasang_baru',
                'unit',
                'relokasi',
                'relokasi_pasang',
                'relokasi_bongkar',
                'service_perbaikan_survey',
                'service_perbaikan_service'
            ) NOT NULL
        ");
    }
};
