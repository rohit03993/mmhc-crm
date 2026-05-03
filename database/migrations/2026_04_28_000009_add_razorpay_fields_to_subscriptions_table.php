<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('payment_provider', 40)->nullable()->after('payment_status');
            $table->string('gateway_status', 40)->nullable()->after('payment_provider');
            $table->json('gateway_payload')->nullable()->after('gateway_status');

            $table->string('razorpay_order_id')->nullable()->after('gateway_payload');
            $table->string('razorpay_payment_id')->nullable()->after('razorpay_order_id');
            $table->text('razorpay_signature')->nullable()->after('razorpay_payment_id');
            $table->string('razorpay_event_id')->nullable()->after('razorpay_signature');
            $table->timestamp('webhook_received_at')->nullable()->after('razorpay_event_id');

            $table->index('razorpay_order_id');
            $table->index('razorpay_payment_id');
            $table->unique('razorpay_event_id');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropUnique(['razorpay_event_id']);
            $table->dropIndex(['razorpay_order_id']);
            $table->dropIndex(['razorpay_payment_id']);

            $table->dropColumn([
                'payment_provider',
                'gateway_status',
                'gateway_payload',
                'razorpay_order_id',
                'razorpay_payment_id',
                'razorpay_signature',
                'razorpay_event_id',
                'webhook_received_at',
            ]);
        });
    }
};
