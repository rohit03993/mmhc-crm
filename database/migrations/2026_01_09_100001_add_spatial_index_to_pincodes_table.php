<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds spatial POINT column and spatial index for optimized pincode location searches
     */
    public function up(): void
    {
        // Check if column already exists (in case migration was partially run)
        $columnExists = DB::select("SHOW COLUMNS FROM pincodes LIKE 'location'");
        
        if (empty($columnExists)) {
            // Step 1: Add POINT column as nullable first (MySQL doesn't allow functions in DEFAULT)
            DB::statement("ALTER TABLE pincodes ADD COLUMN location POINT NULL AFTER longitude");
            
            // Step 2: Populate POINT column from existing latitude/longitude data
            DB::statement("
                UPDATE pincodes 
                SET location = ST_GeomFromText(
                    CONCAT('POINT(', longitude, ' ', latitude, ')'),
                    4326
                )
                WHERE latitude IS NOT NULL 
                AND longitude IS NOT NULL
            ");
            
            // Step 3: Set sentinel value POINT(0 0) for pincodes without coordinates
            DB::statement("
                UPDATE pincodes 
                SET location = ST_GeomFromText('POINT(0 0)', 4326)
                WHERE location IS NULL
            ");
            
            // Step 4: Convert to NOT NULL (now all rows have values)
            DB::statement("ALTER TABLE pincodes MODIFY COLUMN location POINT NOT NULL");
        } else {
            // Column exists - handle existing data
            // Populate NULL values with sentinel
            DB::statement("
                UPDATE pincodes 
                SET location = ST_GeomFromText('POINT(0 0)', 4326)
                WHERE location IS NULL
            ");
            
            // Update from lat/lng if location is sentinel value
            DB::statement("
                UPDATE pincodes 
                SET location = ST_GeomFromText(
                    CONCAT('POINT(', longitude, ' ', latitude, ')'),
                    4326
                )
                WHERE latitude IS NOT NULL 
                AND longitude IS NOT NULL
                AND (location IS NULL OR location = ST_GeomFromText('POINT(0 0)', 4326))
            ");
            
            // Convert to NOT NULL if not already
            $columnInfo = DB::select("SHOW COLUMNS FROM pincodes WHERE Field = 'location'");
            if (!empty($columnInfo) && $columnInfo[0]->Null === 'YES') {
                DB::statement("ALTER TABLE pincodes MODIFY COLUMN location POINT NOT NULL");
            }
        }

        // Step 5: Check if spatial index exists before creating
        $indexExists = DB::select("SHOW INDEXES FROM pincodes WHERE Key_name = 'idx_location_spatial'");
        
        if (empty($indexExists)) {
            // Create spatial index (now that column is NOT NULL)
            DB::statement('ALTER TABLE pincodes ADD SPATIAL INDEX idx_location_spatial (location)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop spatial index first
        DB::statement('ALTER TABLE pincodes DROP INDEX idx_location_spatial');
        
        // Drop POINT column (must use raw SQL)
        DB::statement('ALTER TABLE pincodes DROP COLUMN location');
    }
};

