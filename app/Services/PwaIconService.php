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

        $path = $file->store('site-settings', 'public');
        SiteSetting::set('pwa_icon_path', $path);
        SiteSetting::set('pwa_icon_version', (string) time());

        $absolute = Storage::disk('public')->path($path);
        $this->generateSizedIcons($absolute);

        return $path;
    }

    /**
     * Absolute URL for a PWA icon size.
     * Always use /icons/... static paths (with ?v= cache bust).
     * Never append ?v= onto /media-file?path=... — that breaks the path and shows a blank icon.
     */
    public function iconUrl(int $size = 192): string
    {
        $version = SiteSetting::get('pwa_icon_version');
        $filename = self::SIZES[$size] ?? self::SIZES[192];
        $storageName = 'pwa-icons/' . $filename;
        $publicPath = public_path('icons/' . $filename);

        if (Storage::disk('public')->exists($storageName)) {
            $this->syncStorageIconToPublic($storageName, $filename);
        }

        $url = asset('icons/' . $filename);
        if (! is_file($publicPath) && Storage::disk('public')->exists($storageName)) {
            // public/icons not writable — use media-file with &v= (not ?v=)
            $url = storage_asset($storageName) ?: $url;
        }

        return $this->withCacheBust($url, $version);
    }

    private function withCacheBust(string $url, ?string $version): string
    {
        if ($version === null || $version === '') {
            return $url;
        }

        $sep = str_contains($url, '?') ? '&' : '?';

        return $url . $sep . 'v=' . urlencode($version);
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

        $publicTarget = $publicIcons . DIRECTORY_SEPARATOR . $filename;
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
                $bytes = file_get_contents($sourceAbsolutePath);
            } else {
                $bytes = $resized;
            }

            Storage::disk('public')->put('pwa-icons/' . $filename, $bytes);

            $publicTarget = $publicIcons . DIRECTORY_SEPARATOR . $filename;
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
     * Contain-fit logo onto a brand-blue square (letterbox, no crop).
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

        $canvas = imagecreatetruecolor($size, $size);
        if ($canvas === false) {
            imagedestroy($src);

            return null;
        }

        [$br, $bg, $bb] = self::BRAND_BLUE;
        $blue = imagecolorallocate($canvas, $br, $bg, $bb);
        imagefilledrectangle($canvas, 0, 0, $size, $size, $blue);

        // Keep ~10% padding so maskable installs don't clip the mark
        $pad = (int) round($size * 0.10);
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
}
