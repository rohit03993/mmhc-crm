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
            // GST fields
            $table->decimal('base_amount', 10, 2)->nullable()->after('total_amount');
            $table->decimal('gst_amount', 10, 2)->default(0.00)->after('base_amount');
            $table->decimal('gst_rate', 5, 2)->default(18.00)->after('gst_amount');
            
            // Referral tracking
            $table->unsignedBigInteger('referrer_id')->nullable()->after('user_id')->comment('Staff member (nurse/caregiver) who referred this subscription');
            $table->decimal('referral_commission_amount', 10, 2)->default(0.00)->after('referrer_id');
            $table->decimal('referral_commission_rate', 5, 2)->default(5.00)->after('referral_commission_amount');
            
            // Foreign key for referrer
            $table->foreign('referrer_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['referrer_id']);
            $table->dropColumn([
                'base_amount',
                'gst_amount',
                'gst_rate',
                'referrer_id',
                'referral_commission_amount',
                'referral_commission_rate',
            ]);
        });
    }
};

