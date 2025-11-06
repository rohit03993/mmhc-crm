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
        Schema::table('service_requests', function (Blueprint $table) {
            // Store the specific staff member the patient selected from Available Staff page
            $table->foreignId('preferred_staff_id')->nullable()->constrained('users')->onDelete('set null')->after('preferred_staff_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropForeign(['preferred_staff_id']);
            $table->dropColumn('preferred_staff_id');
        });
    }
};

