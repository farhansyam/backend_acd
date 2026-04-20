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
        Schema::table('orders', function (Blueprint $table) {
            // Tipe relokasi
            $table->enum('relocation_type', ['same_location', 'different_location'])
                ->nullable()->after('notes');

            // Alamat asal (beda lokasi)
            $table->unsignedBigInteger('origin_address_id')->nullable()->after('relocation_type');

            // Biaya transportasi (diset BP)
            $table->decimal('transport_fee', 12, 2)->default(0)->after('origin_address_id');

            // Apakah pakai 2 teknisi berbeda
            $table->boolean('split_technician')->default(false)->after('transport_fee');

            // Teknisi kedua (pasang) untuk kasus beda teknisi
            $table->unsignedBigInteger('second_technician_id')->nullable()->after('split_technician');

            $table->foreign('second_technician_id')->references('id')->on('technicians')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
