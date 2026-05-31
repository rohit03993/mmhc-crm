<?php

namespace App\Modules\Plans\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Plans\Models\SubscriptionCoupon;
use App\Modules\Plans\Services\SubscriptionCouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminSubscriptionCouponController extends Controller
{
    public function index()
    {
        $coupons = SubscriptionCoupon::query()
            ->with('creator:id,name')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('plans::admin.coupons.index', compact('coupons'));
    }

    public function create(SubscriptionCouponService $couponService)
    {
        return view('plans::admin.coupons.form', [
            'coupon' => new SubscriptionCoupon([
                'audience' => 'student',
                'discount_type' => 'fixed',
                'is_active' => true,
            ]),
            'suggestedCode' => $couponService->generateCode(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['code'] = strtoupper($data['code']);
        $data['created_by'] = Auth::id();

        SubscriptionCoupon::create($data);

        return redirect()->route('admin.subscription-coupons.index')
            ->with('success', 'Coupon '.$data['code'].' created successfully.');
    }

    public function edit(SubscriptionCoupon $coupon)
    {
        return view('plans::admin.coupons.form', [
            'coupon' => $coupon,
            'suggestedCode' => $coupon->code,
        ]);
    }

    public function update(Request $request, SubscriptionCoupon $coupon)
    {
        $data = $this->validated($request, $coupon->id);
        $data['code'] = strtoupper($data['code']);

        $coupon->update($data);

        return redirect()->route('admin.subscription-coupons.index')
            ->with('success', 'Coupon updated successfully.');
    }

    public function destroy(SubscriptionCoupon $coupon)
    {
        if ($coupon->subscriptions()->where('payment_status', 'paid')->exists()) {
            $coupon->update(['is_active' => false]);

            return redirect()->back()
                ->with('info', 'Coupon deactivated (it was already used on paid subscriptions).');
        }

        $coupon->delete();

        return redirect()->route('admin.subscription-coupons.index')
            ->with('success', 'Coupon deleted.');
    }

    protected function validated(Request $request, ?int $exceptId = null): array
    {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:64',
                'alpha_dash',
                Rule::unique('subscription_coupons', 'code')->ignore($exceptId),
            ],
            'description' => 'nullable|string|max:255',
            'audience' => 'required|in:student,patient,all',
            'discount_type' => 'required|in:fixed,percent',
            'discount_value' => 'required|numeric|min:0.01',
            'max_uses' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'nullable|boolean',
        ], [], [
            'code' => 'coupon code',
            'discount_value' => 'discount value',
        ]) + [
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
