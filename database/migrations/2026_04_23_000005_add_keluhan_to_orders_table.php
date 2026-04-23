<?php
// database/migrations/2026_04_23_000005_add_keluhan_to_orders_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->json('keluhan')->nullable()->after('notes');
            $table->text('keluhan_lainnya')->nullable()->after('keluhan');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['keluhan', 'keluhan_lainnya']);
        });
    }
};
