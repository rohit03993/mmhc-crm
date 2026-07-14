<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * @deprecated Use php artisan webpush:setup
 */
class GenerateWebPushVapidKeys extends Command
{
    protected $signature = 'webpush:vapid {--force : Passed to webpush:setup} {--show : Passed to webpush:setup}';

    protected $description = 'Alias of webpush:setup (generate VAPID keys for PWA Web Push)';

    public function handle(): int
    {
        return $this->call('webpush:setup', [
            '--force' => (bool) $this->option('force'),
            '--show' => (bool) $this->option('show'),
        ]);
    }
}
