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
        Schema::table('service_requests', function (Blueprint $table) {
            // Staff payout calculation
            $table->decimal('total_staff_payout', 10, 2)->nullable()->after('total_amount');
            
            // Payment tracking
            $table->decimal('prepaid_amount', 10, 2)->default(0.00)->after('total_staff_payout');
            $table->enum('payment_status', ['pending', 'partially_paid', 'paid', 'refunded'])->default('pending')->after('prepaid_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn(['total_staff_payout', 'prepaid_amount', 'payment_status']);
        });
    }
};
