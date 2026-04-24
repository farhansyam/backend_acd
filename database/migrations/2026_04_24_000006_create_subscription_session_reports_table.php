<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_session_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_session_id')->constrained('subscription_sessions')->cascadeOnDelete();
            $table->foreignId('technician_id')->constrained('technicians');

            // Foto (sama dengan order_reports)
            $table->string('photo_before');
            $table->string('photo_after');
            $table->text('notes')->nullable();

            // Checklist cuci reguler (sama dengan order_reports)
            $table->boolean('filter_cleaned')->default(false);
            $table->boolean('freon_checked')->default(false);
            $table->boolean('drain_cleaned')->default(false);
            $table->boolean('electrical_checked')->default(false);
            $table->boolean('unit_installed')->default(false);
            $table->boolean('piping_neat')->default(false);
            $table->boolean('cooling_test')->default(false);
            $table->boolean('remote_working')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_session_reports');
    }
};
