<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Services\PwaIconService;
use Illuminate\Http\JsonResponse;

class PwaManifestController extends Controller
{
    public function __invoke(PwaIconService $icons): JsonResponse
    {
        $name = SiteSetting::get('company_name') ?: 'MeD Miracle Health Care';
        $shortName = 'MeD Miracle';
        $icon192 = $icons->iconUrl(192);
        $icon512 = $icons->iconUrl(512);
        $version = SiteSetting::get('pwa_icon_version');

        $payload = [
            'id' => '/',
            'name' => $name,
            'short_name' => $shortName,
            'description' => 'Healthcare CRM for patients, nurses, caregivers, and admins — book care, manage jobs, and stay connected.',
            'start_url' => '/?source=pwa',
            'scope' => '/',
            'display' => 'standalone',
            'display_override' => ['standalone', 'minimal-ui', 'browser'],
            'orientation' => 'portrait-primary',
            'background_color' => '#2E48A2',
            'theme_color' => '#2E48A2',
            'lang' => 'en',
            'dir' => 'ltr',
            'categories' => ['health', 'medical', 'lifestyle'],
            'icons' => [
                [
                    'src' => $icon192,
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => $icon512,
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => $icon192,
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
                [
                    'src' => $icon512,
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
                [
                    'src' => url('/icons/icon.svg'),
                    'sizes' => 'any',
                    'type' => 'image/svg+xml',
                    'purpose' => 'any',
                ],
            ],
            'shortcuts' => [
                [
                    'name' => 'Dashboard',
                    'short_name' => 'Home',
                    'description' => 'Open your MeD Miracle dashboard',
                    'url' => '/dashboard?source=pwa',
                    'icons' => [['src' => $icon192, 'sizes' => '192x192']],
                ],
                [
                    'name' => 'Login',
                    'short_name' => 'Login',
                    'description' => 'Sign in to MeD Miracle',
                    'url' => '/login?source=pwa',
                    'icons' => [['src' => $icon192, 'sizes' => '192x192']],
                ],
            ],
        ];

        return response()
            ->json($payload)
            ->header('Content-Type', 'application/manifest+json')
            ->header('Cache-Control', 'no-cache, must-revalidate');
    }
}
