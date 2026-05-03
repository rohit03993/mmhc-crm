<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->string('completion_otp_hash')->nullable()->after('staff_payment_processed_at');
            $table->timestamp('completion_otp_expires_at')->nullable()->after('completion_otp_hash');
            $table->unsignedTinyInteger('completion_otp_attempts')->default(0)->after('completion_otp_expires_at');
            $table->string('completion_otp_channel', 20)->nullable()->after('completion_otp_attempts');
            $table->string('completion_otp_sent_to')->nullable()->after('completion_otp_channel');
            $table->timestamp('completion_otp_sent_at')->nullable()->after('completion_otp_sent_to');
            $table->timestamp('completion_verified_at')->nullable()->after('completion_otp_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn([
                'completion_otp_hash',
                'completion_otp_expires_at',
                'completion_otp_attempts',
                'completion_otp_channel',
                'completion_otp_sent_to',
                'completion_otp_sent_at',
                'completion_verified_at',
            ]);
        });
    }
};
