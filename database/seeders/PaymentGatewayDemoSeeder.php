<?php

namespace Database\Seeders;

use App\Models\Core\User;
use App\Modules\Incentives\Services\IncentiveCalculatorService;
use App\Modules\Payments\Models\StaffPayment;
use App\Modules\Plans\Models\Plan;
use App\Modules\Plans\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PaymentGatewayDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SubscriptionPlansSeeder::class,
            DemoDataSeeder::class,
            IncentiveRuleSetSeeder::class,
        ]);

        $admin = User::query()->where('role', 'admin')->orderBy('id')->first();
        $patient = User::query()->where('role', 'patient')->orderBy('id')->first();
        $staff = User::query()->whereIn('role', ['nurse', 'caregiver'])->orderBy('id')->first();
        $plan = Plan::query()->orderBy('id')->first();

        if (! $admin || ! $patient || ! $staff || ! $plan) {
            $this->command->error('Payment gateway demo seeder requires admin, patient, staff, and plan records.');

            return;
        }

        $staffUpi = $staff->upi_id ?: 'demo.staff@okaxis';
        if ($staff->upi_id !== $staffUpi) {
            $staff->update(['upi_id' => $staffUpi]);
        }

        $subscription = Subscription::query()->updateOrCreate(
            ['notes' => 'DEMO_RAZORPAY_SUBSCRIPTION_SUCCESS'],
            [
                'user_id' => $patient->id,
                'plan_id' => $plan->id,
                'referrer_id' => $staff->id,
                'payment_frequency' => 'monthly',
                'status' => 'active',
                'start_date' => Carbon::today()->subDay(),
                'end_date' => Carbon::today()->addMonth(),
                'care_benefits_years' => 0,
                'payable_years' => 1,
                'base_amount' => 3000,
                'gst_amount' => 540,
                'gst_rate' => 18,
                'total_amount' => 3540,
                'paid_amount' => 3540,
                'payment_status' => 'paid',
                'payment_provider' => 'razorpay',
                'gateway_status' => 'captured',
                'gateway_payload' => [
                    'entity' => 'payment',
                    'status' => 'captured',
                    'method' => 'upi',
                    'source' => 'demo-seeder',
                ],
                'razorpay_order_id' => 'order_demo_razorpay_subscription_001',
                'razorpay_payment_id' => 'pay_demo_razorpay_subscription_001',
                'razorpay_signature' => 'sig_demo_razorpay_subscription_001',
                'razorpay_event_id' => 'evt_demo_razorpay_subscription_001',
                'webhook_received_at' => now()->subMinutes(5),
                'payment_verified_by' => $admin->id,
                'payment_verified_at' => now()->subMinutes(4),
                'referral_commission_amount' => 0,
                'referral_base_amount' => null,
                'referral_growth_percent' => null,
                'referral_dta_percent' => null,
                'referral_payment_processed' => false,
                'referral_payment_processed_at' => null,
                'auto_renew' => false,
                'approved_by' => $admin->id,
                'approved_at' => now()->subMinutes(4),
            ]
        );

        $calculator = app(IncentiveCalculatorService::class);
        $calculator->createOrUpdateSubscriptionSaleLedger($subscription);

        StaffPayment::query()->updateOrCreate(
            ['notes' => 'DEMO_RAZORPAYX_STAFF_PAYOUT_SUCCESS'],
            [
                'staff_id' => $staff->id,
                'admin_id' => $admin->id,
                'payment_type' => 'staff_referral',
                'amount' => 500,
                'payment_provider' => 'razorpayx',
                'payment_mode' => 'razorpayx',
                'gateway_status' => 'processed',
                'gateway_reference_id' => 'pout_demo_razorpayx_staff_001',
                'gateway_payload' => [
                    'entity' => 'payout',
                    'status' => 'processed',
                    'mode' => 'UPI',
                    'source' => 'demo-seeder',
                ],
                'beneficiary_upi' => $staffUpi,
                'transaction_id' => 'utr_demo_razorpayx_staff_001',
                'payment_screenshot' => null,
                'paid_at' => now()->subMinutes(2),
            ]
        );

        $this->command->info('Payment gateway demo flow seeded: Razorpay subscription + RazorpayX staff payout.');
    }
}
