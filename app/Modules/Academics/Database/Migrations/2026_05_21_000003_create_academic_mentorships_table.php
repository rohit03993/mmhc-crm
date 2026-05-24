<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_mentorships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mentor_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('pending');
            $table->text('request_message')->nullable();
            $table->text('response_message')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['mentee_id', 'mentor_id']);
            $table->index(['mentor_id', 'status']);
            $table->index(['mentee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_mentorships');
    }
};
