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
            $table->boolean('unit_installed')->default(false)->after('electrical_checked');
            $table->boolean('piping_neat')->default(false)->after('unit_installed');
            $table->boolean('cooling_test')->default(false)->after('piping_neat');
            $table->boolean('remote_working')->default(false)->after('cooling_test');
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
