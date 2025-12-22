@extends('auth::layout')

@section('content')
<div class="container-fluid px-3 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title">Manage Subscription Plans</h4>
        <a href="{{ route('admin.plans.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Create New Plan
        </a>
    </div>

    <!-- Plans List -->
    <div class="plans-list">
        @forelse($plans as $plan)
        <div class="plan-card">
            <div class="plan-card-header">
                <div>
                    <h5 class="plan-name">{{ $plan->name }}</h5>
                    <p class="plan-description mb-0">{{ Str::limit($plan->description, 100) }}</p>
                </div>
                <div class="plan-badges">
                    @if($plan->is_active)
                    <span class="badge badge-success">Active</span>
                    @else
                    <span class="badge badge-secondary">Inactive</span>
                    @endif
                    @if($plan->is_popular)
                    <span class="badge badge-warning">Popular</span>
                    @endif
                </div>
            </div>

            <div class="plan-card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <small class="text-muted d-block">Base Price</small>
                        <strong>₹{{ number_format($plan->price, 0) }}</strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <small class="text-muted d-block">Monthly Price</small>
                        <strong>₹{{ number_format($plan->monthly_price ?? $plan->price, 0) }}</strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <small class="text-muted d-block">Members</small>
                        <strong>{{ $plan->members_included ?? 1 }}</strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <small class="text-muted d-block">Subscriptions</small>
                        <strong>{{ $plan->subscriptions->count() }}</strong>
                    </div>
                </div>

                @if($plan->payment_options)
                <div class="payment-options-preview mt-3">
                    <small class="text-muted d-block mb-2">Payment Options:</small>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($plan->payment_options as $frequency => $option)
                        <span class="badge badge-info">
                            {{ ucfirst(str_replace('_', ' ', $frequency)) }}: ₹{{ number_format($option['price'] ?? 0, 0) }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <div class="plan-card-footer">
                <a href="{{ route('admin.plans.edit', $plan) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-edit me-1"></i>Edit Plan
                </a>
                <form action="{{ route('admin.plans.destroy', $plan) }}" 
                      method="POST" 
                      class="d-inline"
                      onsubmit="return confirm('Are you sure you want to delete this plan? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash me-1"></i>Delete
                    </button>
                </form>
                <small class="text-muted ms-3">
                    Sort Order: {{ $plan->sort_order }}
                </small>
            </div>
        </div>
        @empty
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No plans found. 
            <a href="{{ route('admin.plans.create') }}">Create your first plan</a>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($plans->hasPages())
    <div class="mt-4">
        {{ $plans->links() }}
    </div>
    @endif
</div>

<style>
.plan-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 20px;
    margin-bottom: 16px;
}

.plan-card-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    padding-bottom: 16px;
    border-bottom: 1px solid #e9ecef;
    margin-bottom: 16px;
}

.plan-name {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 4px;
}

.plan-description {
    color: #6c757d;
    font-size: 14px;
}

.plan-badges {
    display: flex;
    gap: 8px;
}

.payment-options-preview {
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
}

.plan-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 16px;
    border-top: 1px solid #e9ecef;
    margin-top: 16px;
}
</style>
@endsection

