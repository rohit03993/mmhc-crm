<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('academic_assignments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('file_path'); // single file for submission
            $table->string('original_name')->nullable();
            $table->timestamp('submitted_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['assignment_id', 'user_id']);
            $table->index(['assignment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_submissions');
    }
};
