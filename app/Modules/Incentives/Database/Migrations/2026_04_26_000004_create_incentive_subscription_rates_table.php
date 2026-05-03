<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incentive_subscription_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_set_id')->constrained('incentive_rule_sets')->cascadeOnDelete();
            $table->string('payment_frequency', 32);
            $table->decimal('commission_percent', 8, 4);
            $table->timestamps();

            $table->unique(['rule_set_id', 'payment_frequency'], 'isub_rule_freq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incentive_subscription_rates');
    }
};
