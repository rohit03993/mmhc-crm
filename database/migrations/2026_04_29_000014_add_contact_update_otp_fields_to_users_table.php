<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pending_email')->nullable()->after('email');
            $table->string('pending_phone')->nullable()->after('phone');
            $table->string('contact_update_channel', 16)->nullable()->after('pending_phone');
            $table->text('contact_update_otp_hash')->nullable()->after('contact_update_channel');
            $table->timestamp('contact_update_otp_expires_at')->nullable()->after('contact_update_otp_hash');
            $table->unsignedTinyInteger('contact_update_otp_attempts')->default(0)->after('contact_update_otp_expires_at');
            $table->string('contact_update_otp_sent_to')->nullable()->after('contact_update_otp_attempts');
            $table->timestamp('contact_update_otp_sent_at')->nullable()->after('contact_update_otp_sent_to');
            $table->timestamp('contact_update_verified_at')->nullable()->after('contact_update_otp_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'pending_email',
                'pending_phone',
                'contact_update_channel',
                'contact_update_otp_hash',
                'contact_update_otp_expires_at',
                'contact_update_otp_attempts',
                'contact_update_otp_sent_to',
                'contact_update_otp_sent_at',
                'contact_update_verified_at',
            ]);
        });
    }
};
