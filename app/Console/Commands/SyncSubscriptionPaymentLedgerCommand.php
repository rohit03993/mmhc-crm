<?php

namespace App\Console\Commands;

use App\Modules\Plans\Models\Subscription;
use App\Modules\Plans\Services\SubscriptionInvoiceService;
use App\Modules\Plans\Services\SubscriptionPaymentHistoryService;
use App\Modules\Plans\Support\DemoSubscriptionNotes;
use Illuminate\Console\Command;

/**
 * Backfill completed payment (invoice) rows for subscriptions that were marked paid with real payment proof.
 */
class SyncSubscriptionPaymentLedgerCommand extends Command
{
    protected $signature = 'subscriptions:sync-payment-ledger
                            {--dry-run : Show what would be created without writing}
                            {--include-demo : Also process demo-marked subscriptions (not recommended on production)}';

    protected $description = 'Create missing invoice ledger rows for paid subscriptions that have Razorpay/transaction/proof (excludes demo by default)';

    public function handle(SubscriptionInvoiceService $invoiceService): int
    {
        $dry = (bool) $this->option('dry-run');
        $includeDemo = (bool) $this->option('include-demo');

        $query = Subscription::query()
            ->where('payment_status', 'paid')
            ->whereDoesntHave('payments', fn ($q) => $q->where('status', 'completed'))
            ->where(function ($q) {
                $q->whereNotNull('razorpay_payment_id')
                    ->orWhereNotNull('transaction_id')
                    ->orWhereNotNull('payment_screenshot');
            });

        $subscriptions = $query->with('plan')->orderBy('id')->get();

        if (! $includeDemo) {
            $subscriptions = $subscriptions->reject(
                fn (Subscription $s) => DemoSubscriptionNotes::isDemo($s->notes)
            )->values();
        }

        if ($subscriptions->isEmpty()) {
            $this->info('No paid subscriptions need ledger backfill.');

            return 0;
        }

        $this->info(($dry ? '[dry-run] Would process' : 'Processing').' '.$subscriptions->count().' subscription(s).');

        $created = 0;
        foreach ($subscriptions as $subscription) {
            $amount = $subscription->paid_amount > 0 ? $subscription->paid_amount : $subscription->total_amount;
            $this->line("  #{$subscription->id} user {$subscription->user_id} ₹{$amount}");

            if (! $dry) {
                $invoiceService->ensurePaymentRecord($subscription);
                $created++;
            }
        }

        if (! $dry && $created > 0) {
            SubscriptionPaymentHistoryService::bustAdminDashboardCache();
            $this->info("Created or updated {$created} ledger row(s). Run: php artisan cache:clear");
        }

        return 0;
    }
}
