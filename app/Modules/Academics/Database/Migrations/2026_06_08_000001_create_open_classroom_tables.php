<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_open_classrooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('subject_area', 120)->nullable();
            $table->string('visibility', 20)->default('public'); // public | unlisted
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('members_count')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'visibility'], 'aoc_active_vis_idx');
            $table->index('owner_id', 'aoc_owner_idx');
        });

        Schema::create('academic_open_classroom_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('open_classroom_id')->constrained('academic_open_classrooms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            $table->unique(['open_classroom_id', 'user_id'], 'aocm_room_user_uniq');
            $table->index('user_id', 'aocm_user_idx');
        });

        Schema::create('academic_open_classroom_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('open_classroom_id')->constrained('academic_open_classrooms')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('resource_type', 30)->default('file'); // file | video_link | note
            $table->string('video_url', 500)->nullable();
            $table->string('file_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['open_classroom_id', 'sort_order'], 'aocr_room_sort_idx');
        });

        Schema::create('academic_open_classroom_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('open_classroom_id')->constrained('academic_open_classrooms')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->json('attachments')->nullable();
            $table->json('checklist_items')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['open_classroom_id', 'due_date'], 'aoca_room_due_idx');
        });

        Schema::create('academic_open_classroom_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('academic_open_classroom_assignments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('file_path')->nullable();
            $table->string('original_name')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('checklist_answers')->nullable();
            $table->decimal('checklist_points_earned', 8, 2)->nullable();
            $table->decimal('checklist_points_possible', 8, 2)->nullable();
            $table->timestamps();

            $table->unique(['assignment_id', 'user_id'], 'aocs_assign_user_uniq');
        });

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'is_open_teacher')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_open_teacher')->default(false);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_open_classroom_submissions');
        Schema::dropIfExists('academic_open_classroom_assignments');
        Schema::dropIfExists('academic_open_classroom_resources');
        Schema::dropIfExists('academic_open_classroom_members');
        Schema::dropIfExists('academic_open_classrooms');

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'is_open_teacher')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_open_teacher');
            });
        }
    }
};
