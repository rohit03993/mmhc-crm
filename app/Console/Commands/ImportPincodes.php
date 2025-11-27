<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pincode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ImportPincodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pincodes:import 
                            {--source=github : Data source (github, npm, file)}
                            {--file= : Path to CSV/JSON file if using file source}
                            {--limit= : Limit number of pincodes to import (for testing)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Indian pincodes with latitude/longitude coordinates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $source = $this->option('source');
        $limit = $this->option('limit');
        
        $this->info('🚀 Starting pincode import...');
        $this->newLine();
        
        try {
            switch ($source) {
                case 'github':
                    $this->importFromGitHub($limit);
                    break;
                case 'npm':
                    $this->importFromNPM($limit);
                    break;
                case 'file':
                    $file = $this->option('file');
                    if (!$file) {
                        $this->error('Please provide --file option when using file source');
                        return 1;
                    }
                    $this->importFromFile($file, $limit);
                    break;
                default:
                    $this->error("Unknown source: {$source}");
                    return 1;
            }
            
            $this->newLine();
            $this->info('✅ Pincode import completed successfully!');
            
            $total = Pincode::count();
            $this->info("📊 Total pincodes in database: {$total}");
            
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Import from GitHub repository
     */
    protected function importFromGitHub($limit = null)
    {
        $this->info('📥 Fetching data from GitHub...');
        
        // GitHub raw URL for IndiaPIN dataset
        // Note: This is a placeholder - actual URL needs to be determined
        $url = 'https://raw.githubusercontent.com/harshvardhaniimi/IndiaPIN/master/data/pincodes.csv';
        
        $this->warn('⚠️  GitHub import requires manual data download.');
        $this->warn('Please download the CSV file from: https://github.com/harshvardhaniimi/IndiaPIN');
        $this->warn('Then use: php artisan pincodes:import --source=file --file=path/to/pincodes.csv');
    }

    /**
     * Import from NPM package data
     */
    protected function importFromNPM($limit = null)
    {
        $this->info('📥 Fetching data from NPM package...');
        
        // NPM package data URL
        $url = 'https://raw.githubusercontent.com/npm/pincode-lat-long/master/pincodes.json';
        
        try {
            $response = Http::timeout(60)->get($url);
            
            if (!$response->successful()) {
                throw new \Exception('Failed to fetch data from NPM package');
            }
            
            $data = $response->json();
            
            if (!is_array($data)) {
                throw new \Exception('Invalid data format received');
            }
            
            $this->info('✅ Data fetched successfully. Processing...');
            
            $bar = $this->output->createProgressBar(count($data));
            $bar->start();
            
            $imported = 0;
            $skipped = 0;
            
            DB::beginTransaction();
            
            foreach ($data as $pincode => $info) {
                if ($limit && $imported >= $limit) {
                    break;
                }
                
                // Skip if already exists
                if (Pincode::where('pincode', $pincode)->exists()) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }
                
                // Create pincode record
                Pincode::create([
                    'pincode' => (string) $pincode,
                    'latitude' => $info['lat'] ?? null,
                    'longitude' => $info['lng'] ?? null,
                    'city' => $info['city'] ?? null,
                    'state' => $info['state'] ?? null,
                ]);
                
                $imported++;
                $bar->advance();
            }
            
            DB::commit();
            $bar->finish();
            
            $this->newLine(2);
            $this->info("✅ Imported: {$imported} pincodes");
            if ($skipped > 0) {
                $this->info("⏭️  Skipped (already exists): {$skipped} pincodes");
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Import from local CSV/JSON file
     */
    protected function importFromFile($filePath, $limit = null)
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }
        
        $this->info("📂 Reading file: {$filePath}");
        
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        if ($extension === 'csv') {
            $this->importFromCSV($filePath, $limit);
        } elseif ($extension === 'json') {
            $this->importFromJSON($filePath, $limit);
        } else {
            throw new \Exception("Unsupported file format: {$extension}. Use CSV or JSON.");
        }
    }

    /**
     * Import from CSV file
     */
    protected function importFromCSV($filePath, $limit = null)
    {
        $handle = fopen($filePath, 'r');
        
        if (!$handle) {
            throw new \Exception("Could not open file: {$filePath}");
        }
        
        // Read header row
        $headers = fgetcsv($handle);
        
        if (!$headers) {
            throw new \Exception("Empty or invalid CSV file");
        }
        
        $this->info('✅ CSV file opened. Processing...');
        
        $bar = $this->output->createProgressBar();
        $bar->start();
        
        $imported = 0;
        $skipped = 0;
        
        DB::beginTransaction();
        
        while (($row = fgetcsv($handle)) !== false) {
            if ($limit && $imported >= $limit) {
                break;
            }
            
            // Map CSV columns to database fields
            // Adjust column mapping based on your CSV structure
            $pincode = $row[0] ?? null;
            $latitude = isset($row[1]) ? (float) $row[1] : null;
            $longitude = isset($row[2]) ? (float) $row[2] : null;
            
            if (!$pincode || !$latitude || !$longitude) {
                $skipped++;
                $bar->advance();
                continue;
            }
            
            // Skip if already exists
            if (Pincode::where('pincode', $pincode)->exists()) {
                $skipped++;
                $bar->advance();
                continue;
            }
            
            Pincode::create([
                'pincode' => (string) $pincode,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'city' => $row[3] ?? null,
                'state' => $row[4] ?? null,
                'district' => $row[5] ?? null,
            ]);
            
            $imported++;
            $bar->advance();
        }
        
        fclose($handle);
        DB::commit();
        $bar->finish();
        
        $this->newLine(2);
        $this->info("✅ Imported: {$imported} pincodes");
        if ($skipped > 0) {
            $this->info("⏭️  Skipped: {$skipped} pincodes");
        }
    }

    /**
     * Import from JSON file
     */
    protected function importFromJSON($filePath, $limit = null)
    {
        $content = file_get_contents($filePath);
        $data = json_decode($content, true);
        
        if (!$data) {
            throw new \Exception("Invalid JSON file or empty data");
        }
        
        $this->info('✅ JSON file loaded. Processing...');
        
        $bar = $this->output->createProgressBar(count($data));
        $bar->start();
        
        $imported = 0;
        $skipped = 0;
        
        DB::beginTransaction();
        
        foreach ($data as $key => $value) {
            if ($limit && $imported >= $limit) {
                break;
            }
            
            // Handle different JSON structures
            if (is_array($value) && isset($value['pincode'])) {
                // Structure: [{"pincode": "110001", "lat": 28.61, ...}, ...]
                $pincode = $value['pincode'];
                $latitude = $value['lat'] ?? $value['latitude'] ?? null;
                $longitude = $value['lng'] ?? $value['longitude'] ?? null;
            } elseif (is_array($value) && isset($value['lat'])) {
                // Structure: {"110001": {"lat": 28.61, "lng": 77.20, ...}, ...}
                $pincode = $key;
                $latitude = $value['lat'] ?? null;
                $longitude = $value['lng'] ?? null;
            } else {
                $skipped++;
                $bar->advance();
                continue;
            }
            
            if (!$pincode || !$latitude || !$longitude) {
                $skipped++;
                $bar->advance();
                continue;
            }
            
            // Skip if already exists
            if (Pincode::where('pincode', $pincode)->exists()) {
                $skipped++;
                $bar->advance();
                continue;
            }
            
            Pincode::create([
                'pincode' => (string) $pincode,
                'latitude' => (float) $latitude,
                'longitude' => (float) $longitude,
                'city' => $value['city'] ?? null,
                'state' => $value['state'] ?? null,
            ]);
            
            $imported++;
            $bar->advance();
        }
        
        DB::commit();
        $bar->finish();
        
        $this->newLine(2);
        $this->info("✅ Imported: {$imported} pincodes");
        if ($skipped > 0) {
            $this->info("⏭️  Skipped: {$skipped} pincodes");
        }
    }
}

