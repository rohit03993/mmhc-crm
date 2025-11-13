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
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->string('referral_code', 20)->unique()->index();
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('referred_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->integer('reward_points')->default(1); // Points earned for this referral
            $table->decimal('reward_amount', 10, 2)->default(10.00); // Amount earned (1 point = ₹10)
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('referrer_id');
            $table->index('referred_id');
            $table->index('status');
            $table->index(['referrer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};

