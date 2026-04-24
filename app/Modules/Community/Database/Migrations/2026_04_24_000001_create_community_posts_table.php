<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('post_type', ['text', 'image', 'event']);
            $table->text('content')->nullable();
            $table->string('image_path')->nullable();
            $table->string('event_title')->nullable();
            $table->dateTime('event_date')->nullable();
            $table->string('event_location')->nullable();
            $table->timestamps();

            $table->index(['created_at']);
            $table->index(['post_type', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['event_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_posts');
    }
};

