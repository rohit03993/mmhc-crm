<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_topic_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('academic_topics')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('resource_type', 32)->default('video_link'); // video_link, file, checklist
            $table->string('video_url')->nullable();
            $table->string('file_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['topic_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_topic_resources');
    }
};
