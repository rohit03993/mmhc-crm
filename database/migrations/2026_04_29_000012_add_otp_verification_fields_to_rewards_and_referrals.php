<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caregiver_rewards', function (Blueprint $table) {
            $table->string('verification_status', 20)->default('verified')->after('reward_amount');
            $table->string('verification_otp_hash')->nullable()->after('verification_status');
            $table->timestamp('verification_otp_expires_at')->nullable()->after('verification_otp_hash');
            $table->unsignedTinyInteger('verification_otp_attempts')->default(0)->after('verification_otp_expires_at');
            $table->timestamp('verification_otp_sent_at')->nullable()->after('verification_otp_attempts');
            $table->string('verification_otp_sent_to')->nullable()->after('verification_otp_sent_at');
            $table->timestamp('verified_at')->nullable()->after('verification_otp_sent_to');
            $table->index('verification_status');
        });

        Schema::table('referrals', function (Blueprint $table) {
            $table->string('verification_status', 20)->default('verified')->after('status');
            $table->string('verification_otp_hash')->nullable()->after('verification_status');
            $table->timestamp('verification_otp_expires_at')->nullable()->after('verification_otp_hash');
            $table->unsignedTinyInteger('verification_otp_attempts')->default(0)->after('verification_otp_expires_at');
            $table->timestamp('verification_otp_sent_at')->nullable()->after('verification_otp_attempts');
            $table->string('verification_otp_sent_to')->nullable()->after('verification_otp_sent_at');
            $table->timestamp('verified_at')->nullable()->after('verification_otp_sent_to');
            $table->index('verification_status');
        });
    }

    public function down(): void
    {
        Schema::table('caregiver_rewards', function (Blueprint $table) {
            $table->dropIndex(['verification_status']);
            $table->dropColumn([
                'verification_status',
                'verification_otp_hash',
                'verification_otp_expires_at',
                'verification_otp_attempts',
                'verification_otp_sent_at',
                'verification_otp_sent_to',
                'verified_at',
            ]);
        });

        Schema::table('referrals', function (Blueprint $table) {
            $table->dropIndex(['verification_status']);
            $table->dropColumn([
                'verification_status',
                'verification_otp_hash',
                'verification_otp_expires_at',
                'verification_otp_attempts',
                'verification_otp_sent_at',
                'verification_otp_sent_to',
                'verified_at',
            ]);
        });
    }
};
