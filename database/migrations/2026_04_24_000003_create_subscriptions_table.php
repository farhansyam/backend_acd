<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('address_id')->constrained('addresses');
            $table->foreignId('user_phone_id')->constrained('user_phones');
            $table->foreignId('bp_id')->constrained('business_partners');
            $table->foreignId('subscription_package_id')->constrained('subscription_packages');

            // Harga
            $table->decimal('subtotal', 12, 2)->default(0);        // sebelum diskon
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);    // yang dibayar

            // Pembayaran (ikuti pola orders)
            $table->string('payment_method')->nullable();
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
            $table->string('tripay_reference')->nullable();
            $table->string('tripay_payment_url')->nullable();
            $table->timestamp('paid_at')->nullable();

            // Masa aktif
            $table->date('starts_at')->nullable();       // di-set saat bayar
            $table->date('expires_at')->nullable();      // starts_at + 1 tahun

            $table->enum('status', [
                'pending',      // belum bayar
                'active',       // sudah bayar, sedang berjalan
                'completed',    // semua sesi selesai
                'cancelled',
            ])->default('pending');

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
