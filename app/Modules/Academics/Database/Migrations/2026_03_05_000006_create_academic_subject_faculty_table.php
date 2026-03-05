<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_subject_faculty', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('academic_subjects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['subject_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_subject_faculty');
    }
};
