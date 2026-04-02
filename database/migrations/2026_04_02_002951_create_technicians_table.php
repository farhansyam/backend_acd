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
        Schema::create('technicians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('bp_id')->nullable()->constrained('business_partners')->onDelete('set null');

            // Data wilayah
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();

            // Grade (diisi BP saat approval)
            $table->enum('grade', ['beginner', 'medium', 'pro'])->nullable();

            // Dokumen
            $table->string('skck_file')->nullable();       // path file
            $table->string('ktp_photo')->nullable();       // path foto KTP
            $table->string('selfie_photo')->nullable();    // path foto selfie
            $table->string('certificate')->nullable();     // path sertifikat keahlian

            // Kolom template untuk penambahan data nanti
            $table->string('extra_doc_1')->nullable();     // dokumen tambahan 1
            $table->string('extra_doc_2')->nullable();     // dokumen tambahan 2
            $table->text('extra_note')->nullable();        // catatan tambahan

            // Status approval
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();  // alasan reject dari BP
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technicians');
    }
};
