<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_exams', function (Blueprint $table) {
            $table->foreignId('assignment_id')
                ->nullable()
                ->after('batch_id')
                ->constrained('academic_assignments')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('academic_exams', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assignment_id');
        });
    }
};
