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
        Schema::create('bp_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bp_id')->constrained('business_partners')->onDelete('cascade');
            $table->foreignId('service_type_id')->constrained('service_types')->onDelete('cascade');
            $table->decimal('base_service', 15, 2)->default(0);
            $table->decimal('discount', 5, 2)->default(0);
            $table->integer('is_active')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bp_services');
    }
};
