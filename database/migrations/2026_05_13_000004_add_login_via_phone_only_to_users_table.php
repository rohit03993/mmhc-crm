<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'login_via_phone_only')) {
                $table->boolean('login_via_phone_only')->default(false)->after('phone_verified_source');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'login_via_phone_only')) {
                $table->dropColumn('login_via_phone_only');
            }
        });
    }
};
