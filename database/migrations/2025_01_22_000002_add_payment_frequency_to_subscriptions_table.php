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
            $table->enum('payment_frequency', ['monthly', 'half_yearly', 'annually', 'full_payment'])->default('monthly')->after('plan_id');
            $table->integer('care_benefits_years')->default(0)->after('end_date'); // Extra care benefits years (3, 5, or 7)
            $table->integer('payable_years')->default(0)->after('care_benefits_years'); // Years payable (3, 5, or 7)
            $table->decimal('total_amount', 10, 2)->default(0.00)->after('payable_years');
            $table->decimal('paid_amount', 10, 2)->default(0.00)->after('total_amount');
            $table->enum('payment_status', ['pending', 'partially_paid', 'paid', 'failed'])->default('pending')->after('paid_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'payment_frequency',
                'care_benefits_years',
                'payable_years',
                'total_amount',
                'paid_amount',
                'payment_status'
            ]);
        });
    }
};

