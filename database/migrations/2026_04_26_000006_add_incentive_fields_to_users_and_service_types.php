<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'experience_tier')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('experience_tier', 16)->nullable()->after('experience');
            });
        }

        if (Schema::hasTable('service_types') && ! Schema::hasColumn('service_types', 'incentive_visit_kind')) {
            Schema::table('service_types', function (Blueprint $table) {
                $table->string('incentive_visit_kind', 16)->nullable()->after('duration_hours');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'experience_tier')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('experience_tier');
            });
        }
        if (Schema::hasTable('service_types') && Schema::hasColumn('service_types', 'incentive_visit_kind')) {
            Schema::table('service_types', function (Blueprint $table) {
                $table->dropColumn('incentive_visit_kind');
            });
        }
    }
};
