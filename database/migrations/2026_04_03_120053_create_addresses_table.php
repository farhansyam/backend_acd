<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Jenis properti
            $table->enum('property_type', ['rumah', 'kantor', 'apartemen']);

            // Nama alamat
            $table->string('label'); // misal: "Rumah Utama", "Kantor Jakarta"

            // Wilayah
            $table->string('province_id');
            $table->string('province_name');
            $table->string('city_id');
            $table->string('city_name');
            $table->string('district_id');
            $table->string('district_name');
            $table->string('village_id')->nullable();
            $table->string('village_name')->nullable();

            // Alamat lengkap
            $table->text('full_address');

            // Koordinat GPS
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Alamat utama yang dipilih
            $table->boolean('is_primary')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
