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
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
        'pending',
        'pending_transport_fee',
        'pending_transport_fee_set',
        'confirmed',
        'in_progress',
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
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
