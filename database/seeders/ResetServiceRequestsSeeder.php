<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Services\Models\ServiceRequest;
use App\Models\Core\User;
use Illuminate\Support\Facades\DB;

class ResetServiceRequestsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Cleaning and resetting service requests data...');
        
        DB::beginTransaction();
        
        try {
            $totalRequests = ServiceRequest::count();
            $this->command->info("Found {$totalRequests} service requests to process.");
            
            $updated = 0;
            $cleaned = 0;
            
            ServiceRequest::chunk(100, function ($requests) use (&$updated, &$cleaned) {
                foreach ($requests as $request) {
                    $needsUpdate = false;
                    
                    // Clean up preferred_staff_id if it references invalid staff
                    if ($request->preferred_staff_id) {
                        $preferredStaff = User::find($request->preferred_staff_id);
                        if (!$preferredStaff || !$preferredStaff->isStaff() || !$preferredStaff->is_active) {
                            $request->preferred_staff_id = null;
                            $needsUpdate = true;
                            $cleaned++;
                        }
                    }
                    
                    // Ensure assigned_staff_id is valid if set
                    if ($request->assigned_staff_id) {
                        $assignedStaff = User::find($request->assigned_staff_id);
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
            
            $this->command->info("✅ Service requests data cleaned successfully!");
            $this->command->info("   - Total requests processed: {$totalRequests}");
            $this->command->info("   - Requests updated: {$updated}");
            $this->command->info("   - Invalid references cleaned: {$cleaned}");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ Error cleaning data: " . $e->getMessage());
            throw $e;
        }
    }
}

