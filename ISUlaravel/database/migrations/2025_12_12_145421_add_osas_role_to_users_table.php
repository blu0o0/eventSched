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
        // Drop the existing role column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
        
        // Recreate with osas role added
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['administrator', 'osas', 'main_proponent', 'general_user'])->default('general_user')->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the role column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
        
        // Recreate without osas role
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['administrator', 'main_proponent', 'general_user'])->default('general_user')->after('email');
        });
    }
};
