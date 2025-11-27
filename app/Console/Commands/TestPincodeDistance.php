<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pincode;

class TestPincodeDistance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pincode:test 
                            {pincode1 : First pincode}
                            {pincode2 : Second pincode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test distance calculation between two pincodes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $pincode1 = $this->argument('pincode1');
        $pincode2 = $this->argument('pincode2');
        
        $this->info("📍 Testing distance calculation...");
        $this->newLine();
        
        // Get coordinates for both pincodes
        $coord1 = Pincode::getCoordinates($pincode1);
        $coord2 = Pincode::getCoordinates($pincode2);
        
        if (!$coord1) {
            $this->error("❌ Pincode {$pincode1} not found in database!");
            $this->info("💡 Add it using: php artisan pincode:add {$pincode1} --lat=XX.XXXX --lng=XX.XXXX");
            return 1;
        }
        
        if (!$coord2) {
            $this->error("❌ Pincode {$pincode2} not found in database!");
            $this->info("💡 Add it using: php artisan pincode:add {$pincode2} --lat=XX.XXXX --lng=XX.XXXX");
            return 1;
        }
        
        // Calculate distance
        $distance = Pincode::calculateDistance($pincode1, $pincode2);
        
        // Display results
        $this->info("📍 Pincode 1: {$pincode1}");
        $this->info("   Location: {$coord1['city']}, {$coord1['state']}");
        $this->info("   Coordinates: {$coord1['latitude']}, {$coord1['longitude']}");
        $this->newLine();
        
        $this->info("📍 Pincode 2: {$pincode2}");
        $this->info("   Location: {$coord2['city']}, {$coord2['state']}");
        $this->info("   Coordinates: {$coord2['latitude']}, {$coord2['longitude']}");
        $this->newLine();
        
        $this->info("📏 Distance: {$distance} km");
        $this->newLine();
        
        // Show nearby pincodes
        $this->info("🔍 Finding pincodes within 50km of {$pincode1}...");
        $nearby = Pincode::findNearby($pincode1, 50);
        
        if (count($nearby) > 0) {
            $this->table(
                ['Pincode', 'City', 'State', 'Distance (km)'],
                array_map(function($item) {
                    return [
                        $item['pincode'],
                        $item['city'] ?? 'N/A',
                        $item['state'] ?? 'N/A',
                        number_format($item['distance'], 2)
                    ];
                }, array_slice($nearby, 0, 10))
            );
        } else {
            $this->warn("   No pincodes found within 50km");
        }
        
        return 0;
    }
}

