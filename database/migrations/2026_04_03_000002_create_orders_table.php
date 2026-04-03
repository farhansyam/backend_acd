<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Customer
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Kontak & Alamat
            $table->foreignId('user_phone_id')->constrained('user_phones');
            $table->foreignId('address_id')->constrained('addresses');

            // BP yang handle (di-assign saat order dibuat berdasarkan wilayah)
            $table->foreignId('bp_id')->nullable()->constrained('business_partners');

            // Jadwal
            $table->date('scheduled_date');
            $table->string('scheduled_time'); // misal: "09:00"

            // Biaya tambahan apartemen
            $table->decimal('apartment_surcharge', 10, 2)->default(0);

            // Total
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);

            // Status
            $table->enum('status', [
                'pending',      // menunggu konfirmasi BP
                'confirmed',    // BP sudah konfirmasi
                'in_progress',  // teknisi sedang mengerjakan
                'completed',    // selesai
                'cancelled',    // dibatalkan
            ])->default('pending');

            $table->text('notes')->nullable(); // catatan dari customer

            $table->timestamps();
        });

        // Detail item layanan per order
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bp_service_id')->constrained('bp_services');
            $table->integer('quantity'); // jumlah AC
            $table->decimal('unit_price', 10, 2); // harga saat order (snapshot)
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2); // (unit_price - discount) * quantity
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
