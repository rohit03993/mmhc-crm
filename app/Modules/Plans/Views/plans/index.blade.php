@extends('auth::layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Healthcare Plans</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Plans</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @forelse($plans as $plan)
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 plan-card {{ $plan->is_popular ? 'border-primary' : '' }}">
                @if($plan->is_popular)
                <div class="card-header bg-primary text-white text-center">
                    <span class="badge bg-warning text-dark">MOST POPULAR</span>
                </div>
                @endif
                
                <div class="card-body d-flex flex-column">
                    <div class="text-center mb-3">
                        <h5 class="card-title">{{ $plan->name }}</h5>
                        <p class="text-muted small">{{ $plan->description }}</p>
                    </div>
                    
                    <div class="text-center mb-4">
                        <h2 class="text-primary mb-0">{{ $plan->formatted_price }}</h2>
                        <small class="text-muted">per {{ $plan->duration_text }}</small>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="fw-bold">Features:</h6>
                        <ul class="list-unstyled">
                            @foreach($plan->features as $feature)
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                {{ $feature }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    
                    <div class="mt-auto">
                        <a href="{{ route('plans.show', $plan) }}" class="btn btn-outline-primary w-100 mb-2">
                            View Details
                        </a>
                        @auth
                        <button class="btn btn-primary w-100" onclick="subscribeToPlan({{ $plan->id }})">
                            Subscribe Now
                        </button>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="text-muted">No plans available at the moment</h5>
                    <p>Please check back later for available healthcare plans.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>

<!-- Subscribe Modal -->
<div class="modal fade" id="subscribeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Subscribe to Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="subscribeForm" method="POST" action="{{ route('subscriptions.subscribe') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="plan_id" id="plan_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Auto Renew</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="auto_renew" value="1" id="auto_renew">
                            <label class="form-check-label" for="auto_renew">
                                Automatically renew this subscription
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
                    <button type="submit" class="btn btn-primary">Subscribe</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function subscribeToPlan(planId) {
    document.getElementById('plan_id').value = planId;
    const modal = new bootstrap.Modal(document.getElementById('subscribeModal'));
    modal.show();
}
</script>
@endsection
