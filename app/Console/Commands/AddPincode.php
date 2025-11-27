<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pincode;

class AddPincode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pincode:add 
                            {pincode : The 6-digit pincode}
                            {--lat= : Latitude}
                            {--lng= : Longitude}
                            {--city= : City name}
                            {--state= : State name}
                            {--district= : District name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a single pincode with coordinates manually';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $pincode = $this->argument('pincode');
        
        // Validate pincode format
        if (!preg_match('/^\d{6}$/', $pincode)) {
            $this->error('❌ Invalid pincode format. Must be 6 digits (e.g., 800001)');
            return 1;
        }
        
        // Check if already exists
        if (Pincode::where('pincode', $pincode)->exists()) {
            $this->warn("⚠️  Pincode {$pincode} already exists. Updating...");
        }
        
        $lat = $this->option('lat');
        $lng = $this->option('lng');
        
        if (!$lat || !$lng) {
            $this->error('❌ Latitude and Longitude are required!');
            $this->info('💡 Usage: php artisan pincode:add 800001 --lat=25.5941 --lng=85.1376 --city="Patna" --state="Bihar"');
            return 1;
        }
        
        Pincode::updateOrCreate(
            ['pincode' => $pincode],
            [
                'latitude' => (float) $lat,
                'longitude' => (float) $lng,
                'city' => $this->option('city'),
                'state' => $this->option('state'),
                'district' => $this->option('district'),
            ]
        );
        
        $this->info("✅ Pincode {$pincode} added/updated successfully!");
        $this->info("📍 Coordinates: {$lat}, {$lng}");
        
        return 0;
    }
}

