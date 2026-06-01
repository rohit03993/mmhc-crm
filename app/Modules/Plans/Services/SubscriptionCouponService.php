<?php

namespace App\Modules\Plans\Services;

use App\Models\Core\User;
use App\Modules\Plans\Models\Plan;
use App\Modules\Plans\Models\Subscription;
use App\Modules\Plans\Models\SubscriptionCoupon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubscriptionCouponService
{
    public function __construct(
        private StudentSubscriptionService $studentSubscriptionService
    ) {}

    public function generateCode(int $length = 8): string
    {
        do {
            $code = strtoupper(Str::random($length));
        } while (SubscriptionCoupon::where('code', $code)->exists());

        return $code;
    }

    /**
     * @return array{valid: bool, message: string, coupon?: SubscriptionCoupon, discount?: float, final_amount?: float, original_amount?: float}
     */
    public function validateForCheckout(
        string $code,
        User $user,
        float $originalAmount,
        string $checkoutAudience = 'student'
    ): array {
        $coupon = SubscriptionCoupon::query()
            ->where('code', strtoupper(trim($code)))
            ->first();

        if (! $coupon) {
            return ['valid' => false, 'message' => 'Invalid coupon code.'];
        }

        if (! $coupon->is_active) {
            return ['valid' => false, 'message' => 'This coupon is no longer active.'];
        }

        if ($coupon->valid_from && $coupon->valid_from->isFuture()) {
            return ['valid' => false, 'message' => 'This coupon is not valid yet.'];
        }

        if ($coupon->valid_until && $coupon->valid_until->isPast()) {
            return ['valid' => false, 'message' => 'This coupon has expired.'];
        }

        if ($coupon->max_uses !== null && $coupon->used_count >= $coupon->max_uses) {
            return ['valid' => false, 'message' => 'This coupon has reached its usage limit.'];
        }

        if (! $this->couponMatchesAudience($coupon, $checkoutAudience)) {
            return ['valid' => false, 'message' => 'This coupon cannot be used for this membership.'];
        }

        $discount = $this->calculateDiscount($coupon, $originalAmount);
        if ($discount <= 0) {
            return ['valid' => false, 'message' => 'This coupon does not apply to this amount.'];
        }

        $final = $this->finalAmount($originalAmount, $discount);

        return [
            'valid' => true,
            'message' => 'Coupon applied successfully.',
            'coupon' => $coupon,
            'discount' => $discount,
            'final_amount' => $final,
            'original_amount' => $originalAmount,
        ];
    }

    public function applyToSubscription(Subscription $subscription, SubscriptionCoupon $coupon): Subscription
    {
        if ($subscription->payment_status === 'paid') {
            throw ValidationException::withMessages([
                'coupon_code' => 'Payment is already completed for this subscription.',
            ]);
        }

        $original = (float) ($subscription->amount_before_discount ?? $subscription->total_amount);
        if ($subscription->discount_amount > 0 && $subscription->amount_before_discount) {
            $original = (float) $subscription->amount_before_discount;
        }

        $discount = $this->calculateDiscount($coupon, $original);
        $final = $this->finalAmount($original, $discount);

        $subscription->update([
            'subscription_coupon_id' => $coupon->id,
            'coupon_code' => $coupon->code,
            'amount_before_discount' => $original,
            'discount_amount' => $discount,
            'total_amount' => $final,
            'base_amount' => $final,
            'gst_amount' => 0,
            'razorpay_order_id' => null,
            'gateway_status' => null,
        ]);

        return $subscription->fresh();
    }

    public function removeFromSubscription(Subscription $subscription): Subscription
    {
        if ($subscription->payment_status === 'paid') {
            throw ValidationException::withMessages([
                'coupon_code' => 'Payment is already completed.',
            ]);
        }

        $original = (float) ($subscription->amount_before_discount ?? $subscription->total_amount);

        $subscription->update([
            'subscription_coupon_id' => null,
            'coupon_code' => null,
            'amount_before_discount' => null,
            'discount_amount' => 0,
            'total_amount' => $original,
            'base_amount' => $original,
            'gst_amount' => 0,
            'razorpay_order_id' => null,
            'gateway_status' => null,
        ]);

        return $subscription->fresh();
    }

    public function recordRedemption(Subscription $subscription): void
    {
        if (! $subscription->subscription_coupon_id || $subscription->payment_status !== 'paid') {
            return;
        }

        SubscriptionCoupon::where('id', $subscription->subscription_coupon_id)
            ->increment('used_count');
    }

    public function studentLaunchAmount(?Plan $plan = null): float
    {
        $plan = $plan ?? $this->studentSubscriptionService->getStudentPlan();
        if (! $plan) {
            return (float) config('student_subscription.display.launch_price_inr', 12000);
        }

        $frequency = $this->studentSubscriptionService->paymentFrequency();
        $option = $plan->payment_options[$frequency] ?? null;

        return (float) ($option['price'] ?? $plan->price ?? 12000);
    }

    public function calculateDiscount(SubscriptionCoupon $coupon, float $amount): float
    {
        if ($amount <= 0) {
            return 0.0;
        }

        if ($coupon->discount_type === 'percent') {
            $discount = round($amount * ((float) $coupon->discount_value) / 100, 2);
        } else {
            $discount = (float) $coupon->discount_value;
        }

        return min($discount, $amount);
    }

    public function finalAmount(float $originalAmount, float $discount): float
    {
        return max(1.0, round($originalAmount - $discount, 2));
    }

    protected function couponMatchesAudience(SubscriptionCoupon $coupon, string $checkoutAudience): bool
    {
        if ($coupon->audience === 'all') {
            return true;
        }

        return $coupon->audience === $checkoutAudience;
    }
}
