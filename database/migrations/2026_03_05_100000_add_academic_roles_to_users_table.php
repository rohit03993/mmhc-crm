<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add academic module roles to the users.role enum.
     */
    public function up(): void
    {
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'nurse', 'caregiver', 'patient'])->default('patient')->change();
        });
    }
};
