<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BackupPruneCommand extends Command
{
    protected $signature = 'backup:prune {--dry-run : List files that would be deleted}';

    protected $description = 'Delete site backup zips older than backup.keep_days';

    public function handle(): int
    {
        $days = max(1, (int) config('backup.keep_days', 14));
        $dir = storage_path('app/'.config('backup.relative_path', 'site-backups'));
        if (! File::isDirectory($dir)) {
            $this->info('No backup directory.');

            return self::SUCCESS;
        }

        $cutoff = now()->subDays($days)->getTimestamp();
        $files = File::glob($dir.DIRECTORY_SEPARATOR.'mmhc-backup-*.zip') ?: [];

        $removed = 0;
        foreach ($files as $path) {
            if (filemtime($path) >= $cutoff) {
                continue;
            }
            if ($this->option('dry-run')) {
                $this->line('[dry-run] would delete '.basename($path));
                $removed++;
            } else {
                File::delete($path);
                $removed++;
            }
        }

        $this->info($this->option('dry-run')
            ? "Would remove {$removed} file(s) older than {$days} days."
            : "Removed {$removed} backup(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
