<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        if ($driver === 'mysql') {
            // For MySQL: Drop unique constraint on referral_code
            DB::statement('ALTER TABLE referrals DROP INDEX referrals_referral_code_unique');
            
            // Add unique constraint on referrer_id + referred_id
            DB::statement('ALTER TABLE referrals ADD UNIQUE KEY referrer_referred_unique (referrer_id, referred_id)');
        } elseif ($driver === 'sqlite') {
            // For SQLite: Need to recreate the table
            // This is more complex, so we'll just modify the index
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS referrer_referred_unique ON referrals (referrer_id, referred_id)');
            // Drop the unique index on referral_code if it exists
            DB::statement('DROP INDEX IF EXISTS referrals_referral_code_unique');
        } else {
            // For other databases, use schema builder
            Schema::table('referrals', function (Blueprint $table) {
                // Drop unique constraint on referral_code
                $table->dropUnique(['referral_code']);
                
                // Add unique constraint on referrer_id + referred_id
                $table->unique(['referrer_id', 'referred_id'], 'referrer_referred_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        if ($driver === 'mysql') {
            // Drop the referrer_referred_unique constraint
            DB::statement('ALTER TABLE referrals DROP INDEX referrer_referred_unique');
            
            // Add back unique constraint on referral_code
            DB::statement('ALTER TABLE referrals ADD UNIQUE KEY referrals_referral_code_unique (referral_code)');
        } elseif ($driver === 'sqlite') {
            // Drop the unique index
            DB::statement('DROP INDEX IF EXISTS referrer_referred_unique');
            // Recreate unique index on referral_code
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS referrals_referral_code_unique ON referrals (referral_code)');
        } else {
            Schema::table('referrals', function (Blueprint $table) {
                $table->dropUnique('referrer_referred_unique');
                $table->unique('referral_code');
            });
        }
    }
};

