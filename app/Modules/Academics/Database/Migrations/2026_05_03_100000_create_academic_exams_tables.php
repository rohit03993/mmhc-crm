<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('academic_institutions')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('audience_type', 32);
            $table->foreignId('subject_id')->nullable()->constrained('academic_subjects')->nullOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('academic_batches')->nullOnDelete();
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->unsignedTinyInteger('max_attempts')->default(1);
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('shuffle_options')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->boolean('allows_cross_institution')->default(false);
            $table->timestamps();

            $table->index(['institution_id', 'is_published']);
            $table->index(['audience_type', 'subject_id']);
            $table->index(['audience_type', 'batch_id']);
        });

        Schema::create('academic_exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('academic_exams')->cascadeOnDelete();
            $table->text('body');
            $table->string('question_type', 32)->default('mcq_single');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->decimal('points', 8, 2)->default(1);
            $table->timestamps();

            $table->index(['exam_id', 'sort_order']);
        });

        Schema::create('academic_exam_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('academic_exam_questions')->cascadeOnDelete();
            $table->string('label', 8)->nullable();
            $table->text('body');
            $table->boolean('is_correct')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['question_id', 'sort_order']);
        });

        Schema::create('academic_exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('academic_exams')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 24)->default('in_progress');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('score', 10, 2)->nullable();
            $table->timestamps();

            $table->index(['exam_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('academic_exam_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('academic_exam_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('academic_exam_questions')->cascadeOnDelete();
            $table->foreignId('option_id')->nullable()->constrained('academic_exam_options')->nullOnDelete();
            $table->timestamps();

            $table->unique(['attempt_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_exam_attempt_answers');
        Schema::dropIfExists('academic_exam_attempts');
        Schema::dropIfExists('academic_exam_options');
        Schema::dropIfExists('academic_exam_questions');
        Schema::dropIfExists('academic_exams');
    }
};
