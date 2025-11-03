<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Services\Models\ServiceRequest;

class CalculateMissingStaffPayouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:calculate-payouts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and update missing staff payouts for all service requests';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Calculating missing staff payouts...');
        
        $serviceRequests = ServiceRequest::whereNotNull('assigned_staff_id')
            ->whereNull('total_staff_payout')
            ->with(['assignedStaff', 'serviceType'])
            ->get();
        
        $count = 0;
        
        foreach ($serviceRequests as $serviceRequest) {
            if (!$serviceRequest->assignedStaff || !$serviceRequest->serviceType) {
                continue;
            }
            
            $staff = $serviceRequest->assignedStaff;
            $serviceType = $serviceRequest->serviceType;
            $dailyStaffPayout = $staff->isNurse() ? $serviceType->nurse_payout : $serviceType->caregiver_payout;
            $totalStaffPayout = $serviceRequest->duration_days * $dailyStaffPayout;
            
            $serviceRequest->update([
                'total_staff_payout' => $totalStaffPayout
            ]);
            
            $count++;
            
            $this->line("✓ Updated Service Request #{$serviceRequest->id} - Staff: {$staff->name} - Payout: ₹{$totalStaffPayout}");
        }
        
        $this->info("\n✅ Completed! Updated {$count} service requests with staff payouts.");
        
        return Command::SUCCESS;
    }
}