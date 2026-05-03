<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subscriptions') && Schema::hasColumn('subscriptions', 'referral_commission_rate')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropColumn('referral_commission_rate');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('subscriptions') && ! Schema::hasColumn('subscriptions', 'referral_commission_rate')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->decimal('referral_commission_rate', 5, 2)->default(0.00)->after('referral_commission_amount');
            });
        }
    }
};
