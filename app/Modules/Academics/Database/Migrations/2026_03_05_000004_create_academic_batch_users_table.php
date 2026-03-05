<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_batch_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('academic_batches')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 20); // 'student' | 'faculty'
            $table->timestamps();

            $table->unique(['batch_id', 'user_id']);
            $table->index(['batch_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_batch_users');
    }
};
