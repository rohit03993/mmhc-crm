<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->timestamp('refund_due_at')->nullable()->after('cancellation_reason');
            $table->decimal('refund_amount', 10, 2)->nullable()->after('refund_due_at');
            $table->timestamp('refunded_at')->nullable()->after('refund_amount');
            $table->foreignId('refunded_by')->nullable()->after('refunded_at')->constrained('users')->nullOnDelete();
            $table->string('refund_reference', 191)->nullable()->after('refunded_by');
            $table->text('refund_note')->nullable()->after('refund_reference');
        });

        // Backfill: already cancelled + paid visits that still need a manual refund queue entry.
        DB::table('service_requests')
            ->where('status', 'cancelled')
            ->where('payment_status', 'paid')
            ->where('total_amount', '>', 0)
            ->whereNull('refunded_at')
            ->whereNull('refund_due_at')
            ->update([
                'refund_due_at' => DB::raw('COALESCE(cancelled_at, updated_at, created_at)'),
                'refund_amount' => DB::raw('total_amount'),
            ]);
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('refunded_by');
            $table->dropColumn([
                'refund_due_at',
                'refund_amount',
                'refunded_at',
                'refund_reference',
                'refund_note',
            ]);
        });
    }
};
