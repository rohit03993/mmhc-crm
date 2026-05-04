<?php

namespace Database\Seeders;

use App\Models\AchievementMedia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AchievementMediaSeeder extends Seeder
{
    /**
     * Demo images for the Achievements & Media carousel (login left + landing).
     * Tries picsum first; falls back to inline SVGs so local/VPS work offline (matches VPS when DB is seeded).
     */
    public function run(): void
    {
        if (AchievementMedia::query()->exists()) {
            return;
        }

        $demos = [
            ['seed' => 'award1', 'caption' => 'Indian Icon of the Year'],
            ['seed' => 'award2', 'caption' => 'India Excellence Award'],
            ['seed' => 'award3', 'caption' => 'International Excellence Award'],
            ['seed' => 'media1', 'caption' => 'Media coverage'],
            ['seed' => 'media2', 'caption' => 'Media coverage'],
            ['seed' => 'nurse1', 'caption' => 'Nursing Warrior recognition'],
        ];

        foreach ($demos as $index => $demo) {
            $path = $this->fetchAndStoreImage($demo['seed'], $index + 1)
                ?? $this->storePlaceholderSvg($index + 1, $demo['caption']);
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
            $response = Http::timeout(8)->connectTimeout(5)->get($url);
            if (! $response->successful()) {
                return null;
            }
            $contents = $response->body();
            if ($contents === '' || strlen($contents) < 500) {
                return null;
            }
            $path = "achievement-media/demo-{$num}.jpg";
            Storage::disk('public')->put($path, $contents);

            return $path;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * Offline-safe placeholder so login/VPS-style carousel always has rows after migrate --seed.
     */
    private function storePlaceholderSvg(int $num, string $caption): ?string
    {
        $hues = [210, 265, 190, 330, 25, 160];
        $hue = $hues[($num - 1) % count($hues)];
        $safe = htmlspecialchars(\Illuminate\Support\Str::limit($caption, 42, ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="400" viewBox="0 0 800 400">
  <defs>
    <linearGradient id="g{$num}" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:hsl({$hue},55%,28%)"/>
      <stop offset="100%" style="stop-color:hsl({$hue},45%,18%)"/>
    </linearGradient>
  </defs>
  <rect width="800" height="400" fill="url(#g{$num})"/>
  <rect x="24" y="24" width="752" height="352" rx="16" fill="rgba(255,255,255,0.08)" stroke="rgba(255,255,255,0.2)"/>
  <text x="400" y="188" text-anchor="middle" fill="rgba(255,255,255,0.92)" font-family="system-ui,sans-serif" font-size="22" font-weight="700">MMHC</text>
  <text x="400" y="228" text-anchor="middle" fill="rgba(255,255,255,0.85)" font-family="system-ui,sans-serif" font-size="15">{$safe}</text>
  <text x="400" y="312" text-anchor="middle" fill="rgba(255,255,255,0.45)" font-family="system-ui,sans-serif" font-size="12">Achievement &amp; media • demo {$num}</text>
</svg>
SVG;

        $path = "achievement-media/placeholder-{$num}.svg";
        Storage::disk('public')->put($path, $svg);

        return $path;
    }
}
