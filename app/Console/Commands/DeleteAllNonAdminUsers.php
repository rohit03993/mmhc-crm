<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Core\User;
use App\Modules\Auth\Services\UserService;

class DeleteAllNonAdminUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:delete-non-admin {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all users except admins';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userService = new UserService();
        
        // Count users to be deleted
        $nonAdminCount = User::where('role', '!=', 'admin')->count();
        $adminCount = User::where('role', 'admin')->count();
        
        if ($nonAdminCount === 0) {
            $this->info('No non-admin users found. Nothing to delete.');
            return 0;
        }
        
        // Show summary
        $this->warn('⚠️  WARNING: This will delete ALL non-admin users!');
        $this->line('');
        $this->info("Users to be deleted: {$nonAdminCount}");
        $this->info("Admin users (protected): {$adminCount}");
        $this->line('');
        
        // Get confirmation unless --force flag is used
        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to delete all non-admin users?', false)) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }
        
        // Delete users
        $this->info('Deleting non-admin users...');
        $deleted = $userService->deleteAllNonAdminUsers();
        
        $this->info("✅ Successfully deleted {$deleted} non-admin user(s).");
        $this->info("Admin users remain intact: {$adminCount}");
        
        return 0;
    }
}

