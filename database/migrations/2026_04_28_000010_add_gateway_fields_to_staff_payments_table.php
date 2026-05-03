<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_payments', function (Blueprint $table) {
            $table->string('payment_provider', 40)->nullable()->after('amount');
            $table->string('payment_mode', 20)->default('manual')->after('payment_provider');
            $table->string('gateway_status', 40)->nullable()->after('payment_mode');
            $table->string('gateway_reference_id')->nullable()->after('gateway_status');
            $table->json('gateway_payload')->nullable()->after('gateway_reference_id');
            $table->string('beneficiary_upi')->nullable()->after('gateway_payload');

            $table->index(['payment_mode', 'gateway_status']);
            $table->index('gateway_reference_id');
        });
    }

    public function down(): void
    {
        Schema::table('staff_payments', function (Blueprint $table) {
            $table->dropIndex(['payment_mode', 'gateway_status']);
            $table->dropIndex(['gateway_reference_id']);

            $table->dropColumn([
                'payment_provider',
                'payment_mode',
                'gateway_status',
                'gateway_reference_id',
                'gateway_payload',
                'beneficiary_upi',
            ]);
        });
    }
};
