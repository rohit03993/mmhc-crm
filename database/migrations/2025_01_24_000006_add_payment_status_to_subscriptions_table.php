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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->boolean('referral_payment_processed')->default(false)->after('referral_commission_rate');
            $table->timestamp('referral_payment_processed_at')->nullable()->after('referral_payment_processed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['referral_payment_processed', 'referral_payment_processed_at']);
        });
    }
};

