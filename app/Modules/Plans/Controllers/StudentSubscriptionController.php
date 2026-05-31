<?php

namespace App\Modules\Plans\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Plans\Services\StudentSubscriptionService;
use App\Modules\Plans\Services\SubscriptionCouponService;
use App\Modules\Plans\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StudentSubscriptionController extends Controller
{
    public function offer(StudentSubscriptionService $studentSubscriptionService)
    {
        $user = Auth::user();
        if ($user->role !== 'student') {
            return redirect()->route('dashboard');
        }

        if (! $user->hasVerifiedPhone()) {
            return redirect()->route('profile.verify-phone');
        }

        if ($studentSubscriptionService->hasActiveStudentMembership($user)) {
            return redirect()->route('academics.dashboard')
                ->with('success', 'Your student membership is active.');
        }

        $pending = $studentSubscriptionService->getPendingStudentSubscription($user);
        $plan = $studentSubscriptionService->getStudentPlan();
        $display = $studentSubscriptionService->offerDisplay();

        return view('plans::student-subscription.offer', compact('user', 'pending', 'plan', 'display'));
    }

    public function subscribe(
        Request $request,
        StudentSubscriptionService $studentSubscriptionService,
        SubscriptionService $subscriptionService,
        SubscriptionCouponService $couponService
    ) {
        $user = Auth::user();
        if ($user->role !== 'student') {
            abort(403);
        }

        if (! $user->hasVerifiedPhone()) {
            return redirect()->route('profile.verify-phone');
        }

        if ($studentSubscriptionService->hasActiveStudentMembership($user)) {
            return redirect()->route('academics.dashboard');
        }

        $pending = $studentSubscriptionService->getPendingStudentSubscription($user);
        if ($pending) {
            return redirect()->route('subscriptions.payment-confirmation', $pending->id)
                ->with('info', 'Complete payment for your pending student membership.');
        }

        $plan = $studentSubscriptionService->getStudentPlan();
        if (! $plan) {
            return redirect()->route('student-subscription.offer')
                ->with('error', 'Student membership is not configured yet. Please contact MMHC support.');
        }

        try {
            $subscription = $subscriptionService->createSubscription($user, $plan, [
                'payment_frequency' => $studentSubscriptionService->paymentFrequency(),
                'notes' => 'Student journey launch membership',
            ]);

            $couponCode = trim((string) $request->input('coupon_code', ''));
            if ($couponCode !== '') {
                $original = (float) $subscription->total_amount;
                $result = $couponService->validateForCheckout($couponCode, $user, $original, 'student');
                if (! $result['valid']) {
                    $subscription->delete();

                    return redirect()->route('student-subscription.offer')
                        ->withInput()
                        ->with('error', $result['message']);
                }
                $couponService->applyToSubscription($subscription, $result['coupon']);
                $subscription->refresh();
            }

            return redirect()->route('subscriptions.payment-confirmation', $subscription->id)
                ->with('success', 'Almost there! Complete your one-time payment of ₹'.number_format((float) $subscription->total_amount, 0).' to activate your membership.');
        } catch (\Throwable $e) {
            Log::error('Student subscription create failed', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'payment_frequency' => $studentSubscriptionService->paymentFrequency(),
                'error' => $e->getMessage(),
            ]);

            $message = 'Could not start subscription. Please try again or contact support.';
            if (config('app.debug')) {
                $message .= ' ('.$e->getMessage().')';
            }

            return redirect()->route('student-subscription.offer')
                ->with('error', $message);
        }
    }

    public function validateCoupon(Request $request, SubscriptionCouponService $couponService, StudentSubscriptionService $studentSubscriptionService)
    {
        $user = Auth::user();
        if ($user->role !== 'student') {
            abort(403);
        }

        $request->validate([
            'coupon_code' => 'required|string|max:64',
        ]);

        $plan = $studentSubscriptionService->getStudentPlan();
        $original = $couponService->studentLaunchAmount($plan);
        $result = $couponService->validateForCheckout(
            $request->input('coupon_code'),
            $user,
            $original,
            'student'
        );

        return response()->json([
            'success' => $result['valid'],
            'message' => $result['message'],
            'original_amount' => $result['original_amount'] ?? $original,
            'discount_amount' => $result['discount'] ?? 0,
            'final_amount' => $result['final_amount'] ?? $original,
            'coupon_code' => $result['valid'] ? $result['coupon']->code : null,
        ], $result['valid'] ? 200 : 422);
    }
}
