<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds spatial POINT column and spatial index for optimized location searches
     */
    public function up(): void
    {
        // Check if column already exists (in case migration was partially run)
        $columnExists = DB::select("SHOW COLUMNS FROM users LIKE 'location'");
        
        if (empty($columnExists)) {
            // MySQL spatial indexes require NOT NULL, so we need a different approach
            // Step 1: Add POINT column as NOT NULL with default (sentinel value for missing coordinates)
            // Using POINT(0 0) as sentinel - this is in the middle of the ocean, won't match real locations
            DB::statement("ALTER TABLE users ADD COLUMN location POINT NOT NULL DEFAULT (ST_GeomFromText('POINT(0 0)', 4326)) AFTER longitude");
        } else {
            // Column exists but might be NULL - need to convert to NOT NULL
            // First, populate any NULL values with sentinel
            DB::statement("
                UPDATE users 
                SET location = ST_GeomFromText('POINT(0 0)', 4326)
                WHERE location IS NULL
            ");
            
            // Update existing NULL location column to NOT NULL with default
            DB::statement("
                ALTER TABLE users 
                MODIFY COLUMN location POINT NOT NULL DEFAULT (ST_GeomFromText('POINT(0 0)', 4326))
            ");
        }

        // Step 2: Populate POINT column from existing latitude/longitude data
        DB::statement("
            UPDATE users 
            SET location = ST_GeomFromText(
                CONCAT('POINT(', longitude, ' ', latitude, ')'),
                4326
            )
            WHERE latitude IS NOT NULL 
            AND longitude IS NOT NULL
            AND (location IS NULL OR location = ST_GeomFromText('POINT(0 0)', 4326))
        ");

        // Step 3: Check if spatial index exists before creating
        $indexExists = DB::select("SHOW INDEXES FROM users WHERE Key_name = 'idx_location_spatial'");
        
        if (empty($indexExists)) {
            // Create spatial index (now that column is NOT NULL)
            DB::statement('ALTER TABLE users ADD SPATIAL INDEX idx_location_spatial (location)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop spatial index first
        DB::statement('ALTER TABLE users DROP INDEX idx_location_spatial');
        
        // Drop POINT column (must use raw SQL)
        DB::statement('ALTER TABLE users DROP COLUMN location');
    }
};

