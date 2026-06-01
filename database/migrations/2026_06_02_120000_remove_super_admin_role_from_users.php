<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop legacy super_admin role from users.role enum.
     * Run `php artisan academics:purge-super-admin-users` before migrate if any super_admin rows remain.
     */
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (DB::table('users')->where('role', 'super_admin')->whereNull('deleted_at')->exists()) {
            throw new \RuntimeException(
                'Active users with role super_admin still exist. Run: php artisan academics:purge-super-admin-users'
            );
        }

        // Soft-deleted rows still hold the enum value and block ALTER TABLE.
        DB::table('users')->where('role', 'super_admin')->delete();

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'admin',
                'nurse',
                'caregiver',
                'patient',
                'institution_admin',
                'faculty',
                'student',
            ])->default('patient')->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'admin',
                'nurse',
                'caregiver',
                'patient',
                'super_admin',
                'institution_admin',
                'faculty',
                'student',
            ])->default('patient')->change();
        });
    }
};
