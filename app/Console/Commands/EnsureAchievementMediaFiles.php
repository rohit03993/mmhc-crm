<?php

namespace App\Console\Commands;

use App\Models\AchievementMedia;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Creates missing image files for achievement_media records.
 * Run locally or after deploy when DB has paths but files are missing from storage/app/public.
 */
class EnsureAchievementMediaFiles extends Command
{
    protected $signature = 'achievement-media:ensure-files';

    protected $description = 'Create missing image files for achievement_media records (fetch from Picsum or placeholder)';

    private const PICSUM_SEEDS = [
        'demo-1.jpg' => 'award1',
        'demo-2.jpg' => 'award2',
        'demo-3.jpg' => 'award3',
    ];

    public function handle(): int
    {
        $items = AchievementMedia::ordered()->get();
        if ($items->isEmpty()) {
            $this->info('No achievement_media records.');
            return self::SUCCESS;
        }

        $created = 0;
        foreach ($items as $item) {
            $path = $item->image_path;
            if (! $path || ! is_string($path)) {
                continue;
            }
            $path = trim($path, '/');
            if (Storage::disk('public')->exists($path)) {
                continue;
            }
            $dir = dirname($path);
            if ($dir !== '.') {
                Storage::disk('public')->makeDirectory($dir);
            }
            if ($this->fetchAndPut($path)) {
                $created++;
                $this->line("Created: {$path}");
            } else {
                $this->warn("Skipped (could not create): {$path}");
            }
        }

        $this->info($created > 0 ? "Created {$created} missing file(s)." : 'No missing files.');
        return self::SUCCESS;
    }

    private function fetchAndPut(string $path): bool
    {
        $basename = basename($path);
        $seed = self::PICSUM_SEEDS[$basename] ?? 'achievement' . md5($path);
        $url = 'https://picsum.photos/seed/' . $seed . '/800/400';
        try {
            $response = Http::timeout(15)->get($url);
            if ($response->successful()) {
                Storage::disk('public')->put($path, $response->body());
                return true;
            }
        } catch (\Throwable $e) {
            // fallback to placeholder
        }
        return $this->putPlaceholderJpeg($path);
    }

    private function putPlaceholderJpeg(string $path): bool
    {
        try {
            $response = Http::timeout(5)->get('https://picsum.photos/400/300');
            if ($response->successful()) {
                Storage::disk('public')->put($path, $response->body());
                return true;
            }
        } catch (\Throwable $e) {
            //
        }
        return false;
    }
}
