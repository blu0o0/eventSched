<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old TOTP columns if they exist
        if (Schema::hasColumn('users', 'google2fa_secret')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['google2fa_secret', 'google2fa_enabled', 'backup_codes']);
            });
        }

        // Drop old password_resets table if it exists from the previous migration
        Schema::dropIfExists('password_resets');

        // Add email_verified column to users
        if (!Schema::hasColumn('users', 'email_verified')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('email_verified')->default(false)->after('email');
            });
        }

        // Create OTP codes table
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('otp', 6);
            $table->string('type'); // 'email_verification' or 'password_reset'
            $table->boolean('is_used')->default(false);
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
        if (Schema::hasColumn('users', 'email_verified')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('email_verified');
            });
        }
    }
};