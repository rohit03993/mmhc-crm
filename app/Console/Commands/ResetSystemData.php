<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SystemResetService;

class ResetSystemData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:reset-data {--force : Force reset without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all system data except admin user and system configuration (plans, service types, etc.)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = new SystemResetService();

        // Show current stats
        $this->info('Current System Data:');
        $stats = $service->getSystemStats();
        $this->table(
            ['Category', 'Count'],
            [
                ['Total Users (non-admin)', $stats['non_admin_users']],
                ['Patients', $stats['patients']],
                ['Nurses', $stats['nurses']],
                ['Caregivers', $stats['caregivers']],
                ['Service Requests', $stats['service_requests']],
                ['Daily Services', $stats['daily_services']],
                ['Rewards', $stats['rewards']],
                ['Referrals', $stats['referrals']],
                ['Subscriptions', $stats['subscriptions']],
                ['Staff Payments', $stats['staff_payments']],
            ]
        );

        if (!$this->option('force')) {
            $this->warn('⚠️  WARNING: This will delete ALL user data except admin account!');
            $this->warn('⚠️  This action CANNOT be undone!');
            
            if (!$this->confirm('Do you want to proceed?', false)) {
                $this->info('Reset cancelled.');
                return 0;
            }

            // Double confirmation
            if (!$this->confirm('Are you ABSOLUTELY SURE? Type "yes" to confirm', false)) {
                $this->info('Reset cancelled.');
                return 0;
            }
        }

        $this->info('Starting system reset...');

        try {
            $result = $service->resetSystemData();

            if ($result['success']) {
                $this->info('✅ System reset completed successfully!');
                $this->table(
                    ['Category', 'Deleted'],
                    [
                        ['Users', $result['users_deleted']],
                        ['Service Requests', $result['service_requests_deleted']],
                        ['Daily Services', $result['daily_services_deleted']],
                        ['Rewards', $result['rewards_deleted']],
                        ['Referrals', $result['referrals_deleted']],
                        ['Subscriptions', $result['subscriptions_deleted']],
                        ['Staff Payments', $result['staff_payments_deleted']],
                        ['Profiles', $result['profiles_deleted'] ?? 0],
                        ['Documents', $result['documents_deleted'] ?? 0],
                    ]
                );
                return 0;
            } else {
                $this->error('❌ Reset failed: ' . ($result['error'] ?? 'Unknown error'));
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Reset failed: ' . $e->getMessage());
            return 1;
        }
    }
}
