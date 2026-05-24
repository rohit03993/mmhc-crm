<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('academic_submissions', 'mentor_verified_at')) {
            Schema::table('academic_submissions', function (Blueprint $table) {
                $table->timestamp('mentor_verified_at')->nullable()->after('submitted_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('academic_submissions', 'mentor_verified_at')) {
            Schema::table('academic_submissions', function (Blueprint $table) {
                $table->dropColumn('mentor_verified_at');
            });
        }
    }
};
