<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'academic_enrollment_status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('academic_enrollment_status', 20)->nullable()->after('academic_institution_id');
            });
        }

        if (Schema::hasColumn('users', 'academic_enrollment_status')) {
            DB::table('users')
                ->where('role', 'student')
                ->whereNull('academic_enrollment_status')
                ->update(['academic_enrollment_status' => 'approved']);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'academic_enrollment_status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('academic_enrollment_status');
            });
        }
    }
};
