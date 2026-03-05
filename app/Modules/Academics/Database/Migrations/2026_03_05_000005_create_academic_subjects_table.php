<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('academic_batches')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['batch_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_subjects');
    }
};
