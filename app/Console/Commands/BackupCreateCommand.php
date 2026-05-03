<?php

namespace App\Console\Commands;

use App\Services\SiteBackupService;
use Illuminate\Console\Command;

class BackupCreateCommand extends Command
{
    protected $signature = 'backup:create';

    protected $description = 'Create a full site backup (database + storage/app/private + storage/app/public) as a zip under storage/app/site-backups';

    public function handle(SiteBackupService $backupService): int
    {
        $this->info('Creating backup…');

        try {
            $result = $backupService->createBackup();
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $path = $result['path'];
        $sizeMb = round($result['size'] / 1048576, 2);
        $this->info("Done: {$result['filename']} ({$sizeMb} MiB)");
        $this->line($path);

        return self::SUCCESS;
    }
}
