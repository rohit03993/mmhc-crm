<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('academic_subjects')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->timestamps();

            $table->index(['subject_id', 'is_completed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_topics');
    }
};
