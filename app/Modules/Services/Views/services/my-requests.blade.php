@extends('auth::layout')

@section('title', 'My Service Requests - MMHC CRM')

@section('head')
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-primary">My Service Requests</h2>
                    <p class="text-muted">Track your healthcare service requests</p>
                </div>
                <a href="{{ route('services.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>New Request
                </a>
            </div>

            @if($serviceRequests->count() > 0)
                <div class="row">
                    @foreach($serviceRequests as $request)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 request-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="card-title">{{ $request->serviceType->name }}</h5>
                                        <span class="badge 
                                            @if($request->status === 'pending') bg-warning
                                            @elseif($request->status === 'assigned') bg-info
                                            @elseif($request->status === 'in_progress') bg-primary
                                            @elseif($request->status === 'completed') bg-success
                                            @else bg-secondary
                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                        </span>
                                    </div>
                                    <div class="text-end">
                                        <div class="text-muted small">{{ $request->created_at->format('M d, Y') }}</div>
                                    </div>
                                </div>
                                
                                <div class="request-details mb-3">
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">Duration</small>
                                            <div class="fw-bold">{{ $request->duration_days }} days</div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Total Amount</small>
                                            <div class="fw-bold text-success">₹{{ number_format($request->total_amount) }}</div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <small class="text-muted">Service Period</small>
                                        <div class="fw-bold">
                                            {{ $request->start_date->format('M d, Y') }} - {{ $request->end_date->format('M d, Y') }}
                                        </div>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <small class="text-muted">Location</small>
                                        <div class="fw-bold">{{ Str::limit($request->location, 30) }}</div>
                                    </div>
                                    
                                    @if($request->assignedStaff)
                                    <div class="mb-2">
                                        <small class="text-muted">Assigned Staff</small>
                                        <div class="fw-bold">
                                            <i class="fas fa-user-{{ $request->assignedStaff->isNurse() ? 'nurse' : 'md' }} me-1"></i>
                                            {{ $request->assignedStaff->name }}
                                            <span class="badge bg-{{ $request->assignedStaff->isNurse() ? 'info' : 'success' }} ms-1">
                                                {{ ucfirst($request->assignedStaff->role) }}
                                            </span>
                                        </div>
                                    </div>
                                    @endif
                                    
                                    @if($request->notes)
                                    <div class="mb-2">
                                        <small class="text-muted">Notes</small>
                                        <div class="fw-bold">{{ Str::limit($request->notes, 50) }}</div>
                                    </div>
                                    @endif
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <a href="{{ route('services.show', $request) }}" class="btn btn-outline-primary btn-sm flex-fill">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </a>
                                    @if($request->status === 'pending')
                                    <button class="btn btn-outline-warning btn-sm" onclick="cancelRequest({{ $request->id }})">
                                        <i class="fas fa-times me-1"></i>Cancel
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $serviceRequests->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-clipboard-list fa-4x text-muted"></i>
                    </div>
                    <h4 class="text-muted">No Service Requests Yet</h4>
                    <p class="text-muted mb-4">You haven't made any service requests yet. Start by requesting healthcare services.</p>
                    <a href="{{ route('services.create') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus me-2"></i>Request Your First Service
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.request-card {
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.request-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.request-details {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.5rem;
}

@media (max-width: 768px) {
    .request-card {
        margin-bottom: 1rem;
    }
}
</style>

<script>
function cancelRequest(requestId) {
    if (confirm('Are you sure you want to cancel this service request?')) {
        // Here you would typically make an AJAX request to cancel the request
        alert('Request cancellation feature will be implemented soon.');
    }
}
</script>
@endsection
