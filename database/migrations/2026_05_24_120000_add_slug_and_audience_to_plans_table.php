<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (! Schema::hasColumn('plans', 'slug')) {
                $table->string('slug', 64)->nullable()->unique()->after('name');
            }
            if (! Schema::hasColumn('plans', 'audience')) {
                $table->string('audience', 32)->nullable()->default('healthcare')->after('slug');
                $table->index('audience');
            }
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans', 'audience')) {
                $table->dropIndex(['audience']);
                $table->dropColumn('audience');
            }
            if (Schema::hasColumn('plans', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }
};
