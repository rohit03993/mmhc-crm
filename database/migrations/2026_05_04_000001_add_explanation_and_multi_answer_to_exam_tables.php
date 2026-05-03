<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_exam_questions', function (Blueprint $table) {
            $table->text('explanation')->nullable()->after('points');
        });

        Schema::table('academic_exam_attempt_answers', function (Blueprint $table) {
            $table->json('selected_option_ids')->nullable()->after('option_id');
        });
    }

    public function down(): void
    {
        Schema::table('academic_exam_questions', function (Blueprint $table) {
            $table->dropColumn('explanation');
        });

        Schema::table('academic_exam_attempt_answers', function (Blueprint $table) {
            $table->dropColumn('selected_option_ids');
        });
    }
};
