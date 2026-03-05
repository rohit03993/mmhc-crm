<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('academic_institutions')->cascadeOnDelete();
            $table->string('name');
            $table->string('academic_year', 20)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['institution_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_batches');
    }
};
