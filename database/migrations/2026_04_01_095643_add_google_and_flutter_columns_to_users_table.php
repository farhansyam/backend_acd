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
        Schema::table('users', function (Blueprint $table) {
            // Google Login
            $table->string('google_id')->nullable()->after('avatar');
            $table->text('google_token')->nullable()->after('google_id');
            $table->text('google_refresh_token')->nullable()->after('google_token');

            // Flutter mobile
            $table->text('fcm_token')->nullable()->after('google_refresh_token');
            $table->timestamp('last_login_at')->nullable()->after('fcm_token');
            $table->string('device_type')->nullable()->after('last_login_at'); // android/ios
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'google_id',
                'google_token',
                'google_refresh_token',
                'fcm_token',
                'last_login_at',
                'device_type',
            ]);
        });
    }
};
