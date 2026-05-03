<?php

namespace Database\Seeders;

use App\Models\Core\User;
use App\Modules\Incentives\Services\IncentiveCalculatorService;
use App\Modules\Plans\Models\Plan;
use App\Modules\Plans\Models\Subscription;
use App\Modules\Services\Models\ServiceRequest;
use App\Modules\Services\Models\ServiceType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class IncentiveDemoFlowSeeder extends Seeder
{
    private const NURSE_VISITS_FOR_SLAB_DEMO = 55;

    public function run(): void
    {
        // Ensure baseline demo users/service types/plans/rule set exist.
        $this->call([
            ServiceTypesSeeder::class,
            SubscriptionPlansSeeder::class,
            DemoDataSeeder::class,
            IncentiveRuleSetSeeder::class,
        ]);

        $admin = User::query()->where('role', 'admin')->orderBy('id')->first();
        $nurse = User::query()->where('role', 'nurse')->orderBy('id')->first();
        $patient = User::query()->where('role', 'patient')->orderBy('id')->first();
        $serviceType = ServiceType::query()->orderBy('duration_hours', 'desc')->orderBy('id')->first();
        $plan = Plan::query()->orderBy('id')->first();

        if (! $admin || ! $nurse || ! $patient || ! $serviceType || ! $plan) {
            $this->command->error('Missing admin/nurse/patient/service type/plan required for incentive demo seeding.');

            return;
        }

        if (empty($nurse->experience_tier)) {
            $nurse->update(['experience_tier' => '3y']);
        }

        $calculator = app(IncentiveCalculatorService::class);
        $serviceLedgers = $this->seedNurseVisitHistoryForSlabDemo(
            $calculator,
            $nurse,
            $patient,
            $serviceType
        );

        $subscription = Subscription::query()->updateOrCreate(
            ['notes' => 'INCENTIVE_DEMO_SUBSCRIPTION_ACTIVE'],
            [
                'user_id' => $patient->id,
                'plan_id' => $plan->id,
                'referrer_id' => $nurse->id,
                'payment_frequency' => 'half_yearly',
                'status' => 'active',
                'start_date' => Carbon::today()->subDay(),
                'end_date' => Carbon::today()->addYears(1),
                'care_benefits_years' => 0,
                'payable_years' => 1,
                'base_amount' => 10000,
                'gst_amount' => 1800,
                'gst_rate' => 18,
                'total_amount' => 11800,
                'paid_amount' => 11800,
                'payment_status' => 'paid',
                'payment_verified_by' => $admin->id,
                'payment_verified_at' => Carbon::now()->subHours(2),
                'referral_commission_amount' => 0,
                'referral_base_amount' => null,
                'referral_growth_percent' => null,
                'referral_dta_percent' => null,
                'referral_payment_processed' => false,
                'referral_payment_processed_at' => null,
                'auto_renew' => false,
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subHours(2),
            ]
        );

        $subscriptionLedger = $calculator->createOrUpdateSubscriptionSaleLedger($subscription);

        $this->command->info('Incentive demo flow seeded successfully.');
        $this->command->line('Nurse visit slab demo count: '.self::NURSE_VISITS_FOR_SLAB_DEMO);
        $this->command->line('Last seeded service request id: '.$serviceLedgers['last_service_id'].', ledger id: '.$serviceLedgers['last_ledger_id']);
        $this->command->line('Subscription id: '.$subscription->id.', ledger id: '.($subscriptionLedger?->id ?? 'null'));
    }

    /**
     * Seed approved completed nurse visits so growth/DtA slab jumps are visible.
     *
     * @return array{last_service_id:int,last_ledger_id:int}
     */
    private function seedNurseVisitHistoryForSlabDemo(
        IncentiveCalculatorService $calculator,
        User $nurse,
        User $patient,
        ServiceType $serviceType
    ): array {
        $durationDays = 1;
        $dailyCharge = (float) ($serviceType->patient_charge ?? 0);
        $totalAmount = $dailyCharge * $durationDays;

        $lastServiceId = 0;
        $lastLedgerId = 0;

        for ($i = 1; $i <= self::NURSE_VISITS_FOR_SLAB_DEMO; $i++) {
            $startDate = Carbon::today()->subDays(self::NURSE_VISITS_FOR_SLAB_DEMO + 2 - $i);
            $endDate = (clone $startDate)->addDays($durationDays - 1);

            $service = ServiceRequest::query()->updateOrCreate(
                ['notes' => 'INCENTIVE_DEMO_SERVICE_APPROVED_'.$i],
                [
                    'patient_id' => $patient->id,
                    'service_type_id' => $serviceType->id,
                    'preferred_staff_type' => 'nurse',
                    'preferred_staff_id' => $nurse->id,
                    'assigned_staff_id' => $nurse->id,
                    'location' => $patient->address ?? 'Demo location',
                    'contact_person' => $patient->name,
                    'contact_phone' => $patient->phone,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'duration_days' => $durationDays,
                    'total_amount' => $totalAmount,
                    'prepaid_amount' => 0.00,
                    'payment_status' => 'paid',
                    'status' => 'completed',
                    'assigned_at' => (clone $startDate)->addHours(1),
                    'started_at' => (clone $startDate)->addHours(2),
                    'completed_at' => (clone $startDate)->addHours(12),
                    'admin_approved_at' => (clone $startDate)->addHours(14),
                    'special_requirements' => 'Incentive slab demo visit #'.$i,
                ]
            );

            $ledger = $calculator->createOrUpdateServiceLedger($nurse, $service, false);
            $lastServiceId = (int) $service->id;
            $lastLedgerId = (int) $ledger->id;
        }

        return [
            'last_service_id' => $lastServiceId,
            'last_ledger_id' => $lastLedgerId,
        ];
    }
}
