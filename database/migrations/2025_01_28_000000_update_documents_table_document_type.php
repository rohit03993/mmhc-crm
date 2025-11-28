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
        // Change document_type from enum to string to allow role-specific types
        Schema::table('documents', function (Blueprint $table) {
            // First, we need to drop the enum constraint
            // MySQL doesn't support direct enum modification, so we'll use raw SQL
        });
        
        // Use raw SQL to modify the column
        DB::statement("ALTER TABLE documents MODIFY COLUMN document_type VARCHAR(50) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert back to enum (only if needed)
        DB::statement("ALTER TABLE documents MODIFY COLUMN document_type ENUM('certificate', 'id_proof', 'medical_license', 'insurance', 'other') NOT NULL");
    }
};

