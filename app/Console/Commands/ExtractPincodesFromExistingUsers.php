<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Core\User;
use App\Modules\Auth\Services\LocationService;

class ExtractPincodesFromExistingUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:extract-pincodes {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract pincode and coordinates from existing user addresses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }
        
        // Get all users without pincode but with address
        $users = User::whereNull('pincode')
            ->whereNotNull('address')
            ->where('address', '!=', '')
            ->get();
        
        if ($users->isEmpty()) {
            $this->info('✅ All users already have pincodes extracted or no addresses found.');
            return 0;
        }
        
        $this->info("📊 Found {$users->count()} users without pincode data");
        $this->newLine();
        
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();
        
        $stats = [
            'processed' => 0,
            'updated' => 0,
            'not_found' => 0,
            'no_address' => 0,
        ];
        
        foreach ($users as $user) {
            $stats['processed']++;
            
            // Extract pincode and coordinates
            $locationData = LocationService::extractLocationData($user->address);
            
            if ($locationData && $locationData['pincode']) {
                if (!$dryRun) {
                    $user->update([
                        'pincode' => $locationData['pincode'],
                        'latitude' => $locationData['latitude'],
                        'longitude' => $locationData['longitude'],
                    ]);
                }
                $stats['updated']++;
            } else {
                $stats['not_found']++;
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Display results
        $this->info('📈 Extraction Results:');
        $this->table(
            ['Status', 'Count'],
            [
                ['✅ Updated with pincode', $stats['updated']],
                ['❌ Pincode not found in address', $stats['not_found']],
                ['📊 Total Processed', $stats['processed']],
            ]
        );
        
        if ($dryRun) {
            $this->warn('💡 This was a dry run. Run without --dry-run to apply changes.');
        } else {
            $this->info('✅ Pincode extraction completed!');
            $this->info('💡 Users can now see nearby staff sorted by distance.');
        }
        
        return 0;
    }
}

