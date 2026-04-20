<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tambah category baru
        DB::statement("ALTER TABLE service_types MODIFY COLUMN category 
        ENUM('cuci_reguler','pasang_baru','unit','relokasi','relokasi_bongkar','relokasi_pasang','perbaikan')
        DEFAULT 'cuci_reguler'");

        // Tambah status baru
        DB::statement("ALTER TABLE orders MODIFY COLUMN status 
        ENUM(
            'pending',
            'pending_transport_fee',
            'pending_transport_fee_set',
            'confirmed',
            'in_progress',
            'disassembled',
            'waiting_confirmation',
            'warranty',
            'complained',
            'rework_assigned',
            'rework_completed',
            'closed',
            'completed',
            'cancelled'
        ) NOT NULL DEFAULT 'pending'");
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
