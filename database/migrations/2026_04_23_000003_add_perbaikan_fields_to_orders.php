<?php
// database/migrations/2026_04_23_000003_add_perbaikan_fields_to_orders.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_perbaikan')->default(false)->after('order_type');
            $table->enum('perbaikan_phase', ['survey', 'phase2'])->nullable()->after('is_perbaikan');
            $table->unsignedBigInteger('phase2_order_id')->nullable()->after('perbaikan_phase');
            $table->unsignedBigInteger('survey_order_id')->nullable()->after('phase2_order_id');

            $table->foreign('phase2_order_id')->references('id')->on('orders')->nullOnDelete();
            $table->foreign('survey_order_id')->references('id')->on('orders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['phase2_order_id']);
            $table->dropForeign(['survey_order_id']);
            $table->dropColumn(['is_perbaikan', 'perbaikan_phase', 'phase2_order_id', 'survey_order_id']);
        });
    }
};
