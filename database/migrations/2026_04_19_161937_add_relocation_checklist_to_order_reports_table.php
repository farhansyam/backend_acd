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
        Schema::table('order_reports', function (Blueprint $table) {
            $table->boolean('ac_dismantled')->default(false)->after('remote_working');
            $table->boolean('unit_safe_transport')->default(false)->after('ac_dismantled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_reports', function (Blueprint $table) {
            //
        });
    }
};
