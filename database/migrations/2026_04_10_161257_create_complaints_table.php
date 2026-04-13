<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bp_id')->constrained('business_partners')->cascadeOnDelete();
            $table->foreignId('technician_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rework_technician_id')->nullable()->constrained('technicians')->nullOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('photo')->nullable();
            $table->text('bp_comment')->nullable();
            $table->enum('status', ['open', 'in_review', 'rework_assigned', 'rework_completed', 'closed'])->default('open');
            $table->decimal('rework_cost', 15, 2)->default(0);
            $table->decimal('rework_earning', 15, 2)->default(0);
            $table->timestamp('warranty_expires_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
