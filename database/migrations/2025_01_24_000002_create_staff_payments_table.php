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
        Schema::create('staff_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->enum('payment_type', ['service_request', 'patient_reward', 'staff_referral', 'subscription_referral']);
            $table->decimal('amount', 10, 2);
            $table->string('transaction_id')->nullable();
            $table->text('notes')->nullable();
            $table->string('payment_screenshot')->nullable();
            $table->timestamp('paid_at');
            $table->timestamps();

            // Indexes
            $table->index('staff_id');
            $table->index('admin_id');
            $table->index('payment_type');
            $table->index('paid_at');
            $table->index(['staff_id', 'payment_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_payments');
    }
};

