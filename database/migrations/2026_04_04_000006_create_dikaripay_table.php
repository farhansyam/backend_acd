<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah balance ke users
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('balance', 12, 2)->default(0)->after('fcm_token');
        });

        // Tabel riwayat transaksi DikariPay
        Schema::create('dikaripay_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', [
                'topup',    // isi saldo
                'payment',  // bayar order
                'refund',   // refund
            ]);
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_before', 12, 2)->default(0);
            $table->decimal('balance_after', 12, 2)->default(0);
            $table->foreignId('order_id')->nullable()->constrained();
            $table->string('tripay_reference')->nullable(); // untuk topup via Tripay
            $table->string('payment_method')->nullable();  // metode topup
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dikaripay_transactions');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('balance');
        });
    }
};
