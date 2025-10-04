<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerModules();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadModuleRoutes();
        $this->loadModuleViews();
        $this->loadModuleMigrations();
    }

    /**
     * Register all modules
     */
    protected function registerModules(): void
    {
        $modulesPath = app_path('Modules');
        
        if (File::exists($modulesPath)) {
            $modules = File::directories($modulesPath);
            
            foreach ($modules as $module) {
                $moduleName = basename($module);
                $this->registerModule($moduleName);
            }
        }
    }

    /**
     * Register individual module
     */
    protected function registerModule(string $moduleName): void
    {
        $modulePath = app_path("Modules/{$moduleName}");
        
        // Register module service provider if exists
        $providerPath = "{$modulePath}/Providers/{$moduleName}ServiceProvider.php";
        if (File::exists($providerPath)) {
            $providerClass = "App\\Modules\\{$moduleName}\\Providers\\{$moduleName}ServiceProvider";
            $this->app->register($providerClass);
        }
    }

    /**
     * Load module routes
     */
    protected function loadModuleRoutes(): void
    {
        $modulesPath = app_path('Modules');
        
        if (File::exists($modulesPath)) {
            $modules = File::directories($modulesPath);
            
            foreach ($modules as $module) {
                $moduleName = basename($module);
                $routesPath = "{$module}/Routes";
                
                if (File::exists($routesPath)) {
                    // Load web routes
                    if (File::exists("{$routesPath}/web.php")) {
                        $this->loadRoutesFrom("{$routesPath}/web.php");
                    }
                    
                    // Load API routes
                    if (File::exists("{$routesPath}/api.php")) {
                        $this->loadRoutesFrom("{$routesPath}/api.php");
                    }
                }
            }
        }
    }

    /**
     * Load module views
     */
    protected function loadModuleViews(): void
    {
        $modulesPath = app_path('Modules');
        
        if (File::exists($modulesPath)) {
            $modules = File::directories($modulesPath);
            
            foreach ($modules as $module) {
                $moduleName = basename($module);
                $viewsPath = "{$module}/Views";
                
                if (File::exists($viewsPath)) {
                    $this->loadViewsFrom($viewsPath, strtolower($moduleName));
                }
            }
        }
    }

    /**
     * Load module migrations
     */
    protected function loadModuleMigrations(): void
    {
        $modulesPath = app_path('Modules');
        
        if (File::exists($modulesPath)) {
            $modules = File::directories($modulesPath);
            
            foreach ($modules as $module) {
                $moduleName = basename($module);
                $migrationsPath = "{$module}/Database/Migrations";
                
                if (File::exists($migrationsPath)) {
                    $this->loadMigrationsFrom($migrationsPath);
                }
            }
        }
    }
}
