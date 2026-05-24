<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_submission_mentor_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('academic_submissions')->cascadeOnDelete();
            $table->foreignId('mentor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mentorship_id')->nullable()->constrained('academic_mentorships')->nullOnDelete();
            $table->timestamps();

            $table->unique(['submission_id', 'mentor_id'], 'acad_sub_mshare_uq');
        });

        Schema::create('academic_submission_mentor_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('academic_submissions')->cascadeOnDelete();
            $table->foreignId('mentor_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('feedback')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['submission_id', 'mentor_id'], 'acad_sub_mreview_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_submission_mentor_reviews');
        Schema::dropIfExists('academic_submission_mentor_shares');
    }
};
