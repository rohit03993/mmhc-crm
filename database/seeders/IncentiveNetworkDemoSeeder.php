<?php

namespace Database\Seeders;

use App\Models\Core\User;
use App\Modules\Incentives\Services\IncentiveCalculatorService;
use App\Modules\Plans\Models\Plan;
use App\Modules\Plans\Models\Subscription;
use App\Modules\Rewards\Models\CaregiverReward;
use App\Modules\Services\Models\ServiceRequest;
use App\Modules\Services\Models\ServiceType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IncentiveNetworkDemoSeeder extends Seeder
{
    private const TOTAL_NURSES = 30;

    private const TOTAL_CAREGIVERS = 25;

    private const TOTAL_PATIENTS = 120;

    private const SERVICES_PER_STAFF = 8;

    private const SUBSCRIPTIONS_PER_STAFF = 2;

    private const REWARDS_PER_STAFF = 2;

    public function run(): void
    {
        $this->call([
            ServiceTypesSeeder::class,
            SubscriptionPlansSeeder::class,
            DemoDataSeeder::class,
            IncentiveRuleSetSeeder::class,
        ]);

        $admin = User::query()->where('role', 'admin')->orderBy('id')->first();
        $plan = Plan::query()->orderBy('id')->first();
        $serviceTypes = ServiceType::query()->orderBy('id')->get();

        if (! $admin || ! $plan || $serviceTypes->isEmpty()) {
            $this->command->error('Missing admin/plan/service types for incentive network demo seeding.');

            return;
        }

        $nurses = $this->seedStaffByRole('nurse', self::TOTAL_NURSES);
        $caregivers = $this->seedStaffByRole('caregiver', self::TOTAL_CAREGIVERS);
        $patients = $this->seedPatients(self::TOTAL_PATIENTS);

        $staffMembers = $nurses->concat($caregivers)->values();
        $this->seedStaffReferrals($staffMembers);
        $this->seedPatientRewards($staffMembers);
        $this->seedSubscriptions($staffMembers, $patients, $plan, $admin);
        $this->seedServiceRequests($staffMembers, $patients, $serviceTypes, $admin);

        $this->command->info('Incentive network demo data seeded successfully.');
        $this->command->line('Nurses: '.$nurses->count());
        $this->command->line('Caregivers: '.$caregivers->count());
        $this->command->line('Patients: '.$patients->count());
    }

    private function seedStaffByRole(string $role, int $targetCount)
    {
        $defaultPoint = DB::raw("ST_GeomFromText('POINT(0 0)', 4326)");
        $created = collect();

        for ($i = 1; $i <= $targetCount; $i++) {
            $isFirstDemo = $i === 1;
            $email = $isFirstDemo
                ? ($role === 'nurse' ? 'nurse@demo.com' : 'caregiver@demo.com')
                : "{$role}.demo{$i}@mmhc.local";
            $uniqueId = ($role === 'nurse' ? 'N' : 'C').'-UID-'.str_pad((string) $i, 6, '0', STR_PAD_LEFT);
            $phone = (string) (9000000000 + ($role === 'nurse' ? 0 : 500) + $i);
            $namePrefix = $role === 'nurse' ? 'Nurse' : 'Caregiver';
            $qualification = $role === 'nurse' ? 'B.Sc Nursing' : 'General Care';

            $staff = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $isFirstDemo
                        ? ($role === 'nurse' ? 'Dr. Priya Sharma' : 'Ram Prasad Yadav')
                        : "{$namePrefix} Demo {$i}",
                    'phone' => $phone,
                    'password' => 'password123',
                    'plain_password' => 'password123',
                    'role' => $role,
                    'unique_id' => $uniqueId,
                    'qualification' => $qualification,
                    'experience' => $this->experienceBandForIndex($i),
                    'experience_tier' => $this->experienceTierForIndex($i),
                    'address' => "Demo {$namePrefix} Address {$i}",
                    'location' => $defaultPoint,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            $created->push($staff);
        }

        return $created;
    }

    private function seedPatients(int $targetCount)
    {
        $defaultPoint = DB::raw("ST_GeomFromText('POINT(0 0)', 4326)");
        $patients = collect();

        for ($i = 1; $i <= $targetCount; $i++) {
            $isFirstDemo = $i === 1;
            $email = $isFirstDemo ? 'patient@demo.com' : "patient.demo{$i}@mmhc.local";
            $uniqueId = 'P-UID-'.str_pad((string) $i, 6, '0', STR_PAD_LEFT);
            $phone = (string) (9300000000 + $i);

            $patient = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $isFirstDemo ? 'Shri Ram Kumar Singh' : "Patient Demo {$i}",
                    'phone' => $phone,
                    'password' => 'password123',
                    'plain_password' => 'password123',
                    'role' => 'patient',
                    'unique_id' => $uniqueId,
                    'address' => "Demo Patient Address {$i}",
                    'location' => $defaultPoint,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            $patients->push($patient);
        }

        return $patients;
    }

    private function seedStaffReferrals($staffMembers): void
    {
        if ($staffMembers->count() < 2) {
            return;
        }

        foreach ($staffMembers as $idx => $staff) {
            if ($idx === 0 || $idx % 3 === 0) {
                continue;
            }

            $referrer = $staffMembers[($idx - 1 + $staffMembers->count()) % $staffMembers->count()];
            if ((int) $referrer->id === (int) $staff->id) {
                continue;
            }

            \App\Modules\Referrals\Models\Referral::query()->updateOrCreate(
                [
                    'referrer_id' => $referrer->id,
                    'referred_id' => $staff->id,
                ],
                [
                    'referral_code' => 'DEMO-REF-'.$referrer->id,
                    'status' => 'completed',
                    'reward_points' => 0,
                    'reward_amount' => 100.00,
                    'completed_at' => now()->subDays(($idx % 20) + 1),
                    'payment_processed' => false,
                    'payment_processed_at' => null,
                ]
            );
        }
    }

    private function seedPatientRewards($staffMembers): void
    {
        foreach ($staffMembers as $staff) {
            for ($j = 1; $j <= self::REWARDS_PER_STAFF; $j++) {
                CaregiverReward::query()->updateOrCreate(
                    [
                        'user_id' => $staff->id,
                        'patient_phone' => (string) (9600000000 + ($staff->id % 1000) * 10 + $j),
                    ],
                    [
                        'patient_name' => "Reward Patient {$staff->id}-{$j}",
                        'patient_age' => 45 + ($j % 20),
                        'patient_address' => "Reward Patient Address {$staff->id}-{$j}",
                        'patient_pincode' => '4620'.str_pad((string) (($staff->id + $j) % 99), 2, '0', STR_PAD_LEFT),
                        'hospital_name' => "Demo Hospital {$j}",
                        'treatment_details' => 'Demo reward submission',
                        'reward_points' => 1,
                        'reward_amount' => 10.00,
                        'payment_processed' => false,
                        'payment_processed_at' => null,
                    ]
                );
            }

            $points = (int) CaregiverReward::query()
                ->where('user_id', $staff->id)
                ->sum('reward_points');
            $staff->update(['reward_points' => $points]);
        }
    }

    private function seedSubscriptions($staffMembers, $patients, Plan $plan, User $admin): void
    {
        $calculator = app(IncentiveCalculatorService::class);
        $frequencies = ['monthly', 'half_yearly', 'annually', 'full_payment'];
        $paymentOptions = is_array($plan->payment_options) ? $plan->payment_options : [];
        $patientIdx = 0;

        foreach ($staffMembers as $sIdx => $staff) {
            for ($k = 1; $k <= self::SUBSCRIPTIONS_PER_STAFF; $k++) {
                $patient = $patients[$patientIdx % $patients->count()];
                $patientIdx++;

                $frequency = $frequencies[($sIdx + $k) % count($frequencies)];
                $opt = $paymentOptions[$frequency] ?? [];
                $baseAmount = (float) ($opt['price'] ?? $plan->monthly_price ?? 999.00);
                $payableYears = (int) ($opt['payable_years'] ?? 0);
                $careBenefitsYears = (int) ($opt['care_benefits_years'] ?? 0);
                $totalYears = $payableYears + $careBenefitsYears;
                $gstRate = 18.00;
                $gstAmount = round(($baseAmount * $gstRate) / 100, 2);
                $totalAmount = round($baseAmount + $gstAmount, 2);
                $startDate = Carbon::today()->subDays(($k % 10) + 1);
                if ($totalYears <= 0) {
                    $endDate = (clone $startDate)->addMonth();
                } else {
                    $endDate = (clone $startDate)->addYears($totalYears);
                }

                $subscription = Subscription::query()->updateOrCreate(
                    ['notes' => "DEMO_NETWORK_SUB_{$staff->id}_{$k}"],
                    [
                        'user_id' => $patient->id,
                        'plan_id' => $plan->id,
                        'referrer_id' => $staff->id,
                        'payment_frequency' => $frequency,
                        'status' => 'active',
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'care_benefits_years' => $careBenefitsYears,
                        'payable_years' => $payableYears,
                        'base_amount' => $baseAmount,
                        'gst_amount' => $gstAmount,
                        'gst_rate' => $gstRate,
                        'total_amount' => $totalAmount,
                        'paid_amount' => $totalAmount,
                        'payment_status' => 'paid',
                        'payment_verified_by' => $admin->id,
                        'payment_verified_at' => now()->subDays($k),
                        'referral_commission_amount' => 0,
                        'referral_base_amount' => null,
                        'referral_growth_percent' => null,
                        'referral_dta_percent' => null,
                        'referral_payment_processed' => false,
                        'referral_payment_processed_at' => null,
                        'auto_renew' => false,
                        'approved_by' => $admin->id,
                        'approved_at' => now()->subDays($k),
                    ]
                );

                $calculator->createOrUpdateSubscriptionSaleLedger($subscription);
            }
        }
    }

    private function seedServiceRequests($staffMembers, $patients, $serviceTypes, User $admin): void
    {
        $calculator = app(IncentiveCalculatorService::class);
        $patientIdx = 0;

        foreach ($staffMembers as $sIdx => $staff) {
            for ($v = 1; $v <= self::SERVICES_PER_STAFF; $v++) {
                $patient = $patients[$patientIdx % $patients->count()];
                $patientIdx++;
                $serviceType = $serviceTypes[($sIdx + $v) % $serviceTypes->count()];
                $durationDays = (($sIdx + $v) % 3) + 1;
                $startDate = Carbon::today()->subDays(120 - (($sIdx * self::SERVICES_PER_STAFF) + $v));
                $endDate = (clone $startDate)->addDays($durationDays - 1);
                $totalAmount = (float) $serviceType->patient_charge * $durationDays;

                $serviceRequest = ServiceRequest::query()->updateOrCreate(
                    ['notes' => "DEMO_NETWORK_SERVICE_{$staff->id}_{$v}"],
                    [
                        'patient_id' => $patient->id,
                        'service_type_id' => $serviceType->id,
                        'preferred_staff_type' => $staff->role,
                        'preferred_staff_id' => $staff->id,
                        'assigned_staff_id' => $staff->id,
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
                        'approved_by' => $admin->id,
                        'staff_payment_processed' => false,
                        'staff_payment_processed_at' => null,
                        'special_requirements' => 'Demo network seeded request',
                    ]
                );

                $hasActiveSubscription = Subscription::query()
                    ->where('user_id', $patient->id)
                    ->where('status', 'active')
                    ->where('end_date', '>', now())
                    ->exists();

                $calculator->createOrUpdateServiceLedger($staff, $serviceRequest, $hasActiveSubscription);
            }
        }
    }

    private function experienceBandForIndex(int $i): string
    {
        $bands = ['0-2', '3-5', '5-10', '10-15'];

        return $bands[$i % count($bands)];
    }

    private function experienceTierForIndex(int $i): string
    {
        return match ($i % 3) {
            0 => '1y',
            1 => '3y',
            default => '5y_plus',
        };
    }
}
