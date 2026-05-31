<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('description')->nullable();
            $table->enum('audience', ['student', 'patient', 'all'])->default('student');
            $table->enum('discount_type', ['fixed', 'percent'])->default('fixed');
            $table->decimal('discount_value', 10, 2);
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['audience', 'is_active']);
            $table->index('valid_until');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('subscription_coupon_id')->nullable()->after('plan_id')->constrained('subscription_coupons')->nullOnDelete();
            $table->string('coupon_code', 64)->nullable()->after('subscription_coupon_id');
            $table->decimal('amount_before_discount', 10, 2)->nullable()->after('total_amount');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('amount_before_discount');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subscription_coupon_id');
            $table->dropColumn(['coupon_code', 'amount_before_discount', 'discount_amount']);
        });

        Schema::dropIfExists('subscription_coupons');
    }
};
