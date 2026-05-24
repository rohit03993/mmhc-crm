<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('subscriptions') || ! Schema::hasColumn('subscriptions', 'payment_frequency')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE subscriptions MODIFY payment_frequency ENUM('monthly', 'half_yearly', 'annually', 'full_payment', 'student_launch') NOT NULL DEFAULT 'monthly'");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('subscriptions')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE subscriptions MODIFY payment_frequency ENUM('monthly', 'half_yearly', 'annually', 'full_payment') NOT NULL DEFAULT 'monthly'");
        }
    }
};
