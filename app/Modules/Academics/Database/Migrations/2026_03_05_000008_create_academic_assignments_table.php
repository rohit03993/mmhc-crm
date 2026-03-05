<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('academic_topics')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->json('attachments')->nullable(); // [{"path": "...", "name": "file.pdf"}, ...]
            $table->timestamps();

            $table->index(['topic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_assignments');
    }
};
