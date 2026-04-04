<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Assign order ke teknisi ──────────────────────────
        Schema::create('order_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technician_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users'); // BP user_id
            $table->enum('status', [
                'assigned',     // sudah di-assign
                'accepted',     // teknisi terima
                'in_progress',  // sedang dikerjakan
                'completed',    // selesai
                'rejected',     // teknisi tolak
            ])->default('assigned');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ─── Riwayat transaksi saldo ──────────────────────────
        Schema::create('balance_transactions', function (Blueprint $table) {
            $table->id();
            $table->morphs('owner'); // bisa teknisi atau BP (polymorphic)
            $table->foreignId('order_id')->nullable()->constrained();
            $table->enum('type', [
                'earning',   // pendapatan dari order
                'withdraw',  // penarikan saldo
                'hold',      // saldo ditahan
                'release',   // saldo dilepas setelah holding
            ]);
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_before', 12, 2)->default(0);
            $table->decimal('balance_after', 12, 2)->default(0);
            $table->string('description')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('completed');
            $table->timestamp('release_at')->nullable(); // untuk holding
            $table->timestamps();
        });

        // ─── Saldo ACD ────────────────────────────────────────
        Schema::create('acd_balances', function (Blueprint $table) {
            $table->id();
            $table->decimal('balance', 12, 2)->default(0);
            $table->decimal('total_earned', 12, 2)->default(0);
            $table->decimal('total_withdrawn', 12, 2)->default(0);
            $table->timestamps();
        });

        // ─── Tambah kolom saldo ke teknisi ────────────────────
        Schema::table('technicians', function (Blueprint $table) {
            $table->decimal('balance', 12, 2)->default(0)->after('grade');
            $table->decimal('balance_hold', 12, 2)->default(0)->after('balance');
        });

        // ─── Tambah assigned_technician_id ke orders ──────────
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('technician_id')
                ->nullable()
                ->after('bp_id')
                ->constrained('technicians');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['technician_id']);
            $table->dropColumn('technician_id');
        });
        Schema::table('technicians', function (Blueprint $table) {
            $table->dropColumn(['balance', 'balance_hold']);
        });
        Schema::dropIfExists('acd_balances');
        Schema::dropIfExists('balance_transactions');
        Schema::dropIfExists('order_assignments');
    }
};
