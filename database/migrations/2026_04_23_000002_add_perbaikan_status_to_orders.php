<?php
// database/migrations/2026_04_23_000002_add_perbaikan_status_to_orders.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `orders` MODIFY `status` enum(
            'pending',
            'pending_transport_fee',
            'pending_transport_fee_set',
            'confirmed',
            'in_progress',
            'survey_in_progress',
            'waiting_customer_response',
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

    public function down(): void
    {
        DB::statement("ALTER TABLE `orders` MODIFY `status` enum(
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
};
