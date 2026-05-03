<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_assignments', function (Blueprint $table) {
            $table->json('checklist_items')->nullable()->after('attachments');
        });

        Schema::table('academic_submissions', function (Blueprint $table) {
            $table->json('checklist_answers')->nullable()->after('notes');
            $table->decimal('checklist_points_earned', 10, 2)->nullable()->after('checklist_answers');
            $table->decimal('checklist_points_possible', 10, 2)->nullable()->after('checklist_points_earned');
        });
    }

    public function down(): void
    {
        Schema::table('academic_assignments', function (Blueprint $table) {
            $table->dropColumn('checklist_items');
        });

        Schema::table('academic_submissions', function (Blueprint $table) {
            $table->dropColumn(['checklist_answers', 'checklist_points_earned', 'checklist_points_possible']);
        });
    }
};
