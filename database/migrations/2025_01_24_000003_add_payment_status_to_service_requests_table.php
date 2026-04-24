<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('service_requests', 'staff_payment_processed')) {
            Schema::table('service_requests', function (Blueprint $table) {
                $table->boolean('staff_payment_processed')->default(false);
            });
        }

        if (!Schema::hasColumn('service_requests', 'staff_payment_processed_at')) {
            Schema::table('service_requests', function (Blueprint $table) {
                $table->timestamp('staff_payment_processed_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('service_requests', 'staff_payment_processed')) {
            Schema::table('service_requests', function (Blueprint $table) {
                $table->dropColumn('staff_payment_processed');
            });
        }

        if (Schema::hasColumn('service_requests', 'staff_payment_processed_at')) {
            Schema::table('service_requests', function (Blueprint $table) {
                $table->dropColumn('staff_payment_processed_at');
            });
        }
    }
};

