<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('caregiver_rewards')) {
            DB::table('caregiver_rewards')
                ->where('verification_status', 'verified')
                ->where(function ($query) {
                    $query->whereNull('verified_at')
                        ->orWhereNull('verification_otp_sent_at');
                })
                ->update([
                    'verification_status' => 'pending',
                    'verified_at' => null,
                ]);

            if (Schema::getConnection()->getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE caregiver_rewards MODIFY verification_status VARCHAR(20) NOT NULL DEFAULT 'pending'");
            }
        }

        if (Schema::hasTable('referrals')) {
            DB::table('referrals')
                ->where('verification_status', 'verified')
                ->whereNull('verified_at')
                ->update([
                    'verification_status' => 'pending',
                ]);

            if (Schema::getConnection()->getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE referrals MODIFY verification_status VARCHAR(20) NOT NULL DEFAULT 'pending'");
            }
        }
    }

    public function down(): void
    {
        // Irreversible data correction — legacy fake-verified rows cannot be restored safely.
    }
};
