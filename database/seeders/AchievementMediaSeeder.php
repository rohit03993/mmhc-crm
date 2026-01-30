<?php

namespace Database\Seeders;

use App\Models\AchievementMedia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AchievementMediaSeeder extends Seeder
{
    /**
     * Demo images for the Achievements & Media carousel.
     * Uses placeholder service; replace with real images via admin.
     */
    public function run(): void
    {
        if (AchievementMedia::exists()) {
            return;
        }

        $demos = [
            ['seed' => 'award1', 'caption' => 'Indian Icon of the Year'],
            ['seed' => 'award2', 'caption' => 'India Excellence Award'],
            ['seed' => 'award3', 'caption' => 'International Excellence Award'],
        ];

        foreach ($demos as $index => $demo) {
            $path = $this->fetchAndStoreImage($demo['seed'], $index + 1);
            if ($path) {
                AchievementMedia::create([
                    'image_path' => $path,
                    'caption' => $demo['caption'],
                    'sort_order' => $index,
                ]);
            }
        }
    }

    private function fetchAndStoreImage(string $seed, int $num): ?string
    {
        $url = "https://picsum.photos/seed/{$seed}/800/400";

        try {
            $response = Http::timeout(10)->get($url);
            if (!$response->successful()) {
                return null;
            }
            $contents = $response->body();
            $path = "achievement-media/demo-{$num}.jpg";
            Storage::disk('public')->put($path, $contents);
            return $path;
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }
}
