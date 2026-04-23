<?php
// database/migrations/2026_04_23_000001_add_service_perbaikan_to_service_types.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah category baru ke enum
        DB::statement("ALTER TABLE `service_types` MODIFY `category` enum(
            'cuci_reguler',
            'pasang_baru',
            'unit',
            'relokasi',
            'relokasi_pasang',
            'relokasi_bongkar',
            'service_perbaikan_survey',
            'service_perbaikan_service'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `service_types` MODIFY `category` enum(
            'cuci_reguler',
            'pasang_baru',
            'unit',
            'relokasi',
            'relokasi_pasang',
            'relokasi_bongkar'
        ) NOT NULL");
    }
};
