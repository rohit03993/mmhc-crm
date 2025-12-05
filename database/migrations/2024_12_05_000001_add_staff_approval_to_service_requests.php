<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            // Add staff approval fields
            $table->timestamp('staff_approved_at')->nullable()->after('assigned_at');
            $table->timestamp('staff_rejected_at')->nullable()->after('staff_approved_at');
            $table->text('staff_rejection_reason')->nullable()->after('staff_rejected_at');
        });

        // Update status enum to include 'pending_approval'
        // Note: MySQL doesn't support ALTER ENUM directly, so we'll handle this in the model
        DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM('pending', 'pending_approval', 'assigned', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn(['staff_approved_at', 'staff_rejected_at', 'staff_rejection_reason']);
        });

        // Revert status enum
        DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM('pending', 'assigned', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending'");
    }
};

