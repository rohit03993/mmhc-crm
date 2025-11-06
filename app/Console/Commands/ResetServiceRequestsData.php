<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Services\Models\ServiceRequest;
use Illuminate\Support\Facades\DB;

class ResetServiceRequestsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service-requests:reset {--force : Force reset without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset and clean service requests data (useful after schema changes)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will reset all service requests data. Are you sure?', false)) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('Resetting service requests data...');
        
        try {
            DB::beginTransaction();

            // Get count before reset
            $totalRequests = ServiceRequest::count();
            $this->info("Found {$totalRequests} service requests to process.");

            // Update existing service requests:
            // 1. Set preferred_staff_id to null if it doesn't match valid staff
            // 2. Ensure all pending requests have proper status
            // 3. Clean up any orphaned references
            
            $updated = 0;
            $cleaned = 0;

            ServiceRequest::chunk(100, function ($requests) use (&$updated, &$cleaned) {
                foreach ($requests as $request) {
                    $needsUpdate = false;
                    
                    // Clean up preferred_staff_id if it references invalid staff
                    if ($request->preferred_staff_id) {
                        $preferredStaff = \App\Models\Core\User::find($request->preferred_staff_id);
                        if (!$preferredStaff || !$preferredStaff->isStaff() || !$preferredStaff->is_active) {
                            $request->preferred_staff_id = null;
                            $needsUpdate = true;
                            $cleaned++;
                        }
                    }
                    
                    // Ensure assigned_staff_id is valid if set
                    if ($request->assigned_staff_id) {
                        $assignedStaff = \App\Models\Core\User::find($request->assigned_staff_id);
                        if (!$assignedStaff || !$assignedStaff->isStaff() || !$assignedStaff->is_active) {
                            $request->assigned_staff_id = null;
                            $request->status = 'pending';
                            $request->assigned_at = null;
                            $needsUpdate = true;
                            $cleaned++;
                        }
                    }
                    
                    // Ensure status is valid
                    $validStatuses = ['pending', 'assigned', 'in_progress', 'completed', 'cancelled'];
                    if (!in_array($request->status, $validStatuses)) {
                        $request->status = 'pending';
                        $needsUpdate = true;
                        $cleaned++;
                    }
                    
                    // Ensure payment fields are properly set
                    if (is_null($request->prepaid_amount)) {
                        $request->prepaid_amount = 0.00;
                        $needsUpdate = true;
                    }
                    
                    if (empty($request->payment_status)) {
                        $request->payment_status = 'pending';
                        $needsUpdate = true;
                    }
                    
                    if ($needsUpdate) {
                        $request->save();
                        $updated++;
                    }
                }
            });

            DB::commit();

            $this->info("✅ Service requests data reset completed!");
            $this->line("   - Total requests processed: {$totalRequests}");
            $this->line("   - Requests updated: {$updated}");
            $this->line("   - Invalid references cleaned: {$cleaned}");
            
            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error resetting data: " . $e->getMessage());
            return 1;
        }
    }
}

