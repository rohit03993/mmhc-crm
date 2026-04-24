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
        if (!Schema::hasColumn('referrals', 'payment_processed')) {
            Schema::table('referrals', function (Blueprint $table) {
                $table->boolean('payment_processed')->default(false);
            });
        }

        if (!Schema::hasColumn('referrals', 'payment_processed_at')) {
            Schema::table('referrals', function (Blueprint $table) {
                $table->timestamp('payment_processed_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('referrals', 'payment_processed')) {
            Schema::table('referrals', function (Blueprint $table) {
                $table->dropColumn('payment_processed');
            });
        }

        if (Schema::hasColumn('referrals', 'payment_processed_at')) {
            Schema::table('referrals', function (Blueprint $table) {
                $table->dropColumn('payment_processed_at');
            });
        }
    }
};

