<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_deletion_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('target_user_id');
            $table->string('target_role', 32);
            $table->string('target_unique_id', 32)->nullable();
            $table->string('original_phone', 32)->nullable();
            $table->string('original_email')->nullable();
            $table->json('stats')->nullable();
            $table->timestamps();

            $table->index(['target_user_id', 'created_at']);
            $table->index(['admin_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_deletion_logs');
    }
};
