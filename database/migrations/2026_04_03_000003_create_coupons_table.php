<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel kupon
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();           // kode kupon, misal: DINGIN10
            $table->string('name');                     // nama deskriptif
            $table->decimal('discount_percent', 5, 2); // misal: 10.00 = 10%
            $table->decimal('max_discount', 10, 2)->nullable(); // maksimal potongan nominal
            $table->decimal('min_order', 10, 2)->default(0);   // minimal order untuk pakai kupon
            $table->boolean('all_services')->default(true);     // berlaku semua layanan?
            $table->date('valid_from');
            $table->date('valid_until');
            $table->integer('max_usage_per_user')->default(1);  // batas per user
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Relasi kupon ke service tertentu (kalau all_services = false)
        Schema::create('coupon_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bp_service_id')->constrained('bp_services')->cascadeOnDelete();
        });

        // Riwayat pemakaian kupon per user per order
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->decimal('discount_amount', 10, 2); // nominal diskon yang didapat
            $table->timestamps();
        });

        // Tambah kolom pembayaran di tabel orders
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('coupon_id')->nullable()->constrained('coupons');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('apartment_surcharge');
            $table->string('payment_method')->nullable()->after('total_amount');
            $table->string('payment_status')->default('unpaid')->after('payment_method');
            $table->string('tripay_reference')->nullable()->after('payment_status');
            $table->string('tripay_payment_url')->nullable()->after('tripay_reference');
            $table->timestamp('paid_at')->nullable()->after('tripay_payment_url');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'coupon_id',
                'discount_amount',
                'payment_method',
                'payment_status',
                'tripay_reference',
                'tripay_payment_url',
                'paid_at',
            ]);
        });
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupon_services');
        Schema::dropIfExists('coupons');
    }
};
