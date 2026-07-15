<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PwaIconService
{
    public const SIZES = [
        192 => 'icon-192.png',
        512 => 'icon-512.png',
        180 => 'apple-touch-icon.png',
    ];

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
     * Absolute URL for the primary PWA icon (192), with cache-bust when available.
     * Prefers public/icons (synced on upload) so the service worker and install flow keep working.
     */
    public function iconUrl(int $size = 192): string
    {
        $version = SiteSetting::get('pwa_icon_version');
        $query = $version ? '?v=' . urlencode((string) $version) : '';
        $filename = self::SIZES[$size] ?? self::SIZES[192];

        $publicPath = public_path('icons/' . $filename);
        if (is_file($publicPath)) {
            return asset('icons/' . $filename) . $query;
        }

        $storageName = 'pwa-icons/' . $filename;
        if (Storage::disk('public')->exists($storageName)) {
            return (storage_asset($storageName) ?: asset('icons/' . $filename)) . $query;
        }

        return asset('icons/' . $filename) . $query;
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
                // Fallback: copy original bytes (still works for PWA, just not ideally sized)
                $bytes = file_get_contents($sourceAbsolutePath);
            } else {
                $bytes = $resized;
            }

            Storage::disk('public')->put('pwa-icons/' . $filename, $bytes);

            $publicTarget = $publicIcons . DIRECTORY_SEPARATOR . $filename;
            @file_put_contents($publicTarget, $bytes);

            if ($filename === 'apple-touch-icon.png') {
                @file_put_contents(public_path('apple-touch-icon.png'), $bytes);
            }
        }
    }

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

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $size, $size, $transparent);
        imagealphablending($canvas, true);

        // Cover-fit into square (center crop)
        $scale = max($size / max($width, 1), $size / max($height, 1));
        $newW = (int) round($width * $scale);
        $newH = (int) round($height * $scale);
        $dstX = (int) round(($size - $newW) / 2);
        $dstY = (int) round(($size - $newH) / 2);

        imagecopyresampled($canvas, $src, $dstX, $dstY, 0, 0, $newW, $newH, $width, $height);

        ob_start();
        imagesavealpha($canvas, true);
        imagepng($canvas, null, 6);
        $png = ob_get_clean();

        imagedestroy($src);
        imagedestroy($canvas);

        return $png === false ? null : $png;
    }
}
