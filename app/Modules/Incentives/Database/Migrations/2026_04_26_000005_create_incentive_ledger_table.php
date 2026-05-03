<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incentive_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_set_id')->constrained('incentive_rule_sets');
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->string('source_type', 32);
            $table->unsignedBigInteger('source_id');
            $table->decimal('base_amount', 14, 2);
            $table->unsignedInteger('service_count_at_event')->default(0);
            $table->string('snapshot_visit_kind', 16)->nullable();
            $table->string('snapshot_experience_tier', 16)->nullable();
            $table->boolean('snapshot_subscriber_patient')->nullable();
            $table->decimal('growth_percent', 8, 4)->default(0);
            $table->decimal('dta_percent', 8, 4)->default(0);
            $table->decimal('pre_adjustment_amount', 14, 2);
            $table->decimal('adjustment_amount', 14, 2)->default(0);
            $table->string('adjustment_reason')->nullable();
            $table->decimal('final_amount', 14, 2);
            $table->boolean('payment_settled')->default(false);
            $table->timestamp('settled_at')->nullable();
            $table->unsignedBigInteger('staff_payment_id')->nullable();
            $table->timestamps();

            $table->unique(['source_type', 'source_id']);
            $table->index(['staff_id', 'payment_settled']);
            $table->foreign('staff_payment_id')->references('id')->on('staff_payments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incentive_ledger');
    }
};
