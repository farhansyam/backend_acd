<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('warranty_expires_at')->nullable()->after('auto_complete_at');
            $table->timestamp('warranty_started_at')->nullable()->after('warranty_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['warranty_expires_at', 'warranty_started_at']);
        });
    }
};
