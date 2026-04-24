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
        if (!Schema::hasColumn('subscriptions', 'referral_payment_processed')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->boolean('referral_payment_processed')->default(false);
            });
        }

        if (!Schema::hasColumn('subscriptions', 'referral_payment_processed_at')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->timestamp('referral_payment_processed_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('subscriptions', 'referral_payment_processed')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropColumn('referral_payment_processed');
            });
        }

        if (Schema::hasColumn('subscriptions', 'referral_payment_processed_at')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropColumn('referral_payment_processed_at');
            });
        }
    }
};

