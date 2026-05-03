<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_osce_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('academic_institutions')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('academic_batches')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(120);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['institution_id', 'batch_id']);
        });

        Schema::create('academic_osce_stations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('osce_session_id')->constrained('academic_osce_sessions')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('name');
            $table->text('instructions')->nullable();
            $table->unsignedInteger('time_limit_seconds')->nullable();
            $table->json('checklist_items')->nullable();
            $table->timestamps();

            $table->index(['osce_session_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_osce_stations');
        Schema::dropIfExists('academic_osce_sessions');
    }
};
