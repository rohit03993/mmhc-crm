@extends('auth::layout')

@section('title', ($coupon->exists ? 'Edit' : 'Create').' coupon')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="mb-4">
        <a href="{{ route('admin.subscription-coupons.index') }}" class="btn btn-link text-decoration-none ps-0">
            <i class="fas fa-arrow-left me-1"></i>Back to coupons
        </a>
        <h2 class="h4 mb-0 mt-2">{{ $coupon->exists ? 'Edit coupon' : 'Create coupon' }}</h2>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ $coupon->exists ? route('admin.subscription-coupons.update', $coupon) : route('admin.subscription-coupons.store') }}">
                        @csrf
                        @if($coupon->exists)
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Coupon code</label>
                            <div class="input-group">
                                <input type="text" name="code" class="form-control text-uppercase @error('code') is-invalid @enderror"
                                       value="{{ old('code', $coupon->code ?: $suggestedCode) }}" required maxlength="64"
                                       pattern="[A-Za-z0-9_-]+" title="Letters, numbers, dash, underscore">
                                <button type="button" class="btn btn-outline-secondary" onclick="document.querySelector('[name=code]').value='{{ $suggestedCode }}'">Generate</button>
                            </div>
                            @error('code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description (optional)</label>
                            <input type="text" name="description" class="form-control" maxlength="255"
                                   value="{{ old('description', $coupon->description) }}" placeholder="e.g. Nursing college batch 2026">
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Audience</label>
                                <select name="audience" class="form-select" required>
                                    @foreach(['student' => 'Students only', 'patient' => 'Patients only', 'all' => 'All'] as $val => $label)
                                        <option value="{{ $val }}" @selected(old('audience', $coupon->audience) === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Discount type</label>
                                <select name="discount_type" class="form-select" id="discountType" required>
                                    <option value="fixed" @selected(old('discount_type', $coupon->discount_type) === 'fixed')>Fixed amount (₹)</option>
                                    <option value="percent" @selected(old('discount_type', $coupon->discount_type) === 'percent')>Percentage (%)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Discount value</label>
                            <input type="number" step="0.01" min="0.01" name="discount_value" class="form-control @error('discount_value') is-invalid @enderror"
                                   value="{{ old('discount_value', $coupon->discount_value ?: 100) }}" required>
                            <small class="text-muted" id="discountHint">Fixed ₹ off the membership total (e.g. 200 → ₹1,200 becomes ₹1,000).</small>
                            @error('discount_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Max uses</label>
                                <input type="number" min="1" name="max_uses" class="form-control" value="{{ old('max_uses', $coupon->max_uses) }}" placeholder="Unlimited">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Valid from</label>
                                <input type="datetime-local" name="valid_from" class="form-control"
                                       value="{{ old('valid_from', optional($coupon->valid_from)->format('Y-m-d\TH:i')) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Valid until</label>
                                <input type="datetime-local" name="valid_until" class="form-control"
                                       value="{{ old('valid_until', optional($coupon->valid_until)->format('Y-m-d\TH:i')) }}">
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive"
                                   @checked(old('is_active', $coupon->is_active ?? true))>
                            <label class="form-check-label" for="isActive">Active (students can use this code)</label>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>{{ $coupon->exists ? 'Update coupon' : 'Create coupon' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('discountType')?.addEventListener('change', function () {
    const hint = document.getElementById('discountHint');
    if (!hint) return;
    hint.textContent = this.value === 'percent'
        ? 'Percent off the total (e.g. 10 → 10% off ₹1,200 = ₹1,080).'
        : 'Fixed ₹ off the membership total (e.g. 200 → ₹1,200 becomes ₹1,000).';
});
</script>
@endsection
