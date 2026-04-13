<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technician_id')->constrained()->cascadeOnDelete();
            $table->string('photo_before');
            $table->string('photo_after');
            $table->text('notes')->nullable();
            // Checklist kondisi unit
            $table->boolean('filter_cleaned')->default(false);
            $table->boolean('freon_checked')->default(false);
            $table->boolean('drain_cleaned')->default(false);
            $table->boolean('electrical_checked')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_reports');
    }
};
