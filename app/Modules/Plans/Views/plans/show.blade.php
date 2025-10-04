@extends('auth::layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">{{ $plan->name }} - Plan Details</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('plans.index') }}">Plans</a></li>
                        <li class="breadcrumb-item active">{{ $plan->name }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Plan Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold">Description</h6>
                            <p>{{ $plan->description }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold">Duration</h6>
                            <p>{{ $plan->duration_text }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Features & Benefits</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($plan->features as $feature)
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                <span>{{ $feature }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 100px;">
                <div class="card-header text-center">
                    <h5 class="card-title mb-0">{{ $plan->name }}</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <h2 class="text-primary mb-0">{{ $plan->formatted_price }}</h2>
                        <small class="text-muted">per {{ $plan->duration_text }}</small>
                    </div>
                    
                    @if($plan->is_popular)
                    <div class="alert alert-primary mb-3">
                        <i class="fas fa-star me-2"></i>
                        <strong>Most Popular Choice!</strong>
                    </div>
                    @endif
                    
                    <div class="d-grid gap-2">
                        <a href="{{ route('plans.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Plans
                        </a>
                        
                        @auth
                        <button class="btn btn-primary btn-lg" onclick="subscribeToPlan({{ $plan->id }})">
                            <i class="fas fa-credit-card me-2"></i>Subscribe Now
                        </button>
                        @else
                        <a href="{{ route('auth.login') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Login to Subscribe
                        </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Subscribe Modal -->
<div class="modal fade" id="subscribeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Subscribe to {{ $plan->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="subscribeForm" method="POST" action="{{ route('subscriptions.subscribe') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                    
                    <div class="alert alert-info">
                        <h6 class="alert-heading">Plan Summary</h6>
                        <p class="mb-2"><strong>Plan:</strong> {{ $plan->name }}</p>
                        <p class="mb-0"><strong>Price:</strong> {{ $plan->formatted_price }} per {{ $plan->duration_text }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Auto Renew</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="auto_renew" value="1" id="auto_renew">
                            <label class="form-check-label" for="auto_renew">
                                Automatically renew this subscription when it expires
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Additional Notes (Optional)</label>
                        <textarea class="form-control" name="notes" id="notes" rows="3" placeholder="Any special requirements or notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Proceed to Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function subscribeToPlan(planId) {
    const modal = new bootstrap.Modal(document.getElementById('subscribeModal'));
    modal.show();
}
</script>
@endsection
