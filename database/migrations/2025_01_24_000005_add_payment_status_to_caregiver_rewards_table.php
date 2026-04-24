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
        if (!Schema::hasTable('caregiver_rewards')) {
            return;
        }

        if (!Schema::hasColumn('caregiver_rewards', 'payment_processed')) {
            Schema::table('caregiver_rewards', function (Blueprint $table) {
                $table->boolean('payment_processed')->default(false);
            });
        }

        if (!Schema::hasColumn('caregiver_rewards', 'payment_processed_at')) {
            Schema::table('caregiver_rewards', function (Blueprint $table) {
                $table->timestamp('payment_processed_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('caregiver_rewards')) {
            return;
        }

        if (Schema::hasColumn('caregiver_rewards', 'payment_processed')) {
            Schema::table('caregiver_rewards', function (Blueprint $table) {
                $table->dropColumn('payment_processed');
            });
        }

        if (Schema::hasColumn('caregiver_rewards', 'payment_processed_at')) {
            Schema::table('caregiver_rewards', function (Blueprint $table) {
                $table->dropColumn('payment_processed_at');
            });
        }
    }
};

