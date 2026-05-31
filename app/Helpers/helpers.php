<?php

if (! function_exists('storage_url')) {
    /**
     * URL for a file via Laravel route (/media-file?path=...).
     * Use when symlink cannot be used (e.g. VPS disable_symlinks).
     * Returns null for empty/invalid path.
     */
    function storage_url(?string $path): ?string
    {
        if ($path && filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $path = $path ? trim($path, '/') : '';
        if ($path === '' || $path === '0' || str_contains($path, '..')) {
            return null;
        }
        return '/media-file?path=' . rawurlencode($path);
    }
}

if (! function_exists('storage_asset')) {
    /**
     * URL for a file on the public storage disk.
     * Default: uses storage link (asset('storage/...')) when php artisan storage:link is set.
     * When SERVE_STORAGE_VIA_LARAVEL=true: uses Laravel route (/media-file?path=...) for VPS where symlink cannot be used.
     * Returns null for empty/invalid path so views can show a placeholder.
     */
    function storage_asset(?string $path): ?string
    {
        if ($path && filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $path = $path ? trim($path, '/') : '';
        if ($path === '' || $path === '0' || str_contains($path, '..')) {
            return null;
        }
        if (config('filesystems.serve_via_laravel', false)) {
            return '/media-file?path=' . rawurlencode($path);
        }
        return '/storage/' . $path;
    }
}

if (! function_exists('mmhc_app_logo_url')) {
    /**
     * Fixed horizontal company logo for the authenticated CRM shell (nav + sidebar).
     * Uses med-logo-app.png (248×76) — not the square med-logo.png Android icon asset.
     */
    function mmhc_app_logo_url(): string
    {
        $path = public_path('images/med-logo-app.png');
        if (! is_file($path)) {
            return asset('images/med-logo.png');
        }

        return asset('images/med-logo-app.png');
    }
}
