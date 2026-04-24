<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->foreignId('bp_service_id')->constrained('bp_services');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);           // harga satuan per sesi (dari bp_services)
            $table->decimal('subtotal_per_session', 10, 2); // unit_price * quantity
            $table->decimal('subtotal_total', 10, 2);       // subtotal_per_session * total_sessions
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_items');
    }
};
