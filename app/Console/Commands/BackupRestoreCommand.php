<?php

namespace App\Console\Commands;

use App\Services\SiteBackupService;
use Illuminate\Console\Command;

class BackupRestoreCommand extends Command
{
    protected $signature = 'backup:restore
                            {zip : Absolute path to mmhc-backup-*.zip}
                            {--force : Skip confirmation (dangerous)}';

    protected $description = 'Restore database and storage from a backup zip created by this app (overwrites current data)';

    public function handle(SiteBackupService $backupService): int
    {
        $zip = $this->argument('zip');
        if (! is_readable($zip)) {
            $this->error('File not readable: '.$zip);

            return self::FAILURE;
        }

        $this->warn('This will REPLACE the current database and merge backup files into storage/app/private and storage/app/public.');
        $this->warn('Put the application in maintenance mode first on production.');

        if (! $this->option('force')) {
            if (! $this->confirm('Continue with restore?', false)) {
                return self::SUCCESS;
            }
        }

        try {
            $backupService->restoreBackup($zip);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Restore finished.');

        return self::SUCCESS;
    }
}
