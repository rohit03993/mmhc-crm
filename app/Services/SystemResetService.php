<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Core\User;
use App\Modules\Services\Models\ServiceRequest;
use App\Modules\Services\Models\DailyService;
use App\Modules\Rewards\Models\CaregiverReward;
use App\Modules\Referrals\Models\Referral;
use App\Modules\Plans\Models\Subscription;
use App\Modules\Payments\Models\StaffPayment;

class SystemResetService
{
    /**
     * Reset all system data except admin user and system configuration
     * 
     * @return array Statistics about what was deleted
     */
    public function resetSystemData(): array
    {
        $stats = [
            'users_deleted' => 0,
            'service_requests_deleted' => 0,
            'daily_services_deleted' => 0,
            'rewards_deleted' => 0,
            'referrals_deleted' => 0,
            'subscriptions_deleted' => 0,
            'staff_payments_deleted' => 0,
            'profiles_deleted' => 0,
            'documents_deleted' => 0,
            'success' => false,
            'error' => null,
        ];

        try {
            // Get admin user ID to preserve
            $adminUser = User::where('role', 'admin')->first();
            
            if (!$adminUser) {
                throw new \Exception('Admin user not found. Cannot proceed with reset.');
            }

            $adminId = $adminUser->id;

            Log::warning('System Reset Started', [
                'admin_id' => $adminId,
                'admin_email' => $adminUser->email,
                'timestamp' => now()->toDateTimeString(),
            ]);

            // Disable foreign key checks temporarily (MySQL only)
            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            }

            DB::beginTransaction();

            try {
                // 1. Delete Daily Services (must be deleted before Service Requests due to foreign keys)
                $stats['daily_services_deleted'] = DailyService::count();
                DailyService::query()->delete();

                // 2. Delete Service Requests
                $stats['service_requests_deleted'] = ServiceRequest::count();
                ServiceRequest::query()->delete();

                // 3. Delete Caregiver Rewards
                $stats['rewards_deleted'] = CaregiverReward::count();
                CaregiverReward::query()->delete();

                // 4. Delete Referrals
                $stats['referrals_deleted'] = Referral::count();
                Referral::query()->delete();

                // 5. Delete Subscriptions (user subscriptions, but keep plans)
                $stats['subscriptions_deleted'] = Subscription::count();
                Subscription::query()->delete();

                // 6. Delete Staff Payments
                $stats['staff_payments_deleted'] = StaffPayment::count();
                StaffPayment::query()->delete();

                // 7. Delete Profiles (if exists)
                if (DB::getSchemaBuilder()->hasTable('profiles')) {
                    $stats['profiles_deleted'] = DB::table('profiles')
                        ->where('user_id', '!=', $adminId)
                        ->count();
                    DB::table('profiles')->where('user_id', '!=', $adminId)->delete();
                }

                // 8. Delete Documents (if exists)
                if (DB::getSchemaBuilder()->hasTable('documents')) {
                    $stats['documents_deleted'] = DB::table('documents')
                        ->where('user_id', '!=', $adminId)
                        ->count();
                    DB::table('documents')->where('user_id', '!=', $adminId)->delete();
                }

                // 9. Reset admin user's reward points and related fields
                $adminUser->update([
                    'reward_points' => 0,
                    'is_active' => true,
                ]);

                // 10. Delete all non-admin users
                $stats['users_deleted'] = User::where('id', '!=', $adminId)->count();
                User::where('id', '!=', $adminId)->delete();

                // Commit transaction
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            } finally {
                // Re-enable foreign key checks (MySQL only)
                if (DB::getDriverName() === 'mysql') {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                }
            }

            $stats['success'] = true;

            Log::info('System Reset Completed Successfully', [
                'admin_id' => $adminId,
                'stats' => $stats,
                'timestamp' => now()->toDateTimeString(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            $stats['error'] = $e->getMessage();
            
            Log::error('System Reset Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            throw $e;
        }

        return $stats;
    }

    /**
     * Get statistics about current system data
     * 
     * @return array Current data counts
     */
    public function getSystemStats(): array
    {
        $adminUser = User::where('role', 'admin')->first();
        $adminId = $adminUser ? $adminUser->id : 0;

        return [
            'total_users' => User::count(),
            'non_admin_users' => User::where('id', '!=', $adminId)->count(),
            'patients' => User::where('role', 'patient')->count(),
            'nurses' => User::where('role', 'nurse')->count(),
            'caregivers' => User::where('role', 'caregiver')->count(),
            'service_requests' => ServiceRequest::count(),
            'daily_services' => DailyService::count(),
            'rewards' => CaregiverReward::count(),
            'referrals' => Referral::count(),
            'subscriptions' => Subscription::count(),
            'staff_payments' => StaffPayment::count(),
            'profiles' => DB::getSchemaBuilder()->hasTable('profiles') ? DB::table('profiles')->where('user_id', '!=', $adminId)->count() : 0,
            'documents' => DB::getSchemaBuilder()->hasTable('documents') ? DB::table('documents')->where('user_id', '!=', $adminId)->count() : 0,
        ];
    }
}

