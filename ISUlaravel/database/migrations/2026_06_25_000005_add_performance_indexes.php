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
        Schema::table('reservations', function (Blueprint $table) {
            $table->index('status');
            $table->index('date');
            $table->index('updated_at');
            $table->index(['user_id', 'status']);
        });

        if (Schema::hasTable('venues')) {
            Schema::table('venues', function (Blueprint $table) {
                $table->index('location');
                $table->index('status');
                $table->index('updated_at');
            });
        }

        if (Schema::hasTable('emergency_reports')) {
            Schema::table('emergency_reports', function (Blueprint $table) {
                $table->index('status');
                $table->index('updated_at');
            });
        }
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['date']);
            $table->dropIndex(['updated_at']);
            $table->dropIndex(['user_id', 'status']);
        });

        if (Schema::hasTable('venues')) {
            Schema::table('venues', function (Blueprint $table) {
                $table->dropIndex(['location']);
                $table->dropIndex(['status']);
                $table->dropIndex(['updated_at']);
            });
        }

        if (Schema::hasTable('emergency_reports')) {
            Schema::table('emergency_reports', function (Blueprint $table) {
                $table->dropIndex(['status']);
                $table->dropIndex(['updated_at']);
            });
        }
    }
};
