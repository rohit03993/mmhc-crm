@extends('auth::layout')

@section('title', 'My Service Requests - MMHC CRM')

@section('head')
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    @include('services::partials.mobile-assets')
@endsection

@section('content')
<div class="mobile-app-container mmhc-page-requests">
    <!-- App Header (Mobile Only) -->
    <div class="app-header-mobile d-md-none">
        <div class="app-header-content">
            <div class="app-header-left">
                <a href="{{ route('dashboard') }}" class="app-back-btn">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <div class="app-header-title">My Requests</div>
                    <div class="app-header-subtitle">{{ $serviceRequests->total() }} requests</div>
                </div>
            </div>
            <div class="app-header-right">
                <a href="{{ route('staff.index') }}" class="app-header-icon" title="Book a visit">
                    <i class="fas fa-plus"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="app-content">
        <!-- Desktop Header -->
        <div class="d-none d-md-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1" style="font-size: 1.5rem; font-weight: 700; color: #2c3e50;">My Service Requests</h2>
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Track your healthcare service requests</p>
            </div>
            <a href="{{ route('staff.index') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-user-nurse me-1"></i>Book a visit
            </a>
        </div>

        @include('services::partials.booking-flow-steps', ['currentStep' => 'requests'])

        @if($serviceRequests->count() > 0)
            <!-- Mobile-First Card Grid - Max 10 per page -->
            <div class="app-requests-list">
                @foreach($serviceRequests as $request)
                <div class="service-request-card-mobile-full">
                    <!-- Card Header with Status -->
                    <div class="card-header-mobile-full status-{{ $request->status }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <div class="service-status-icon-full">
                                    <i class="fas fa-{{ $request->status === 'pending' ? 'clock' : ($request->status === 'assigned' ? 'user-check' : ($request->status === 'in_progress' ? 'play-circle' : 'check-circle')) }}"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-white">{{ $request->serviceType->name }}</h6>
                                    <small class="text-white opacity-75">{{ $request->created_at->format('M d, Y') }}</small>
                                </div>
                            </div>
                            <span class="badge badge-status-full badge-status-{{ $request->status }}">
                                {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Card Body -->
                    <div class="card-body-mobile-full">
                        <div class="request-details-grid">
                            <div class="detail-grid-item">
                                <div class="detail-label">
                                    <i class="fas fa-calendar-alt"></i> Duration
                                </div>
                                <div class="detail-value">{{ $request->duration_days }} days</div>
                            </div>
                            
                            <div class="detail-grid-item">
                                <div class="detail-label">
                                    <i class="fas fa-rupee-sign"></i> Charge
                                </div>
                                <div class="detail-value fw-bold">
                                    @if($request->isCoveredBySubscription())
                                        <span class="text-success">FREE</span>
                                    @else
                                        â‚¹{{ number_format($request->total_amount, 0) }}
                                    @endif
                                </div>
                            </div>
                            @if((float) $request->total_amount > 0)
                            <div class="detail-grid-item">
                                <div class="detail-label">
                                    <i class="fas fa-wallet"></i> Collected
                                </div>
                                <div class="detail-value text-success">â‚¹{{ number_format($request->prepaid_amount, 0) }}</div>
                            </div>
                            <div class="detail-grid-item">
                                <div class="detail-label">
                                    <i class="fas fa-exclamation-circle"></i> Due
                                </div>
                                <div class="detail-value {{ $request->balanceDue() > 0 ? 'text-danger fw-bold' : 'text-success' }}">
                                    â‚¹{{ number_format($request->balanceDue(), 0) }}
                                </div>
                            </div>
                            <div class="detail-grid-item full-width">
                                <span class="badge bg-{{ $request->paymentStatusBadgeClass() }}">
                                    {{ $request->paymentStatusLabel() }}
                                </span>
                            </div>
                            @endif
                            
                            <div class="detail-grid-item full-width">
                                <div class="detail-label">
                                    <i class="fas fa-calendar-check"></i> Service Period
                                </div>
                                <div class="detail-value">
                                    {{ $request->start_date->format('M d, Y') }} - {{ $request->end_date->format('M d, Y') }}
                                </div>
                            </div>
                            
                            <div class="detail-grid-item full-width">
                                <div class="detail-label">
                                    <i class="fas fa-map-marker-alt"></i> Location
                                </div>
                                <div class="detail-value">{{ Str::limit($request->location, 40) }}</div>
                            </div>
                            
                            @if($request->assignedStaff)
                            <div class="detail-grid-item full-width">
                                <div class="detail-label">
                                    <i class="fas fa-user-{{ $request->assignedStaff->isNurse() ? 'nurse' : 'md' }}"></i> Assigned Staff
                                </div>
                                <div class="detail-value">
                                    {{ $request->assignedStaff->name }}
                                    <span class="badge bg-{{ $request->assignedStaff->isNurse() ? 'info' : 'success' }} ms-1" style="font-size: 0.7rem;">
                                        {{ ucfirst($request->assignedStaff->role) }}
                                    </span>
                                </div>
                            </div>
                            @endif
                            
                            @if($request->notes)
                            <div class="detail-grid-item full-width">
                                <div class="detail-label">
                                    <i class="fas fa-sticky-note"></i> Notes
                                </div>
                                <div class="detail-value">{{ Str::limit($request->notes, 60) }}</div>
                            </div>
                            @endif
                        </div>
                        
                        <!-- Card Footer Actions -->
                        <div class="card-footer-mobile-full">
                            <div class="d-flex gap-2">
                                <a href="{{ route('services.show', $request) }}" class="btn btn-primary btn-sm flex-fill">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </a>
                                @if($request->status === 'pending')
                                <button class="btn btn-outline-warning btn-sm" onclick="cancelRequest({{ $request->id }})">
                                    <i class="fas fa-times"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Mobile-Friendly Pagination -->
            <div class="app-pagination">
                {{ $serviceRequests->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="app-empty-state">
                <div class="app-empty-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h3 class="app-empty-title">No Service Requests Yet</h3>
                <p class="app-empty-text">You haven't made any service requests yet. Start by requesting healthcare services.</p>
                <a href="{{ route('staff.index') }}" class="app-btn-primary">
                    <i class="fas fa-user-nurse me-2"></i>Find staff &amp; book
                </a>
            </div>
        @endif
    </div>

    <!-- Bottom Navigation -->
</div>
<script>
function cancelRequest(requestId) {
    if (confirm('Are you sure you want to cancel this service request?')) {
        // Here you would typically make an AJAX request to cancel the request
        alert('Request cancellation feature will be implemented soon.');
    }
}
</script>
@endsection
