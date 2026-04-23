<?php
// database/migrations/2026_04_23_000004_create_survey_reports_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');          // order fase 1
            $table->unsignedBigInteger('technician_id');
            $table->enum('kondisi_unit', ['normal', 'kotor', 'rusak']);
            $table->json('bagian_bermasalah')->nullable();    // ["kompresor","freon","filter","pcb","fan","lainnya"]
            $table->text('catatan')->nullable();
            $table->enum('rekomendasi', ['cuci_unit', 'perbaikan']);
            $table->string('photo_before')->nullable();
            $table->string('photo_after')->nullable();
            $table->enum('customer_response', ['lanjut', 'tidak'])->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('technician_id')->references('id')->on('technicians')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_reports');
    }
};
