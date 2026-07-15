<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Services\PwaIconService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves the admin-uploaded PWA launcher icons.
 * Avoids stale CDN/browser copies of public/icons/*.png.
 */
class PwaIconController extends Controller
{
    public function __invoke(string $size): Response
    {
        $px = (int) $size;
        $map = PwaIconService::SIZES;
        if (! isset($map[$px])) {
            abort(404);
        }

        $filename = $map[$px];
        $storageName = 'pwa-icons/'.$filename;
        $version = (string) (SiteSetting::get('pwa_icon_version') ?: '0');

        if (Storage::disk('public')->exists($storageName)) {
            $absolute = Storage::disk('public')->path($storageName);

            return $this->pngResponse($absolute, $version);
        }

        $publicPath = public_path('icons/'.$filename);
        if (is_file($publicPath)) {
            return $this->pngResponse($publicPath, $version);
        }

        abort(404);
    }

    private function pngResponse(string $absolutePath, string $version): BinaryFileResponse
    {
        return response()->file($absolutePath, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=300, must-revalidate',
            'ETag' => '"pwa-icon-'.$version.'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
