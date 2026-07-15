<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PwaIconService
{
    public const SIZES = [
        192 => 'icon-192.png',
        512 => 'icon-512.png',
        180 => 'apple-touch-icon.png',
    ];

    public const BRAND_BLUE = [0x2E, 0x48, 0xA2];

    /**
     * Store an admin-uploaded PWA icon, generate sizes, and sync public/icons for PWA install.
     */
    public function storeUploadedIcon(UploadedFile $file): string
    {
        $oldPath = SiteSetting::get('pwa_icon_path');
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $path = $file->storeAs(
            'site-settings',
            'pwa-icon-source.'.$file->getClientOriginalExtension(),
            'public'
        );
        SiteSetting::set('pwa_icon_path', $path);
        SiteSetting::set('pwa_icon_version', (string) time());

        $absolute = Storage::disk('public')->path($path);
        $this->generateSizedIcons($absolute);

        return $path;
    }

    /**
     * Absolute URL for a PWA icon size.
     * Prefer synced public/icons (with ?v=) — Hostinger serves those reliably.
     * Fallback: /pwa-icon/{size} (no .png) so nginx does not 404 before Laravel.
     */
    public function iconUrl(int $size = 192): string
    {
        $version = SiteSetting::get('pwa_icon_version');
        $filename = self::SIZES[$size] ?? self::SIZES[192];
        $storageName = 'pwa-icons/'.$filename;
        $publicPath = public_path('icons/'.$filename);

        if (Storage::disk('public')->exists($storageName)) {
            $this->syncStorageIconToPublic($storageName, $filename);
        }

        if (is_file($publicPath)) {
            return $this->withCacheBust(asset('icons/'.$filename), $version);
        }

        $px = array_key_exists($size, self::SIZES) ? $size : 192;

        return $this->withCacheBust(url('/pwa-icon/'.$px), $version);
    }

    private function withCacheBust(string $url, ?string $version): string
    {
        if ($version === null || $version === '') {
            return $url;
        }

        $sep = str_contains($url, '?') ? '&' : '?';

        return $url.$sep.'v='.urlencode($version);
    }

    private function syncStorageIconToPublic(string $storageName, string $filename): void
    {
        $publicIcons = public_path('icons');
        if (! File::isDirectory($publicIcons)) {
            File::makeDirectory($publicIcons, 0755, true);
        }

        $bytes = Storage::disk('public')->get($storageName);
        if ($bytes === null || $bytes === '') {
            return;
        }

        $publicTarget = $publicIcons.DIRECTORY_SEPARATOR.$filename;
        $written = @file_put_contents($publicTarget, $bytes);
        if ($written === false) {
            Log::warning('PWA icon could not sync to public/icons', ['path' => $publicTarget]);

            return;
        }

        if ($filename === 'apple-touch-icon.png') {
            @file_put_contents(public_path('apple-touch-icon.png'), $bytes);
        }
    }

    public function generateSizedIcons(string $sourceAbsolutePath): void
    {
        if (! is_file($sourceAbsolutePath)) {
            return;
        }

        Storage::disk('public')->makeDirectory('pwa-icons');
        $publicIcons = public_path('icons');
        if (! File::isDirectory($publicIcons)) {
            File::makeDirectory($publicIcons, 0755, true);
        }

        foreach (self::SIZES as $px => $filename) {
            $resized = $this->resizeToSquarePng($sourceAbsolutePath, $px);
            if ($resized === null) {
                Log::warning('PWA icon resize failed; copying source bytes', ['source' => $sourceAbsolutePath]);
                $bytes = file_get_contents($sourceAbsolutePath);
            } else {
                $bytes = $resized;
            }

            Storage::disk('public')->put('pwa-icons/'.$filename, $bytes);

            $publicTarget = $publicIcons.DIRECTORY_SEPARATOR.$filename;
            $written = @file_put_contents($publicTarget, $bytes);
            if ($written === false) {
                Log::warning('PWA icon could not write public/icons file', ['path' => $publicTarget]);
            }

            if ($filename === 'apple-touch-icon.png') {
                @file_put_contents(public_path('apple-touch-icon.png'), $bytes);
            }
        }
    }

    /**
     * Fit the uploaded image into a square as faithfully as possible.
     * Uses corner-sampled letterbox colour (so a blue MeD logo stays blue).
     */
    private function resizeToSquarePng(string $sourcePath, int $size): ?string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return null;
        }

        $info = @getimagesize($sourcePath);
        if ($info === false) {
            return null;
        }

        [$width, $height] = $info;
        $mime = $info['mime'] ?? '';

        $src = match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($sourcePath),
            'image/png' => @imagecreatefrompng($sourcePath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false,
            'image/gif' => @imagecreatefromgif($sourcePath),
            default => false,
        };

        if ($src === false) {
            return null;
        }

        if (function_exists('imagepalettetotruecolor')) {
            @imagepalettetotruecolor($src);
        }
        imagealphablending($src, true);
        imagesavealpha($src, true);

        $canvas = imagecreatetruecolor($size, $size);
        if ($canvas === false) {
            imagedestroy($src);

            return null;
        }

        [$fr, $fg, $fb] = $this->sampleFillColor($src, $width, $height);
        $fill = imagecolorallocate($canvas, $fr, $fg, $fb);
        imagefilledrectangle($canvas, 0, 0, $size, $size, $fill);

        // Small safe inset only (maskable safe zone still covered at ~8%)
        $pad = (int) round($size * 0.06);
        $box = max(1, $size - ($pad * 2));
        $scale = min($box / max($width, 1), $box / max($height, 1));
        $newW = max(1, (int) round($width * $scale));
        $newH = max(1, (int) round($height * $scale));
        $dstX = (int) round(($size - $newW) / 2);
        $dstY = (int) round(($size - $newH) / 2);

        imagealphablending($canvas, true);
        imagecopyresampled($canvas, $src, $dstX, $dstY, 0, 0, $newW, $newH, $width, $height);

        ob_start();
        imagepng($canvas, null, 6);
        $png = ob_get_clean();

        imagedestroy($src);
        imagedestroy($canvas);

        return $png === false ? null : $png;
    }

    /**
     * @return array{0:int,1:int,2:int}
     */
    private function sampleFillColor($src, int $width, int $height): array
    {
        $points = [
            [2, 2],
            [max(0, $width - 3), 2],
            [2, max(0, $height - 3)],
            [max(0, $width - 3), max(0, $height - 3)],
        ];

        $r = $g = $b = $n = 0;
        foreach ($points as [$x, $y]) {
            $rgba = @imagecolorat($src, $x, $y);
            if ($rgba === false) {
                continue;
            }
            $a = ($rgba & 0x7F000000) >> 24;
            // Skip largely transparent samples
            if ($a > 64) {
                continue;
            }
            $r += ($rgba >> 16) & 0xFF;
            $g += ($rgba >> 8) & 0xFF;
            $b += $rgba & 0xFF;
            $n++;
        }

        if ($n === 0) {
            return self::BRAND_BLUE;
        }

        return [(int) round($r / $n), (int) round($g / $n), (int) round($b / $n)];
    }
}
