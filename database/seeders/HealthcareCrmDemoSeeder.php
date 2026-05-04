<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Single entry point for all healthcare / home-care CRM demo data (Indian-context seeders).
 *
 * Order matters: service catalogue → subscription plans → core demo users & requests →
 * incentive rules → large referral/network demo → payment gateway samples.
 *
 * Run alone:
 *   php artisan db:seed --class=HealthcareCrmDemoSeeder
 */
class HealthcareCrmDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ServiceTypesSeeder::class,
            SubscriptionPlansSeeder::class,
            DemoDataSeeder::class,
            IncentiveRuleSetSeeder::class,
            IncentiveNetworkDemoSeeder::class,
            PaymentGatewayDemoSeeder::class,
        ]);

        $this->command?->info('Healthcare CRM demo seeding complete (service types, plans, users, services, incentives, payments).');
    }
}
