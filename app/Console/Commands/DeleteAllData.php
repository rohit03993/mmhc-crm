<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Core\User;
use App\Modules\Profiles\Models\Profile;
use App\Modules\Services\Models\ServiceRequest;
use App\Modules\Services\Models\DailyService;

class DeleteAllData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:delete-all {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all users and related data (profiles, service requests, etc.)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🗑️  Deleting all existing data...');
        $this->info('');

        if (!$this->option('force') && !$this->confirm('This will delete ALL users, profiles, service requests, and related data. Are you sure?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        try {
            // Disable foreign key checks temporarily
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Delete in correct order
            $this->info('Deleting daily services...');
            DailyService::truncate();
            $this->info('✅ Daily services deleted');

            $this->info('Deleting service requests...');
            ServiceRequest::truncate();
            $this->info('✅ Service requests deleted');

            $this->info('Deleting profiles...');
            Profile::truncate();
            $this->info('✅ Profiles deleted');

            $this->info('Deleting all users...');
            User::truncate();
            $this->info('✅ Users deleted');

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info('');
            $this->info('✅ All data deleted successfully!');
            $this->info('');
            $this->info('Now you can run: php artisan db:fresh-bihar');

            return 0;
        } catch (\Exception $e) {
            // Re-enable foreign key checks in case of error
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->error('❌ Error: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}

