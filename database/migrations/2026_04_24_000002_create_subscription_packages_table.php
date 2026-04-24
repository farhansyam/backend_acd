<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_packages', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['hemat', 'rutin', 'intensif'])->unique();
            $table->string('name');                        // "Paket Hemat", dll
            $table->integer('interval_months');            // 6, 3, 1
            $table->integer('total_sessions');             // 2, 4, 12
            $table->decimal('price_multiplier', 5, 2);    // misal 0.90 = diskon 10%
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_packages');
    }
};
