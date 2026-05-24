<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'phone_verified_by_admin_id')) {
                $table->foreignId('phone_verified_by_admin_id')
                    ->nullable()
                    ->after('phone_verified_source')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'phone_verified_by_admin_id')) {
                $table->dropForeign(['phone_verified_by_admin_id']);
                $table->dropColumn('phone_verified_by_admin_id');
            }
        });
    }
};
