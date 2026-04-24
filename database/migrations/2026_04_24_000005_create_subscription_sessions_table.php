<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->integer('session_number');                          // sesi ke-1, ke-2, dst
            $table->date('scheduled_date');
            $table->string('scheduled_time');                           // "10:00"
            $table->foreignId('technician_id')->nullable()->constrained('technicians');

            $table->enum('status', [
                'scheduled',            // jadwal sudah diset customer
                'confirmed',            // admin sudah assign teknisi
                'in_progress',          // teknisi sedang mengerjakan
                'waiting_confirmation', // teknisi selesai, tunggu konfirmasi customer
                'completed',            // customer konfirmasi / auto-complete
                'cancelled',
            ])->default('scheduled');

            $table->timestamp('auto_complete_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_sessions');
    }
};
