<?php

namespace App\Console\Commands;

use App\Models\Core\User;
use App\Modules\Auth\Services\UserService;
use Illuminate\Console\Command;

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
    protected $description = 'Delete all users except CRM admins and academic super admins';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userService = new UserService;

        $protected = User::protectedFromBulkUserDeletionRoleSlugs();
        $nonAdminCount = User::whereNotIn('role', $protected)->count();
        $protectedCount = User::whereIn('role', $protected)->count();

        if ($nonAdminCount === 0) {
            $this->info('No deletable users found. CRM admins and academic super admins are always protected.');

            return 0;
        }

        $this->warn('⚠️  WARNING: This deletes all users except CRM admin!');
        $this->line('');
        $this->info("Users to be deleted: {$nonAdminCount}");
        $this->info("Protected accounts: {$protectedCount}");
        $this->line('');

        if (! $this->option('force')) {
            if (! $this->confirm('Are you sure you want to proceed?', false)) {
                $this->info('Operation cancelled.');

                return 0;
            }
        }

        $this->info('Deleting…');
        $deleted = $userService->deleteAllNonAdminUsers();

        $this->info("✅ Deleted {$deleted} user(s). Protected accounts unchanged: {$protectedCount}");

        return 0;
    }
}
