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
        Schema::create('daily_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained('service_requests')->onDelete('cascade');
            $table->foreignId('staff_id')->constrained('users')->onDelete('cascade');
            $table->date('service_date');
            $table->datetime('start_time');
            $table->datetime('end_time');
            $table->decimal('patient_charge', 10, 2);
            $table->decimal('staff_payout', 10, 2);
            $table->decimal('platform_profit', 10, 2);
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->text('patient_feedback')->nullable();
            $table->text('staff_notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('service_date');
            $table->index('status');
            $table->index(['staff_id', 'service_date']);
            $table->index(['service_request_id', 'service_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_services');
    }
};
