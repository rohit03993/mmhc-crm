<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            if (! Schema::hasColumn('referrals', 'referrer_name_snapshot')) {
                $table->string('referrer_name_snapshot')->nullable()->after('referrer_id');
                $table->string('referrer_unique_id_snapshot', 32)->nullable()->after('referrer_name_snapshot');
                $table->string('referred_name_snapshot')->nullable()->after('referred_id');
                $table->string('referred_unique_id_snapshot', 32)->nullable()->after('referred_name_snapshot');
            }
        });

        if (Schema::hasTable('subscriptions') && ! Schema::hasColumn('subscriptions', 'subscriber_name_snapshot')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->string('subscriber_name_snapshot')->nullable()->after('user_id');
                $table->string('subscriber_unique_id_snapshot', 32)->nullable()->after('subscriber_name_snapshot');
            });
        }
    }

    public function down(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            foreach ([
                'referrer_name_snapshot',
                'referrer_unique_id_snapshot',
                'referred_name_snapshot',
                'referred_unique_id_snapshot',
            ] as $col) {
                if (Schema::hasColumn('referrals', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        if (Schema::hasTable('subscriptions')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                foreach (['subscriber_name_snapshot', 'subscriber_unique_id_snapshot'] as $col) {
                    if (Schema::hasColumn('subscriptions', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
