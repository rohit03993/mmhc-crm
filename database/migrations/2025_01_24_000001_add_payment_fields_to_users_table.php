<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'upi_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('upi_id')->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'qr_code_path')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('qr_code_path')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'upi_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('upi_id');
            });
        }

        if (Schema::hasColumn('users', 'qr_code_path')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('qr_code_path');
            });
        }
    }
};

