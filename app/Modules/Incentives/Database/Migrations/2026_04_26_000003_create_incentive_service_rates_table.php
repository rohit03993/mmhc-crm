<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incentive_service_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_set_id')->constrained('incentive_rule_sets')->cascadeOnDelete();
            $table->string('visit_kind', 16);
            $table->string('experience_tier', 16);
            $table->boolean('is_subscriber_patient');
            $table->string('unit', 8);
            $table->decimal('rate_per_unit', 12, 2);
            $table->timestamps();

            $table->index(['rule_set_id', 'visit_kind', 'experience_tier', 'is_subscriber_patient'], 'isr_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incentive_service_rates');
    }
};
