<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah enum status orders untuk tambah waiting_confirmation
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'pending',
            'confirmed',
            'in_progress',
            'waiting_confirmation',
            'completed',
            'cancelled'
        ) DEFAULT 'pending'");

        Schema::table('orders', function (Blueprint $table) {
            // Waktu auto-complete kalau customer tidak konfirmasi
            $table->timestamp('auto_complete_at')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('auto_complete_at');
        });

        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'pending',
            'confirmed',
            'in_progress',
            'completed',
            'cancelled'
        ) DEFAULT 'pending'");
    }
};
