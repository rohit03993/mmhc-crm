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
        Schema::table('caregiver_rewards', function (Blueprint $table) {
            $table->boolean('payment_processed')->default(false)->after('reward_amount');
            $table->timestamp('payment_processed_at')->nullable()->after('payment_processed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('caregiver_rewards', function (Blueprint $table) {
            $table->dropColumn(['payment_processed', 'payment_processed_at']);
        });
    }
};

