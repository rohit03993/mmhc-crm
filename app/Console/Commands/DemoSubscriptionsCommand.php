<?php

namespace App\Console\Commands;

use App\Modules\Incentives\Models\IncentiveLedger;
use App\Modules\Incentives\Services\IncentiveCalculatorService;
use App\Modules\Plans\Models\Payment;
use App\Modules\Plans\Models\Subscription;
use App\Modules\Plans\Support\DemoSubscriptionNotes;
use App\Modules\Plans\Services\SubscriptionService;
use Database\Seeders\IncentiveDemoFlowSeeder;
use Database\Seeders\IncentiveNetworkDemoSeeder;
use Database\Seeders\PaymentGatewayDemoSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DemoSubscriptionsCommand extends Command
{
    protected $signature = 'demo:subscriptions
                            {action=sync : sync (reconcile from catalogue), delete (remove demo rows only), or refresh (delete then reseed demo subscriptions)}
                            {--dry-run : List actions without writing}';

    protected $description = 'Maintain demo-seeded subscriptions only (notes: INCENTIVE_DEMO_*, DEMO_RAZORPAY_*, DEMO_NETWORK_SUB_*). Does not touch real customer subscriptions.';

    public function handle(SubscriptionService $subscriptionService): int
    {
        $action = strtolower((string) $this->argument('action'));
        $dry = (bool) $this->option('dry-run');

        if (! in_array($action, ['sync', 'delete', 'refresh'], true)) {
            $this->error('Action must be sync, delete, or refresh.');

            return 1;
        }

        $count = DemoSubscriptionNotes::subscriptionQuery()->count();
        $this->info("Demo-marked subscriptions in database: {$count}");

        if ($action === 'sync') {
            return $this->runSync($subscriptionService, $dry);
        }

        if ($action === 'delete') {
            return $this->runDelete($dry);
        }

        return $this->runRefresh($subscriptionService, $dry);
    }

    private function runSync(SubscriptionService $subscriptionService, bool $dry): int
    {
        $subs = DemoSubscriptionNotes::subscriptionQuery()->with('plan')->orderBy('id')->get();
        if ($subs->isEmpty()) {
            $this->info('Nothing to sync.');

            return 0;
        }

        $ok = 0;
        foreach ($subs as $sub) {
            if ($dry) {
                $this->line("[dry-run] Would reconcile subscription #{$sub->id} ({$sub->notes})");

                continue;
            }
            try {
                $subscriptionService->reconcileSubscriptionFromPlanCatalogue($sub, true);
                $fresh = $sub->fresh();
                if (class_exists(IncentiveCalculatorService::class) && $fresh->referrer_id) {
                    app(IncentiveCalculatorService::class)->createOrUpdateSubscriptionSaleLedger($fresh);
                }
                $this->info("Reconciled subscription #{$fresh->id}");
                $ok++;
            } catch (\Throwable $e) {
                $this->warn("Skipped #{$sub->id}: {$e->getMessage()}");
            }
        }

        $this->info($dry ? 'Dry run complete.' : "Reconciled {$ok} subscription(s).");

        return 0;
    }

    private function runDelete(bool $dry): int
    {
        $ids = DemoSubscriptionNotes::subscriptionQuery()->pluck('id');
        if ($ids->isEmpty()) {
            $this->info('No demo subscriptions to delete.');

            return 0;
        }

        if ($dry) {
            $this->info('[dry-run] Would delete subscription IDs: '.$ids->implode(', '));

            return 0;
        }

        DB::transaction(function () use ($ids) {
            Payment::query()->whereIn('subscription_id', $ids)->delete();
            IncentiveLedger::query()
                ->where('source_type', IncentiveLedger::SOURCE_SUBSCRIPTION_SALE)
                ->whereIn('source_id', $ids)
                ->delete();
            Subscription::query()->whereIn('id', $ids)->delete();
        });

        $this->info('Deleted '.$ids->count().' demo subscription(s) and related plan payments / incentive ledger rows.');

        return 0;
    }

    private function runRefresh(SubscriptionService $subscriptionService, bool $dry): int
    {
        if ($dry) {
            $this->warn('[dry-run] Would delete demo subscriptions, then run PaymentGatewayDemoSeeder, IncentiveDemoFlowSeeder, IncentiveNetworkDemoSeeder.');

            return 0;
        }

        $this->runDelete(false);

        $this->info('Reseeding demo subscriptions…');
        // Must use db:seed so SeedCommand sets $command on the seeder tree ($this->call(SomeSeeder::class) is for Artisan commands only).
        $seedOpts = ['--force' => true];
        foreach ([
            PaymentGatewayDemoSeeder::class,
            IncentiveDemoFlowSeeder::class,
            IncentiveNetworkDemoSeeder::class,
        ] as $seederClass) {
            $code = $this->call('db:seed', array_merge($seedOpts, ['--class' => $seederClass]));
            if ($code !== 0) {
                $this->error("db:seed failed for {$seederClass} (exit {$code}).");

                return $code;
            }
        }
        $this->info('Refresh complete.');

        return 0;
    }
}
