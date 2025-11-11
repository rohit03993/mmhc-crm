<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('caregiver_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('patient_name');
            $table->string('patient_phone', 20);
            $table->string('hospital_name');
            $table->text('treatment_details')->nullable();
            $table->unsignedInteger('reward_points')->default(1);
            $table->decimal('reward_amount', 10, 2)->default(10.00);
            $table->timestamps();

            $table->index('patient_phone');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caregiver_rewards');
    }
};

