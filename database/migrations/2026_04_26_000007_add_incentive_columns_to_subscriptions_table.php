<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('subscriptions')) {
            return;
        }
        Schema::table('subscriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('subscriptions', 'referral_base_amount')) {
                $table->decimal('referral_base_amount', 14, 2)->nullable()->after('referral_commission_amount');
            }
            if (! Schema::hasColumn('subscriptions', 'referral_growth_percent')) {
                $table->decimal('referral_growth_percent', 8, 4)->nullable()->after('referral_base_amount');
            }
            if (! Schema::hasColumn('subscriptions', 'referral_dta_percent')) {
                $table->decimal('referral_dta_percent', 8, 4)->nullable()->after('referral_growth_percent');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('subscriptions')) {
            return;
        }
        Schema::table('subscriptions', function (Blueprint $table) {
            foreach (['referral_dta_percent', 'referral_growth_percent', 'referral_base_amount'] as $col) {
                if (Schema::hasColumn('subscriptions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
