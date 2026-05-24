@extends('auth::layout')

@section('title', 'My Service Requests - MMHC CRM')

@section('head')
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
@endsection

@section('content')
<!-- Mobile App View for My Requests -->
<div class="mobile-app-container">
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
                <a href="{{ route('services.create') }}" class="app-header-icon">
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
            <a href="{{ route('services.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>New Request
            </a>
        </div>

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
                                    <i class="fas fa-rupee-sign"></i> Amount
                                </div>
                                <div class="detail-value text-success fw-bold">₹{{ number_format($request->total_amount) }}</div>
                            </div>
                            
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
                <a href="{{ route('services.create') }}" class="app-btn-primary">
                    <i class="fas fa-plus me-2"></i>Request Your First Service
                </a>
            </div>
        @endif
    </div>

    <!-- Bottom Navigation -->
</div>

<style>
/* Mobile App Container */
.mobile-app-container {
    position: relative;
    min-height: 100vh;
    background: #f5f7fa;
    padding-bottom: 80px !important;
    margin-top: 0;
}

/* App Header Styles */
.app-header-mobile {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 12px 16px;
    padding-top: max(12px, env(safe-area-inset-top));
}

.app-header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.app-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.app-back-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    font-size: 1.1rem;
}

.app-header-title {
    font-size: 1.1rem;
    font-weight: 700;
    line-height: 1.2;
}

.app-header-subtitle {
    font-size: 0.8rem;
    opacity: 0.9;
}

.app-header-right {
    display: flex;
    gap: 12px;
}

.app-header-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    font-size: 1.2rem;
}

/* App Content */
.app-content {
    padding: 16px;
    padding-bottom: 90px !important;
    margin-top: 0;
}

/* Mobile-First Service Request Card */
.service-request-card-mobile-full {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid #e9ecef;
    overflow: hidden;
    margin-bottom: 12px;
    display: flex;
    flex-direction: column;
}

.service-request-card-mobile-full:active {
    transform: scale(0.98);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.card-header-mobile-full {
    padding: 1rem;
    color: white;
    font-weight: 600;
}

.card-header-mobile-full.status-pending {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.card-header-mobile-full.status-assigned {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.card-header-mobile-full.status-in_progress {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.card-header-mobile-full.status-completed {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.service-status-icon-full {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.badge-status-full {
    padding: 0.35rem 0.75rem;
    font-size: 0.75rem;
    border-radius: 12px;
    font-weight: 600;
    background: rgba(255,255,255,0.25);
    border: 1px solid rgba(255,255,255,0.3);
}

.card-body-mobile-full {
    padding: 1rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.request-details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.detail-grid-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.detail-grid-item.full-width {
    grid-column: 1 / -1;
}

.detail-label {
    font-size: 0.75rem;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.detail-label i {
    font-size: 0.7rem;
    color: #667eea;
}

.detail-value {
    font-size: 0.9rem;
    color: #2c3e50;
    font-weight: 600;
}

.card-footer-mobile-full {
    margin-top: auto;
    padding-top: 0.75rem;
    border-top: 1px solid #e9ecef;
}

/* Requests List */
.app-requests-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* Empty State */
.app-empty-state {
    text-align: center;
    padding: 48px 24px;
    background: white;
    border-radius: 16px;
}

.app-empty-icon {
    font-size: 3rem;
    color: #dee2e6;
    margin-bottom: 16px;
}

.app-empty-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
}

.app-empty-text {
    font-size: 0.9rem;
    color: #6c757d;
    margin: 0 0 20px 0;
}

.app-btn-primary {
    display: inline-flex;
    align-items: center;
    padding: 12px 24px;
    border-radius: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
}

.app-pagination {
    margin-top: 16px;
    display: flex;
    justify-content: center;
}

/* Desktop View */
@media (min-width: 768px) {
    .mobile-app-container {
        padding-bottom: 0;
    }
    
    .app-content {
        padding: 24px;
        padding-bottom: 24px;
    }
    
    .app-requests-list {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
}

/* Mobile Optimizations */
@media (max-width: 576px) {
    .card-header-mobile-full h6 {
        font-size: 0.9rem;
    }
    
    .detail-value {
        font-size: 0.85rem;
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
