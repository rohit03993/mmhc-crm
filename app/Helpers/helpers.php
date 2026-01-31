<?php

if (! function_exists('storage_url')) {
    /**
     * URL for a file via Laravel route (/storage?path=...).
     * Use when symlink cannot be used (e.g. VPS disable_symlinks).
     * Returns null for empty/invalid path.
     */
    function storage_url(?string $path): ?string
    {
        $path = $path ? trim($path, '/') : '';
        if ($path === '' || $path === '0' || str_contains($path, '..')) {
            return null;
        }
        return url('/storage?path=' . rawurlencode($path));
    }
}

if (! function_exists('storage_asset')) {
    /**
     * URL for a file on the public storage disk.
     * Default: uses storage link (asset('storage/...')) when php artisan storage:link is set.
     * When SERVE_STORAGE_VIA_LARAVEL=true: uses Laravel route (/storage?path=...) for VPS where symlink cannot be used.
     * Returns null for empty/invalid path so views can show a placeholder.
     */
    function storage_asset(?string $path): ?string
    {
        $path = $path ? trim($path, '/') : '';
        if ($path === '' || $path === '0' || str_contains($path, '..')) {
            return null;
        }
        if (config('filesystems.serve_via_laravel', false)) {
            return url('/storage?path=' . rawurlencode($path));
        }
        return asset('storage/' . $path);
    }
}
