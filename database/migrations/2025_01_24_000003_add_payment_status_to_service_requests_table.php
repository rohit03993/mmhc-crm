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
        Schema::table('service_requests', function (Blueprint $table) {
            $table->boolean('staff_payment_processed')->default(false)->after('payment_processed_at');
            $table->timestamp('staff_payment_processed_at')->nullable()->after('staff_payment_processed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn(['staff_payment_processed', 'staff_payment_processed_at']);
        });
    }
};

