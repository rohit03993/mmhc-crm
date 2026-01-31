<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves files from storage/app/public (public disk).
 * Used when the web server cannot follow the public/storage symlink (e.g. disable_symlinks).
 * Only serves files that exist under the public disk; no directory traversal.
 */
class StorageController extends Controller
{
    public function show(Request $request, string $path): StreamedResponse
    {
        $path = $this->sanitizePath($path);
        if ($path === null || ! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path);
    }

    /**
     * Sanitize path: no directory traversal, no null bytes, no leading/trailing slashes.
     */
    private function sanitizePath(string $path): ?string
    {
        $path = str_replace("\0", '', $path);
        $path = trim($path, '/');
        if ($path === '' || str_contains($path, '..')) {
            return null;
        }
        return $path;
    }
}
