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
        Schema::table('order_ratings', function (Blueprint $table) {
            $table->foreignId('second_technician_id')->nullable()->after('technician_id')->constrained('technicians')->nullOnDelete();
            $table->tinyInteger('second_rating')->nullable()->after('second_technician_id');
            $table->text('second_review')->nullable()->after('second_rating');
        });
    }

    public function down(): void
    {
        Schema::table('order_ratings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('second_technician_id');
            $table->dropColumn(['second_rating', 'second_review']);
        });
    }
};
