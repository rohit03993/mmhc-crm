<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use ZipArchive;

class SiteBackupService
{
    public const MANIFEST_VERSION = '1';

    public function backupDirectory(): string
    {
        $dir = storage_path('app/'.config('backup.relative_path', 'site-backups'));
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        return $dir;
    }

    /**
     * @return array{filename: string, path: string, size: int, manifest: array<string, mixed>}
     */
    public function createBackup(): array
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        $stamp = now()->format('Y-m-d-His');
        $token = bin2hex(random_bytes(4));
        $filename = "mmhc-backup-{$stamp}-{$token}.zip";
        $fullPath = $this->backupDirectory().DIRECTORY_SEPARATOR.$filename;

        $manifest = $this->buildManifestBase($connection, $driver);

        $zip = new ZipArchive;
        if ($zip->open($fullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Could not create backup archive.');
        }

        $sqlDumpTempPath = null;

        try {
            if ($driver === 'mysql') {
                $sqlDumpTempPath = $this->dumpMysqlToTempFile($connection);
                $zip->addFile($sqlDumpTempPath, 'database.sql');
                $manifest['database_dump'] = 'database.sql';
            } elseif ($driver === 'sqlite') {
                $sqlitePath = database_path(config("database.connections.{$connection}.database"));
                if (! is_readable($sqlitePath)) {
                    throw new RuntimeException('SQLite database file is not readable: '.$sqlitePath);
                }
                $zip->addFile($sqlitePath, 'database.sqlite');
                $manifest['database_dump'] = 'database.sqlite';
            } else {
                throw new RuntimeException("Backup is only implemented for mysql and sqlite (current driver: {$driver}).");
            }

            $this->addStorageTreesToZip($zip);

            $manifest['storage_paths'] = ['storage/private', 'storage/public'];

            $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } finally {
            $zip->close();
            if ($sqlDumpTempPath !== null && is_file($sqlDumpTempPath)) {
                @unlink($sqlDumpTempPath);
            }
        }

        $size = filesize($fullPath);

        return [
            'filename' => $filename,
            'path' => $fullPath,
            'size' => $size ?: 0,
            'manifest' => $manifest,
        ];
    }

    /**
     * @return list<array{name: string, size: int, modified: int}>
     */
    public function listBackups(): array
    {
        $dir = $this->backupDirectory();
        $files = File::glob($dir.DIRECTORY_SEPARATOR.'mmhc-backup-*.zip') ?: [];

        $list = [];
        foreach ($files as $path) {
            $list[] = [
                'name' => basename($path),
                'size' => (int) filesize($path),
                'modified' => (int) filemtime($path),
            ];
        }

        usort($list, fn ($a, $b) => $b['modified'] <=> $a['modified']);

        return $list;
    }

    public function deleteBackup(string $filename): void
    {
        $path = $this->resolveBackupPathOrFail($filename);
        File::delete($path);
    }

    /**
     * Restore database + storage from a backup zip (destructive).
     *
     * @param  bool  $skipStorage  When true, only restore database.sql/sqlite (used for testing).
     */
    public function restoreBackup(string $absoluteZipPath, bool $skipStorage = false): void
    {
        if (! is_readable($absoluteZipPath)) {
            throw new RuntimeException('Backup file is not readable.');
        }

        $zip = new ZipArchive;
        if ($zip->open($absoluteZipPath) !== true) {
            throw new RuntimeException('Could not open backup archive.');
        }

        try {
            $manifestJson = $zip->getFromName('manifest.json');
            if ($manifestJson === false) {
                throw new RuntimeException('Invalid backup: manifest.json is missing.');
            }
            /** @var array<string, mixed>|null $manifest */
            $manifest = json_decode($manifestJson, true);
            if (! is_array($manifest) || ($manifest['generator'] ?? '') !== 'mmhc-crm-site-backup') {
                throw new RuntimeException('Invalid backup manifest.');
            }

            $connection = config('database.default');
            $driver = config("database.connections.{$connection}.driver");
            $backupDriver = $manifest['driver'] ?? null;

            if ($backupDriver !== $driver) {
                throw new RuntimeException(
                    "Backup driver ({$backupDriver}) does not match current DB_CONNECTION driver ({$driver}). Switch .env or restore on a matching environment."
                );
            }

            $extractRoot = sys_get_temp_dir().DIRECTORY_SEPARATOR.'mmhc-restore-'.bin2hex(random_bytes(8));
            File::makeDirectory($extractRoot, 0755, true);

            try {
                $zip->extractTo($extractRoot);

                if ($driver === 'mysql') {
                    $sqlFile = $extractRoot.DIRECTORY_SEPARATOR.'database.sql';
                    if (! is_readable($sqlFile)) {
                        throw new RuntimeException('database.sql missing from backup.');
                    }
                    $this->importMysqlFile($connection, $sqlFile);
                } elseif ($driver === 'sqlite') {
                    $dbFile = $extractRoot.DIRECTORY_SEPARATOR.'database.sqlite';
                    if (! is_readable($dbFile)) {
                        throw new RuntimeException('database.sqlite missing from backup.');
                    }
                    $target = database_path(config("database.connections.{$connection}.database"));
                    File::ensureDirectoryExists(dirname($target));
                    if (file_exists($target)) {
                        @unlink($target);
                    }
                    File::copy($dbFile, $target);
                }

                if (! $skipStorage) {
                    $privateSrc = $extractRoot.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'private';
                    $publicSrc = $extractRoot.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'public';
                    if (File::isDirectory($privateSrc)) {
                        File::ensureDirectoryExists(storage_path('app/private'));
                        File::copyDirectory($privateSrc, storage_path('app/private'));
                    }
                    if (File::isDirectory($publicSrc)) {
                        File::ensureDirectoryExists(storage_path('app/public'));
                        File::copyDirectory($publicSrc, storage_path('app/public'));
                    }
                }
            } finally {
                File::deleteDirectory($extractRoot);
            }
        } finally {
            $zip->close();
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildManifestBase(string $connection, string $driver): array
    {
        return [
            'generator' => 'mmhc-crm-site-backup',
            'manifest_version' => self::MANIFEST_VERSION,
            'created_at' => now()->toIso8601String(),
            'app_env' => config('app.env'),
            'app_url' => config('app.url'),
            'laravel' => app()->version(),
            'php' => PHP_VERSION,
            'connection_name' => $connection,
            'driver' => $driver,
        ];
    }

    protected function dumpMysqlToTempFile(string $connection): string
    {
        $cfg = config("database.connections.{$connection}");
        $database = $cfg['database'] ?? '';
        $host = $cfg['host'] ?? '127.0.0.1';
        $port = (string) ($cfg['port'] ?? 3306);
        $username = $cfg['username'] ?? 'root';
        $password = (string) ($cfg['password'] ?? '');

        $cnf = $this->writeMysqlClientCnf($username, $password, $host, $port);

        $sqlPath = tempnam(sys_get_temp_dir(), 'sql');
        if ($sqlPath === false) {
            @unlink($cnf);
            throw new RuntimeException('Could not create temp SQL file.');
        }

        try {
            $binary = $this->findMysqlTool('mysqldump');
            $process = new Process([
                $binary,
                '--defaults-extra-file='.$cnf,
                '--single-transaction',
                '--skip-lock-tables',
                '--set-gtid-purged=OFF',
                $database,
            ]);
            $process->setTimeout(null);
            $process->run();
            if (! $process->isSuccessful()) {
                throw new RuntimeException(
                    'mysqldump failed: '.$process->getErrorOutput().$process->getOutput()
                );
            }
            file_put_contents($sqlPath, $process->getOutput());
        } finally {
            @unlink($cnf);
        }

        return $sqlPath;
    }

    protected function importMysqlFile(string $connection, string $sqlPath): void
    {
        $cfg = config("database.connections.{$connection}");
        $database = $cfg['database'] ?? '';
        $host = $cfg['host'] ?? '127.0.0.1';
        $port = (string) ($cfg['port'] ?? 3306);
        $username = $cfg['username'] ?? 'root';
        $password = (string) ($cfg['password'] ?? '');

        $cnf = $this->writeMysqlClientCnf($username, $password, $host, $port);

        $input = fopen($sqlPath, 'rb');
        if ($input === false) {
            @unlink($cnf);
            throw new RuntimeException('Could not read SQL dump file.');
        }

        try {
            $binary = $this->findMysqlTool('mysql');
            $process = new Process([
                $binary,
                '--defaults-extra-file='.$cnf,
                $database,
            ]);
            $process->setTimeout(null);
            $process->setInput($input);
            $process->run();
            if (! $process->isSuccessful()) {
                throw new RuntimeException(
                    'mysql import failed: '.$process->getErrorOutput().$process->getOutput()
                );
            }
        } finally {
            fclose($input);
            @unlink($cnf);
        }
    }

    protected function writeMysqlClientCnf(string $username, string $password, string $host, string $port): string
    {
        $cnf = tempnam(sys_get_temp_dir(), 'mmy');
        if ($cnf === false) {
            throw new RuntimeException('Could not create temp defaults file.');
        }
        $lines = [
            '[client]',
            'user='.$username,
            'host='.$host,
            'port='.$port,
        ];
        if ($password !== '') {
            $lines[] = 'password="'.str_replace(['\\', '"'], ['\\\\', '\\"'], $password).'"';
        }
        file_put_contents($cnf, implode("\n", $lines)."\n");

        return $cnf;
    }

    /**
     * Add uploaded asset trees only (excludes site-backups inside storage).
     */
    protected function addStorageTreesToZip(ZipArchive $zip): void
    {
        $roots = [
            ['disk' => storage_path('app/private'), 'zip' => 'storage/private'],
            ['disk' => storage_path('app/public'), 'zip' => 'storage/public'],
        ];

        foreach ($roots as $root) {
            if (! File::isDirectory($root['disk'])) {
                continue;
            }
            $this->addDirectoryToZip($zip, $root['disk'], $root['zip']);
        }
    }

    protected function addDirectoryToZip(ZipArchive $zip, string $absoluteDir, string $zipPrefix): void
    {
        $skipBase = realpath($this->backupDirectory());
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($absoluteDir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            /** @var \SplFileInfo $fileInfo */
            $path = $fileInfo->getPathname();
            $real = realpath($path);
            if ($skipBase && $real && str_starts_with($real, $skipBase)) {
                continue;
            }
            if ($fileInfo->isDir()) {
                continue;
            }
            $relative = ltrim(str_replace($absoluteDir, '', $path), DIRECTORY_SEPARATOR);
            $zipPath = $zipPrefix.'/'.str_replace(DIRECTORY_SEPARATOR, '/', $relative);
            $zip->addFile($path, $zipPath);
        }
    }

    protected function findMysqlTool(string $name): string
    {
        $configKey = $name === 'mysqldump' ? 'backup.mysqldump_path' : 'backup.mysql_path';
        $configured = config($configKey);
        if (is_string($configured) && $configured !== '' && is_file($configured)) {
            return $configured;
        }

        $finder = new ExecutableFinder;
        $path = $finder->find($name);
        if ($path === null) {
            throw new RuntimeException(
                "Cannot find `{$name}`. Install MySQL / MariaDB client tools or set BACKUP_MYSQLDUMP_PATH / BACKUP_MYSQL_PATH in .env."
            );
        }

        return $path;
    }

    protected function resolveBackupPathOrFail(string $filename): string
    {
        if (! preg_match('/^mmhc-backup-[a-zA-Z0-9._-]+\.zip$/', $filename)) {
            throw new RuntimeException('Invalid backup filename.');
        }
        $path = $this->backupDirectory().DIRECTORY_SEPARATOR.$filename;
        if (! is_file($path)) {
            throw new RuntimeException('Backup not found.');
        }

        return $path;
    }

    /**
     * @internal
     */
    public function validateBackupFilename(string $filename): string
    {
        return $this->resolveBackupPathOrFail($filename);
    }
}
