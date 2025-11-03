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
            // Admin approval tracking
            $table->timestamp('admin_approved_at')->nullable()->after('completed_at');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')->after('admin_approved_at');
            $table->timestamp('payment_processed_at')->nullable()->after('admin_approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['admin_approved_at', 'approved_by', 'payment_processed_at']);
        });
    }
};
