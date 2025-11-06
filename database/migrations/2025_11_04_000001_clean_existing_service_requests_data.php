<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clean up existing service requests data:
        // 1. Set preferred_staff_id to NULL for invalid staff references
        // 2. Set assigned_staff_id to NULL and reset status for invalid staff references
        // 3. Ensure payment fields have proper defaults
        
        // Clean invalid preferred_staff_id (staff that doesn't exist, is not active, or is not a staff member)
        DB::statement("
            UPDATE service_requests sr
            LEFT JOIN users u_pref ON sr.preferred_staff_id = u_pref.id 
                AND u_pref.role IN ('nurse', 'caregiver') 
                AND u_pref.is_active = 1
            SET sr.preferred_staff_id = NULL
            WHERE sr.preferred_staff_id IS NOT NULL 
            AND u_pref.id IS NULL
        ");
        
        // Clean invalid assigned_staff_id (staff that doesn't exist, is not active, or is not a staff member)
        DB::statement("
            UPDATE service_requests sr
            LEFT JOIN users u_assigned ON sr.assigned_staff_id = u_assigned.id 
                AND u_assigned.role IN ('nurse', 'caregiver') 
                AND u_assigned.is_active = 1
            SET sr.assigned_staff_id = NULL,
                sr.status = 'pending',
                sr.assigned_at = NULL
            WHERE sr.assigned_staff_id IS NOT NULL 
            AND u_assigned.id IS NULL
        ");
        
        // Ensure prepaid_amount is not NULL
        DB::statement("
            UPDATE service_requests 
            SET prepaid_amount = 0.00 
            WHERE prepaid_amount IS NULL
        ");
        
        // Ensure payment_status is not empty
        DB::statement("
            UPDATE service_requests 
            SET payment_status = 'pending' 
            WHERE payment_status IS NULL OR payment_status = ''
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration only cleans data, nothing to reverse
    }
};

