<?php

namespace Database\Seeders;

use App\Models\Core\User;
use App\Modules\Incentives\Services\IncentiveCalculatorService;
use App\Modules\Plans\Models\Plan;
use App\Modules\Plans\Models\Subscription;
use App\Modules\Rewards\Services\RewardService;
use App\Modules\Services\Models\ServiceRequest;
use App\Modules\Services\Models\ServiceType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class IncentiveDemoFlowSeeder extends Seeder
{
    private const NURSE_VISITS_FOR_SLAB_DEMO = 55;
    private const PATIENT_REWARDS_FOR_SLAB_DEMO = 55;

    /**
     * Nurse visit slab demo. Run after {@see HealthcareCrmDemoSeeder} (or equivalent prerequisites).
     */
    public function run(): void
    {
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
        $patientRewards = $this->seedPatientRewardsForSlabDemo($nurse);

        $planOptions = $plan->payment_options ?? [];
        $halfYearly = is_array($planOptions) ? ($planOptions['half_yearly'] ?? null) : null;
        $baseAmount = (float) ($halfYearly['price'] ?? 5994.00);
        $payableYears = (int) ($halfYearly['payable_years'] ?? 7);
        $careYears = (int) ($halfYearly['care_benefits_years'] ?? 3);
        $totalYears = $payableYears + $careYears;
        $gstRate = 18.00;
        $gstAmount = round(($baseAmount * $gstRate) / 100, 2);
        $totalAmount = round($baseAmount + $gstAmount, 2);
        $startDate = Carbon::today()->subDay();
        $endDate = (clone $startDate)->addYears($totalYears);

        $subscription = Subscription::query()->updateOrCreate(
            ['notes' => 'INCENTIVE_DEMO_SUBSCRIPTION_ACTIVE'],
            [
                'user_id' => $patient->id,
                'plan_id' => $plan->id,
                'referrer_id' => $nurse->id,
                'payment_frequency' => 'half_yearly',
                'status' => 'active',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'care_benefits_years' => $careYears,
                'payable_years' => $payableYears,
                'base_amount' => $baseAmount,
                'gst_amount' => $gstAmount,
                'gst_rate' => $gstRate,
                'total_amount' => $totalAmount,
                'paid_amount' => $totalAmount,
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
        $this->command->line('Pending verified patient rewards: '.$patientRewards);
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
                    'completion_verified_at' => (clone $startDate)->addHours(13),
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

    /**
     * Create verified, unpaid patient reward rows with slab-applied amount growth.
     */
    private function seedPatientRewardsForSlabDemo(User $staff): int
    {
        $rewardService = app(RewardService::class);

        for ($i = 1; $i <= self::PATIENT_REWARDS_FOR_SLAB_DEMO; $i++) {
            $reward = \App\Modules\Rewards\Models\CaregiverReward::query()->updateOrCreate(
                [
                    'user_id' => $staff->id,
                    'patient_phone' => '+91910090'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                ],
                [
                    'patient_name' => "Incentive Reward Patient {$i}",
                    'patient_email' => "incentive.reward{$i}@mmhc.local",
                    'patient_age' => 45,
                    'patient_address' => 'Demo address',
                    'patient_pincode' => '462001',
                    'hospital_name' => 'MMHC Demo Hospital',
                    'treatment_details' => 'Patient reward slab demonstration',
                    'reward_points' => 1,
                    'reward_amount' => self::POINT_VALUE,
                    'verification_status' => 'pending',
                    'verified_at' => null,
                    'payment_processed' => false,
                    'payment_processed_at' => null,
                    'verification_otp_hash' => null,
                    'verification_otp_expires_at' => null,
                    'verification_otp_attempts' => 0,
                    'verification_otp_sent_at' => null,
                    'verification_otp_sent_to' => null,
                ]
            );
        }

        return (int) \App\Modules\Rewards\Models\CaregiverReward::query()
            ->where('user_id', $staff->id)
            ->verified()
            ->where(function ($q) {
                $q->where('payment_processed', false)->orWhereNull('payment_processed');
            })
            ->count();
    }
}
