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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('payment_screenshot')->nullable()->after('payment_status');
            $table->string('transaction_id')->nullable()->after('payment_screenshot');
            $table->text('payment_notes')->nullable()->after('transaction_id');
            $table->foreignId('payment_verified_by')->nullable()->constrained('users')->onDelete('set null')->after('payment_notes');
            $table->timestamp('payment_verified_at')->nullable()->after('payment_verified_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'payment_screenshot',
                'transaction_id',
                'payment_notes',
                'payment_verified_by',
                'payment_verified_at'
            ]);
        });
    }
};

