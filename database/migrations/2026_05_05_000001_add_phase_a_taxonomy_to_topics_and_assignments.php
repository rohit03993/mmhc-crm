<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_topics', function (Blueprint $table) {
            $table->json('teaching_method_keys')->nullable()->after('is_completed');
        });

        Schema::table('academic_assignments', function (Blueprint $table) {
            $table->string('assignment_type', 32)->default('file_upload')->after('topic_id');
            $table->json('assessment_type_keys')->nullable()->after('description');
            $table->boolean('is_formative')->default(true)->after('assessment_type_keys');
            $table->boolean('is_summative')->default(false)->after('is_formative');
            $table->boolean('eval_includes_mcq')->default(false)->after('is_summative');
            $table->boolean('eval_includes_practical')->default(false)->after('eval_includes_mcq');
            $table->boolean('eval_includes_viva')->default(false)->after('eval_includes_practical');
            $table->boolean('eval_includes_checklist')->default(false)->after('eval_includes_viva');
        });
    }

    public function down(): void
    {
        Schema::table('academic_topics', function (Blueprint $table) {
            $table->dropColumn('teaching_method_keys');
        });

        Schema::table('academic_assignments', function (Blueprint $table) {
            $table->dropColumn([
                'assignment_type',
                'assessment_type_keys',
                'is_formative',
                'is_summative',
                'eval_includes_mcq',
                'eval_includes_practical',
                'eval_includes_viva',
                'eval_includes_checklist',
            ]);
        });
    }
};
