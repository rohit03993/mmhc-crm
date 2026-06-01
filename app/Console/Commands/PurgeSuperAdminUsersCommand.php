<?php

namespace App\Console\Commands;

use App\Models\Core\User;
use App\Services\AccountDeletion\AccountDeletionService;
use Illuminate\Console\Command;

/**
 * Removes legacy academics super_admin accounts (e.g. ACAD-SA-001) using full account deletion.
 */
class PurgeSuperAdminUsersCommand extends Command
{
    protected $signature = 'academics:purge-super-admin-users {--dry-run : List accounts only, do not delete}';

    protected $description = 'Delete all users with the removed super_admin role (legacy academics platform accounts)';

    public function handle(AccountDeletionService $deletionService): int
    {
        $actor = User::query()->where('role', 'admin')->whereNull('deleted_at')->orderBy('id')->first();
        if (! $actor) {
            $this->error('No CRM admin user found to act as deleter. Create an admin account first.');

            return self::FAILURE;
        }

        $targets = User::withTrashed()->where('role', 'super_admin')->orderBy('id')->get();
        if ($targets->isEmpty()) {
            $this->info('No super_admin users found.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'unique_id', 'email', 'name'],
            $targets->map(fn (User $u) => [$u->id, $u->unique_id, $u->email, $u->name])->all()
        );

        if ($this->option('dry-run')) {
            $this->comment('Dry run — no accounts deleted.');

            return self::SUCCESS;
        }

        if (! $this->confirm('Permanently delete '.$targets->count().' super_admin account(s)?', true)) {
            $this->comment('Cancelled.');

            return self::SUCCESS;
        }

        $ok = 0;
        $failed = 0;
        foreach ($targets as $target) {
            if ($target->trashed()) {
                $target->forceDelete();
                $ok++;
                $this->line("Force-removed trashed #{$target->id} ({$target->unique_id})");

                continue;
            }

            try {
                $deletionService->delete($target, $actor);
                $target->refresh();
                if ($target->trashed()) {
                    $target->forceDelete();
                }
                $ok++;
                $this->line("Deleted #{$target->id} ({$target->unique_id})");
            } catch (\Throwable $e) {
                $failed++;
                $this->error("Failed #{$target->id}: {$e->getMessage()}");
            }
        }

        $this->info("Done. Deleted: {$ok}, failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
