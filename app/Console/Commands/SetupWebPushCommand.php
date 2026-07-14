<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Minishlink\WebPush\VAPID;
use Symfony\Component\Process\Process;

/**
 * One-command PWA Web Push setup for local + server.
 * Generates VAPID keys (with Windows/OpenSSL fallbacks) and writes .env.
 */
class SetupWebPushCommand extends Command
{
    protected $signature = 'webpush:setup
                            {--force : Overwrite existing VAPID keys in .env}
                            {--show : Print keys only; do not write .env}';

    protected $description = 'Generate VAPID keys and write WEBPUSH_* settings to .env (PWA Web Push)';

    public function handle(): int
    {
        if (! class_exists(VAPID::class)) {
            $this->error('Package missing. Run: composer update minishlink/web-push');

            return self::FAILURE;
        }

        $this->fixWindowsOpenSslConf();

        try {
            $keys = $this->generateKeys();
        } catch (\Throwable $e) {
            $this->error('Could not generate VAPID keys: '.$e->getMessage());
            $this->newLine();
            $this->warn('Fallback: if Node.js is installed, run:');
            $this->line('  npx --yes web-push generate-vapid-keys --json');
            $this->warn('Then paste publicKey / privateKey into .env as VAPID_PUBLIC_KEY / VAPID_PRIVATE_KEY');

            return self::FAILURE;
        }

        $subject = rtrim((string) (config('app.url') ?: 'https://themmhc.com'), '/');
        if ($subject !== '' && ! str_starts_with($subject, 'mailto:') && ! str_starts_with($subject, 'https://') && ! str_starts_with($subject, 'http://')) {
            $subject = 'https://'.$subject;
        }

        $envLines = [
            'WEBPUSH_ENABLED' => 'true',
            'VAPID_SUBJECT' => $subject,
            'VAPID_PUBLIC_KEY' => $keys['publicKey'],
            'VAPID_PRIVATE_KEY' => $keys['privateKey'],
        ];

        $this->info('VAPID keys ready.');
        $this->newLine();
        foreach ($envLines as $k => $v) {
            $this->line($k.'='.$v);
        }

        if ($this->option('show')) {
            return self::SUCCESS;
        }

        $envPath = base_path('.env');
        if (! File::exists($envPath)) {
            $this->error('.env not found at '.$envPath);

            return self::FAILURE;
        }

        $this->writeEnv($envPath, $envLines, (bool) $this->option('force'));
        $this->newLine();
        $this->info('Wrote keys to .env');
        $this->warn('Next (same on local and server):');
        $this->line('  php artisan config:clear');
        $this->line('  php artisan migrate');
        $this->line('  php artisan view:clear');

        return self::SUCCESS;
    }

    /**
     * @return array{publicKey: string, privateKey: string}
     */
    private function generateKeys(): array
    {
        // 1) Library method (works on most Linux servers)
        try {
            $keys = VAPID::createVapidKeys();
            if (! empty($keys['publicKey']) && ! empty($keys['privateKey'])) {
                $this->comment('Generated via PHP web-push library.');

                return $keys;
            }
        } catch (\Throwable $e) {
            $this->comment('PHP library keygen failed ('.$e->getMessage().'), trying OpenSSL CLI…');
        }

        // 2) openssl CLI + PHP decode (works when openssl binary exists)
        if ($cli = $this->generateViaOpenSslCli()) {
            $this->comment('Generated via OpenSSL CLI.');

            return $cli;
        }

        // 3) Node web-push (works on Windows if Node is installed)
        if ($node = $this->generateViaNode()) {
            $this->comment('Generated via npx web-push.');

            return $node;
        }

        throw new \RuntimeException('All key generation methods failed.');
    }

    /**
     * @return array{publicKey: string, privateKey: string}|null
     */
    private function generateViaOpenSslCli(): ?array
    {
        $openssl = $this->findOpenSslBinary();
        if (! $openssl) {
            return null;
        }

        $tmp = storage_path('app/webpush_tmp_'.uniqid('', true));
        File::ensureDirectoryExists(dirname($tmp));
        $pem = $tmp.'.pem';

        try {
            $gen = new Process([$openssl, 'ecparam', '-name', 'prime256v1', '-genkey', '-noout', '-out', $pem]);
            $gen->setTimeout(30);
            $gen->run();
            if (! $gen->isSuccessful() || ! File::exists($pem)) {
                return null;
            }

            $pemContents = File::get($pem);
            $key = openssl_pkey_get_private($pemContents);
            if ($key === false) {
                return null;
            }

            $details = openssl_pkey_get_details($key);
            if (! is_array($details) || ($details['type'] ?? null) !== OPENSSL_KEYTYPE_EC) {
                return null;
            }

            $x = $details['ec']['x'] ?? null;
            $y = $details['ec']['y'] ?? null;
            $d = $details['ec']['d'] ?? null;
            if (! is_string($x) || ! is_string($y) || ! is_string($d)) {
                return null;
            }

            // Uncompressed public key: 0x04 || X || Y
            $public = "\x04".$x.$y;
            if (strlen($public) !== 65) {
                // pad X/Y to 32 bytes if needed
                $x = str_pad($x, 32, "\0", STR_PAD_LEFT);
                $y = str_pad($y, 32, "\0", STR_PAD_LEFT);
                $d = str_pad($d, 32, "\0", STR_PAD_LEFT);
                $public = "\x04".$x.$y;
            }
            $d = str_pad($d, 32, "\0", STR_PAD_LEFT);

            return [
                'publicKey' => $this->base64UrlEncode($public),
                'privateKey' => $this->base64UrlEncode($d),
            ];
        } finally {
            if (File::exists($pem)) {
                File::delete($pem);
            }
        }
    }

    /**
     * @return array{publicKey: string, privateKey: string}|null
     */
    private function generateViaNode(): ?array
    {
        $process = new Process(['npx', '--yes', 'web-push', 'generate-vapid-keys', '--json']);
        $process->setTimeout(120);
        $process->run();
        if (! $process->isSuccessful()) {
            return null;
        }

        $json = json_decode(trim($process->getOutput()), true);
        if (! is_array($json)) {
            return null;
        }

        $public = $json['publicKey'] ?? $json['public'] ?? null;
        $private = $json['privateKey'] ?? $json['private'] ?? null;
        if (! is_string($public) || ! is_string($private) || $public === '' || $private === '') {
            return null;
        }

        return [
            'publicKey' => $public,
            'privateKey' => $private,
        ];
    }

    private function findOpenSslBinary(): ?string
    {
        foreach (['openssl', 'openssl.exe'] as $bin) {
            $process = new Process(PHP_OS_FAMILY === 'Windows' ? ['where', $bin] : ['which', $bin]);
            $process->run();
            if ($process->isSuccessful()) {
                $path = trim(explode("\n", str_replace("\r", '', $process->getOutput()))[0] ?? '');
                if ($path !== '') {
                    return $path;
                }
            }
        }

        return null;
    }

    private function fixWindowsOpenSslConf(): void
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return;
        }

        $candidates = [
            getenv('OPENSSL_CONF') ?: null,
            PHP_BINARY ? dirname(PHP_BINARY).DIRECTORY_SEPARATOR.'extras'.DIRECTORY_SEPARATOR.'ssl'.DIRECTORY_SEPARATOR.'openssl.cnf' : null,
            'C:\\php\\extras\\ssl\\openssl.cnf',
            'C:\\Program Files\\PHP\\extras\\ssl\\openssl.cnf',
        ];

        foreach ($candidates as $path) {
            if (is_string($path) && $path !== '' && is_file($path)) {
                putenv('OPENSSL_CONF='.$path);
                $_ENV['OPENSSL_CONF'] = $path;

                return;
            }
        }
    }

    /**
     * @param  array<string, string>  $values
     */
    private function writeEnv(string $envPath, array $values, bool $force): void
    {
        $content = File::get($envPath);

        foreach ($values as $key => $value) {
            $line = $key.'='.$this->escapeEnvValue($value);
            if (preg_match('/^'.preg_quote($key, '/').'=.*/m', $content)) {
                if (! $force && ! in_array($key, ['WEBPUSH_ENABLED', 'VAPID_SUBJECT'], true)) {
                    // Keep existing keys unless --force
                    if (preg_match('/^'.preg_quote($key, '/').'=(.*)$/m', $content, $m)) {
                        $existing = trim($m[1], " \t\"'");
                        if ($existing !== '') {
                            $this->comment("Keeping existing {$key}");
                            continue;
                        }
                    }
                }
                $content = preg_replace('/^'.preg_quote($key, '/').'=.*/m', $line, $content, 1) ?? $content;
            } else {
                $content = rtrim($content).PHP_EOL.$line.PHP_EOL;
            }
        }

        File::put($envPath, $content);
    }

    private function escapeEnvValue(string $value): string
    {
        if (preg_match('/\s|#|"/', $value)) {
            return '"'.str_replace('"', '\"', $value).'"';
        }

        return $value;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
