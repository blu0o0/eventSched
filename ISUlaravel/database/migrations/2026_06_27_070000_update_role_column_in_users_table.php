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
            // Drop the old enum column and create a new one with all role values
            $table->dropColumn('role');
            $table->enum('role', [
                'administrator',
                'SBO BSIT WMAD',
                'SBO BSIT NETSEC',
                'SBO BSA',
                'SBL BSLEA',
                'SSC OFFICER',
                'FACULTY/STAFF',
                'STUDENT'
            ])->default('STUDENT')->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
            $table->enum('role', ['administrator', 'main_proponent', 'general_user'])->default('general_user')->after('email');
        });
    }
};