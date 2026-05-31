@extends('auth::layout')

@section('title', 'Subscription coupons')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h4 mb-1"><i class="fas fa-ticket-alt me-2 text-primary"></i>Subscription coupons</h2>
            <p class="text-muted small mb-0">Create discount codes for student membership and patient plans.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.subscription-settings') }}" class="btn btn-outline-secondary btn-sm">Subscription settings</a>
            <a href="{{ route('admin.subscription-coupons.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Create coupon
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Audience</th>
                            <th>Discount</th>
                            <th>Usage</th>
                            <th>Valid until</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($coupons as $coupon)
                        <tr>
                            <td>
                                <code class="fs-6">{{ $coupon->code }}</code>
                                @if($coupon->description)
                                    <div class="small text-muted">{{ $coupon->description }}</div>
                                @endif
                            </td>
                            <td><span class="badge bg-secondary">{{ $coupon->audienceLabel() }}</span></td>
                            <td>{{ $coupon->discountLabel() }}</td>
                            <td>
                                {{ $coupon->used_count }}
                                @if($coupon->max_uses)
                                    / {{ $coupon->max_uses }}
                                @else
                                    <span class="text-muted">/ ∞</span>
                                @endif
                            </td>
                            <td>
                                @if($coupon->valid_until)
                                    {{ $coupon->valid_until->format('d M Y') }}
                                @else
                                    <span class="text-muted">No expiry</span>
                                @endif
                            </td>
                            <td>
                                @if($coupon->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.subscription-coupons.edit', $coupon) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('admin.subscription-coupons.destroy', $coupon) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete or deactivate this coupon?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No coupons yet. Create one for student launch offers.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($coupons->hasPages())
        <div class="card-footer">{{ $coupons->links() }}</div>
        @endif
    </div>
</div>
@endsection
