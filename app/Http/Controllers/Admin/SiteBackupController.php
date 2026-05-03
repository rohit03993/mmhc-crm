<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SiteBackupService;

class SiteBackupController extends Controller
{
    public function index(SiteBackupService $backupService)
    {
        $backups = $backupService->listBackups();

        return view('admin.site-backups.index', compact('backups'));
    }

    public function store(SiteBackupService $backupService)
    {
        set_time_limit(0);

        try {
            $result = $backupService->createBackup();
        } catch (\Throwable $e) {
            return redirect()->route('admin.backups.index')
                ->with('error', $e->getMessage());
        }

        $mb = round($result['size'] / 1048576, 2);

        return redirect()->route('admin.backups.index')
            ->with('success', "Backup created: {$result['filename']} ({$mb} MiB).");
    }

    public function download(SiteBackupService $backupService, string $filename)
    {
        try {
            $path = $backupService->validateBackupFilename($filename);
        } catch (\Throwable $e) {
            abort(404);
        }

        return response()->download($path, $filename);
    }

    public function destroy(SiteBackupService $backupService, string $filename)
    {
        try {
            $backupService->deleteBackup($filename);
        } catch (\Throwable $e) {
            return redirect()->route('admin.backups.index')
                ->with('error', $e->getMessage());
        }

        return redirect()->route('admin.backups.index')
            ->with('success', 'Backup removed.');
    }
}
