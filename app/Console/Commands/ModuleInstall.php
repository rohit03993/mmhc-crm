<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class ModuleInstall extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'module:install {module : The name of the module to install}';

    /**
     * The console command description.
     */
    protected $description = 'Install a CRM module';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $moduleName = $this->argument('module');
        $modulePath = app_path("Modules/{$moduleName}");

        if (!File::exists($modulePath)) {
            $this->error("Module '{$moduleName}' does not exist!");
            return 1;
        }

        $this->info("Installing module: {$moduleName}");

        // Run module migrations
        $migrationsPath = "{$modulePath}/Database/Migrations";
        if (File::exists($migrationsPath)) {
            $this->info("Running migrations for {$moduleName}...");
            Artisan::call('migrate', ['--path' => "app/Modules/{$moduleName}/Database/Migrations"]);
            $this->info("✓ Migrations completed");
        }

        // Publish module assets
        $assetsPath = "{$modulePath}/Assets";
        if (File::exists($assetsPath)) {
            $this->info("Publishing assets for {$moduleName}...");
            $publicPath = public_path("modules/{$moduleName}");
            File::copyDirectory($assetsPath, $publicPath);
            $this->info("✓ Assets published");
        }

        // Clear cache
        Artisan::call('config:cache');
        Artisan::call('route:cache');

        $this->info("✓ Module '{$moduleName}' installed successfully!");
        
        return 0;
    }
}
