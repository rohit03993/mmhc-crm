@extends('auth::layout')

@section('title', 'Dashboard - MMHC CRM')

@section('head')
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard-patient.css') }}?v=1">
    
    <style>
        :root {
            --patient-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --info-gradient: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
    </style>
@endsection

@section('content')
<!-- Mobile App View -->
<div class="mobile-app-container hc-mobile-shell" data-mmhc-ptr>
    <!-- App Header (Mobile Only) -->
    <div class="app-header-mobile d-md-none">
        <div class="app-header-content">
            <div class="app-header-left">
                <div class="app-user-avatar">
                    <i class="fas fa-user-injured"></i>
                            </div>
                <div class="app-user-info">
                    <div class="app-user-name">{{ Str::limit($user->name, 15) }}</div>
                    <div class="app-user-id">{{ $user->unique_id }}</div>
                            </div>
                        </div>
            <div class="app-header-right">
                <a href="{{ route('profile.edit') }}" class="app-header-icon">
                    <i class="fas fa-user-circle"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="app-content">
        <!-- Modern Pincode Search Section -->
        @if($user->isPatient())
        <div class="modern-search-section">
            <div class="modern-search-card">
                <div class="search-card-header">
                    <div class="search-icon-wrapper">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="search-header-text">
                        <h3 class="search-title">Find Healthcare Staff Near You</h3>
                        <p class="search-subtitle">We use your phone’s <strong>current location</strong> on the Find staff screen (you’ll be asked to allow location).</p>
                    </div>
                </div>

                <div class="alert alert-info border-0 mb-3 py-2 px-3" style="font-size: 0.875rem; border-radius: 12px;">
                    <i class="fas fa-info-circle me-1"></i>
                    Location is only used when you open Find staff to show nurses and caregivers nearest to you. It is not requested at login.
                </div>
                
                <a href="{{ route('staff.index') }}" class="modern-search-btn w-100 d-flex align-items-center justify-content-center gap-2 text-decoration-none" style="min-height: 48px; border-radius: 12px;">
                    <i class="fas fa-crosshairs"></i>
                    <span class="btn-text">Find staff near me</span>
                </a>
            </div>
        </div>
        @endif

        <!-- Welcome Section (Mobile App Style) -->
        <div class="app-welcome-section">
            <div class="app-welcome-card">
                <div class="app-welcome-content">
                    <h1 class="app-welcome-title">Welcome back!</h1>
                    <p class="app-welcome-subtitle">{{ $user->name }}</p>
                    <div class="app-profile-badge">
                        <i class="fas fa-check-circle me-1"></i>
                        {{ $stats['profile_completion'] }}% Profile Complete
        </div>
            </div>
        </div>
    </div>

        @if($user->isPatient())
        @include('services::partials.patient-dashboard-quick-actions')
        @include('services::partials.patient-dashboard-referral-teaser', [
            'planReferralLink' => $planReferralLink ?? null,
            'referralStats' => $referralStats ?? [],
        ])
        @endif

        <!-- Subscription Status Banner (For Patients) -->
        @if($user->isPatient())
        <div class="app-subscription-banner">
            @if(isset($has_active_subscription) && $has_active_subscription && $active_subscription)
            <div class="subscription-status-card active">
                <div class="subscription-status-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="subscription-status-content">
                    <h6 class="subscription-status-title">✅ Active Subscription</h6>
                    <p class="subscription-status-text">
                        <strong>{{ $active_subscription->plan->name }}</strong> - 
                        Expires {{ $active_subscription->end_date->format('M d, Y') }}
                        ({{ $active_subscription->days_remaining }} days remaining)
                    </p>
                    <small class="subscription-status-note">
                        <i class="fas fa-gift me-1"></i>All services are FREE while subscribed!
                    </small>
                </div>
                <a href="{{ route('subscriptions.index') }}" class="subscription-status-action">
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            @else
            <div class="subscription-status-card inactive">
                <div class="subscription-status-icon">
                    <i class="fas fa-info-circle"></i>
        </div>
                <div class="subscription-status-content">
                    <h6 class="subscription-status-title">No Active Subscription</h6>
                    <p class="subscription-status-text">
                        Subscribe to a plan and get FREE healthcare services!
                    </p>
                    <small class="subscription-status-note">
                        Starting from ₹999/month with 10 years total care coverage
                    </small>
                </div>
                <a href="{{ route('plans.index') }}" class="subscription-status-action btn-subscribe">
                    <i class="fas fa-arrow-right me-1"></i>View Plans
                </a>
                </div>
            @endif
            </div>
        
        <!-- Subscribe Now Prominent Card -->
        @if(!isset($has_active_subscription) || !$has_active_subscription)
        <div class="app-subscribe-card">
            <div class="subscribe-card-content">
                <div class="subscribe-icon">
                    <i class="fas fa-heartbeat"></i>
        </div>
                <div class="subscribe-text">
                    <h5 class="subscribe-title">Get FREE Healthcare Services!</h5>
                    <p class="subscribe-description">
                        Subscribe to our healthcare plans and enjoy FREE services for 10 years. 
                        Starting from just ₹999/month.
                    </p>
                </div>
                <a href="{{ route('plans.index') }}" class="subscribe-btn">
                    <i class="fas fa-arrow-right me-2"></i>Browse Plans
                </a>
                </div>
            </div>
        @endif
        @endif

        <!-- Statistics Cards - App Style Grid -->
        <div class="app-stats-section">
            <div class="app-stats-grid">
                <!-- Stat Card 1 -->
                <div class="app-stat-card stat-primary">
                    <div class="app-stat-icon">
                        <i class="fas fa-clipboard-list"></i>
        </div>
                    <div class="app-stat-value">{{ $stats['total_requests'] }}</div>
                    <div class="app-stat-label">Total</div>
                </div>

                <!-- Stat Card 2 -->
                <div class="app-stat-card stat-active">
                    <div class="app-stat-icon">
                        <i class="fas fa-play-circle"></i>
                </div>
                    <div class="app-stat-value">{{ $stats['active_requests'] }}</div>
                    <div class="app-stat-label">Active</div>
            </div>
                
                <!-- Stat Card 3 -->
                <div class="app-stat-card stat-success">
                    <div class="app-stat-icon">
                        <i class="fas fa-check-circle"></i>
        </div>
                    <div class="app-stat-value">{{ $stats['completed_requests'] }}</div>
                    <div class="app-stat-label">Done</div>
    </div>

                <!-- Stat Card 4 -->
                <div class="app-stat-card stat-warning">
                    <div class="app-stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="app-stat-value">{{ $stats['pending_requests'] }}</div>
                    <div class="app-stat-label">Pending</div>
            </div>
        </div>
    </div>

        @if($user->isPatient())
        <div class="mmhc-insights-grid">
            <div class="mmhc-insight-card">
                <div class="mmhc-insight-card__value">₹{{ number_format($stats['total_spent'] ?? 0, 0) }}</div>
                <div class="mmhc-insight-card__label">Paid so far</div>
            </div>
            <div class="mmhc-insight-card">
                <div class="mmhc-insight-card__value">{{ $stats['plan_visits_count'] ?? 0 }}</div>
                <div class="mmhc-insight-card__label">Plan visits (free)</div>
            </div>
            <div class="mmhc-insight-card">
                <div class="mmhc-insight-card__value">{{ $stats['upcoming_services'] ?? 0 }}</div>
                <div class="mmhc-insight-card__label">Upcoming</div>
            </div>
            <div class="mmhc-insight-card">
                <div class="mmhc-insight-card__value" title="{{ $stats['favorite_staff'] ?? '' }}">
                    {{ $stats['favorite_staff'] ? Str::limit($stats['favorite_staff'], 12) : '—' }}
                </div>
                <div class="mmhc-insight-card__label">Top staff</div>
            </div>
        </div>
        @endif

    <!-- Available Staff Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="modern-card">
                <div class="modern-card-header bg-gradient-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-users me-2"></i>Available Healthcare Staff
                        </h5>
                        <a href="{{ route('staff.index') }}" class="btn btn-light btn-sm">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="modern-card-body">
                    <!-- Nurses Section -->
                    @if($available_nurses->count() > 0)
                    <div class="mb-4">
                        <h6 class="staff-section-title mb-3">
                            <i class="fas fa-user-nurse me-2 text-primary"></i>Licensed Nurses
                        </h6>
                        <div class="staff-carousel-container">
                            <div class="staff-carousel">
                                @foreach($available_nurses as $nurse)
                                <div class="staff-card-carousel">
                                    <div class="staff-card">
                                        <div class="staff-avatar">
                                            <i class="fas fa-user-nurse"></i>
                                        </div>
                                        <div class="staff-info">
                                            <h6 class="staff-name">{{ $nurse->name }}</h6>
                                            <div class="staff-details">
                                                @if($nurse->qualification)
                                                <div class="staff-detail-item">
                                                    <i class="fas fa-graduation-cap"></i>
                                                    <span>{{ $nurse->qualification }}</span>
                                                </div>
                                                @endif
                                                @if($nurse->experience)
                                                <div class="staff-detail-item">
                                                    <i class="fas fa-briefcase"></i>
                                                    <span>{{ $nurse->experience }} years exp.</span>
                                                </div>
                                                @endif
                                                @if(isset($nurse->distance_km) && $nurse->distance_km !== null)
                                                <div class="staff-detail-item" style="color: #10b981;">
                                                    <i class="fas fa-route"></i>
                                                    <span>
                                                        @if($nurse->distance_km == 0 || $nurse->distance_km < 0.1)
                                                            Near By
                                                        @else
                                                            {{ number_format($nurse->distance_km, 1) }} km away
                                                        @endif
                                                    </span>
                                                </div>
                                                @endif
                                            </div>
                                            @if($service_types->count() > 0)
                                            <div class="staff-pricing">
                                                <small class="text-muted">Starting from</small>
                                                <div class="staff-price">
                                                    ₹{{ number_format($service_types->first()->patient_charge) }}/day
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                        <div class="staff-action">
                                            <a href="{{ route('book.staff', $nurse) }}" 
                                               class="btn btn-primary btn-sm w-100">
                                                <i class="fas fa-calendar-check me-1"></i>Book Now
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Caregivers Section -->
                    @if($available_caregivers->count() > 0)
                    <div>
                        <h6 class="staff-section-title mb-3">
                            <i class="fas fa-user-md me-2 text-success"></i>Caregivers
                        </h6>
                        <div class="staff-carousel-container">
                            <div class="staff-carousel">
                                @foreach($available_caregivers as $caregiver)
                                <div class="staff-card-carousel">
                                    <div class="staff-card">
                                        <div class="staff-avatar caregiver">
                                            <i class="fas fa-user-md"></i>
                                        </div>
                                        <div class="staff-info">
                                            <h6 class="staff-name">{{ $caregiver->name }}</h6>
                                            <div class="staff-details">
                                                @if($caregiver->qualification)
                                                <div class="staff-detail-item">
                                                    <i class="fas fa-graduation-cap"></i>
                                                    <span>{{ $caregiver->qualification }}</span>
                                                </div>
                                                @endif
                                                @if($caregiver->experience)
                                                <div class="staff-detail-item">
                                                    <i class="fas fa-briefcase"></i>
                                                    <span>{{ $caregiver->experience }} years exp.</span>
                                                </div>
                                                @endif
                                                @if(isset($caregiver->distance_km) && $caregiver->distance_km !== null)
                                                <div class="staff-detail-item" style="color: #10b981;">
                                                    <i class="fas fa-route"></i>
                                                    <span>
                                                        @if($caregiver->distance_km == 0 || $caregiver->distance_km < 0.1)
                                                            Near By
                                                        @else
                                                            {{ number_format($caregiver->distance_km, 1) }} km away
                                                        @endif
                                                    </span>
                                                </div>
                                                @endif
                                            </div>
                                            @if($service_types->count() > 0)
                                            <div class="staff-pricing">
                                                <small class="text-muted">Starting from</small>
                                                <div class="staff-price">
                                                    ₹{{ number_format($service_types->first()->patient_charge) }}/day
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                        <div class="staff-action">
                                            <a href="{{ route('book.staff', $caregiver) }}" 
                                               class="btn btn-success btn-sm w-100">
                                                <i class="fas fa-calendar-check me-1"></i>Book Now
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($available_nurses->count() == 0 && $available_caregivers->count() == 0)
                    <div class="empty-state py-4">
                        <div class="empty-state-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h6 class="empty-state-title">No Staff Nearby</h6>
                        <p class="empty-state-text">Use current location on Find staff to see nurses and caregivers near you.</p>
                        <a href="{{ route('staff.index') }}" class="btn btn-primary btn-sm mt-2">
                            <i class="fas fa-crosshairs me-1"></i>Find staff
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

        <!-- Service Requests Section - App Style -->
        <div class="app-section">
            <div class="app-section-header">
                <h2 class="app-section-title">My Requests</h2>
                <a href="{{ route('services.my-requests') }}" class="app-section-link">
                    View All <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                    @if(isset($recent_requests) && $recent_requests->count() > 0)
                <div class="app-requests-list">
                            @foreach($recent_requests as $request)
                    <a href="{{ route('services.show', $request) }}" class="app-request-card">
                        <div class="app-request-header status-{{ $request->status }}">
                            <div class="app-request-status-icon">
                                    <i class="fas fa-{{ $request->status === 'pending' ? 'clock' : ($request->status === 'assigned' ? 'user-check' : ($request->status === 'in_progress' ? 'play-circle' : 'check-circle')) }}"></i>
                                </div>
                            <div class="app-request-info">
                                <h3 class="app-request-title">{{ $request->serviceType->name }}</h3>
                                <p class="app-request-date">{{ $request->start_date->format('M d') }} - {{ $request->end_date->format('M d, Y') }}</p>
                            </div>
                            <span class="app-request-badge status-{{ $request->status }}">
                                            {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                        </span>
                                    </div>
                        <div class="app-request-body">
                            <div class="app-request-detail">
                                <i class="fas fa-rupee-sign"></i>
                                <span>
                                    @if($request->isCoveredBySubscription())
                                        FREE
                                    @else
                                        ₹{{ number_format($request->total_amount, 0) }} <small class="text-muted">visit fee</small>
                                    @endif
                                </span>
                            </div>
                                        @if($request->assignedStaff)
                            <div class="app-request-detail">
                                <i class="fas fa-user-{{ $request->assignedStaff->isNurse() ? 'nurse' : 'md' }}"></i>
                                <span>{{ Str::limit($request->assignedStaff->name, 25) }}</span>
                                        </div>
                                        @endif
                                    </div>
                                    </a>
                            @endforeach
                        </div>
                
                <!-- Pagination -->
                @if($recent_requests->hasPages())
                <div class="app-pagination">
                    {{ $recent_requests->links() }}
                </div>
                @endif
                    @else
                <div class="app-empty-state">
                    <div class="app-empty-icon">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                    <h3 class="app-empty-title">No Requests Yet</h3>
                    <p class="app-empty-text">Start by requesting your first healthcare service</p>
                        </div>
                    @endif
        </div>

        <!-- Available Staff Section - App Style -->
        @if($available_nurses->count() > 0 || $available_caregivers->count() > 0)
        <div class="app-section">
            <div class="app-section-header">
                <h2 class="app-section-title">Available Staff</h2>
                <a href="{{ route('staff.index') }}" class="app-section-link">
                    View All <i class="fas fa-chevron-right"></i>
                </a>
                            </div>
            <div class="app-staff-carousel">
                @foreach($available_nurses->take(3) as $nurse)
                <a href="{{ route('book.staff', $nurse) }}" class="app-staff-card">
                    <div class="app-staff-avatar">
                        <i class="fas fa-user-nurse"></i>
                            </div>
                    <h4 class="app-staff-name">{{ Str::limit($nurse->name, 15) }}</h4>
                    @if(isset($nurse->distance_km) && $nurse->distance_km !== null)
                    <p class="app-staff-distance">
                        <i class="fas fa-route"></i>
                        @if($nurse->distance_km == 0 || $nurse->distance_km < 0.1)
                            Near By
                        @else
                            {{ number_format($nurse->distance_km, 1) }} km
                        @endif
                    </p>
                    @endif
                </a>
                @endforeach
                @foreach($available_caregivers->take(3) as $caregiver)
                <a href="{{ route('book.staff', $caregiver) }}" class="app-staff-card">
                    <div class="app-staff-avatar caregiver">
                        <i class="fas fa-user-md"></i>
                            </div>
                    <h4 class="app-staff-name">{{ Str::limit($caregiver->name, 15) }}</h4>
                    @if(isset($caregiver->distance_km) && $caregiver->distance_km !== null)
                    <p class="app-staff-distance">
                        <i class="fas fa-route"></i>
                        @if($caregiver->distance_km == 0 || $caregiver->distance_km < 0.1)
                            Near By
                        @else
                            {{ number_format($caregiver->distance_km, 1) }} km
                        @endif
                    </p>
                    @endif
                </a>
                @endforeach
                            </div>
                            </div>
        @endif
            </div>

    <!-- Floating Action Button (FAB) - Mobile Only -->
    <a href="{{ route('staff.index') }}" class="app-fab d-md-none">
        <i class="fas fa-users"></i>
    </a>

</div>

<!-- Mobile App Styling -->
<style>
/* Mobile App Container */
.mobile-app-container {
    position: relative;
    min-height: 100vh;
    background: #f5f7fa;
    padding-bottom: 80px !important; /* Space for bottom nav - Always visible */
}

/* App Header (Mobile Only) */
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

.app-user-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.app-user-info {
    display: flex;
    flex-direction: column;
}

.app-user-name {
    font-size: 1rem;
    font-weight: 600;
    line-height: 1.2;
}

.app-user-id {
    font-size: 0.75rem;
    opacity: 0.8;
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
    padding-bottom: 90px !important; /* Space for bottom nav - Always visible */
}

/* Welcome Section */
.app-welcome-section {
    margin-bottom: 20px;
}

.app-welcome-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 24px;
    color: white;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
}

.app-welcome-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 4px;
}

.app-welcome-subtitle {
    font-size: 1rem;
    opacity: 0.9;
    margin-bottom: 12px;
}

.app-profile-badge {
    display: inline-flex;
    align-items: center;
    background: rgba(255,255,255,0.2);
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    backdrop-filter: blur(10px);
}

/* Stats Section */
.app-stats-section {
    margin-bottom: 24px;
}

.app-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
}

.app-stat-card {
    background: white;
    border-radius: 16px;
    padding: 16px 8px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: transform 0.2s ease;
}

.app-stat-card:active {
    transform: scale(0.95);
}

.app-stat-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 8px;
    font-size: 1rem;
    color: white;
}

.app-stat-card.stat-primary .app-stat-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.app-stat-card.stat-active .app-stat-icon {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.app-stat-card.stat-success .app-stat-icon {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.app-stat-card.stat-warning .app-stat-icon {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.app-stat-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 4px;
}

.app-stat-label {
    font-size: 0.7rem;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* App Section */
.app-section {
    margin-bottom: 24px;
}

.app-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding: 0 4px;
}

.app-section-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
}

.app-section-link {
    font-size: 0.85rem;
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Service Requests List */
.app-requests-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.app-request-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    text-decoration: none;
    color: inherit;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    display: block;
}

.app-request-card:active {
    transform: scale(0.98);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.app-request-header {
    padding: 16px;
    color: white;
    display: flex;
    align-items: center;
    gap: 12px;
}

.app-request-header.status-pending {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.app-request-header.status-assigned {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.app-request-header.status-in_progress {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.app-request-header.status-completed {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.app-request-status-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.app-request-info {
    flex: 1;
    min-width: 0;
}

.app-request-title {
    font-size: 1rem;
    font-weight: 600;
    margin: 0 0 4px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.app-request-date {
    font-size: 0.8rem;
    opacity: 0.9;
    margin: 0;
}

.app-request-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    background: rgba(255,255,255,0.25);
    border: 1px solid rgba(255,255,255,0.3);
    white-space: nowrap;
}

.app-request-body {
    padding: 12px 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.app-request-detail {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
    color: #6c757d;
}

.app-request-detail i {
    width: 18px;
    color: #667eea;
    text-align: center;
}

/* Staff Carousel */
.app-staff-carousel {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding-bottom: 8px;
    -webkit-overflow-scrolling: touch;
    scroll-snap-type: x mandatory;
}

.app-staff-carousel::-webkit-scrollbar {
    display: none;
}

.app-staff-card {
    flex: 0 0 auto;
    width: 140px;
    background: white;
    border-radius: 16px;
    padding: 16px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    text-decoration: none;
    color: inherit;
    scroll-snap-align: start;
    transition: transform 0.2s ease;
}

.app-staff-card:active {
    transform: scale(0.95);
}

.app-staff-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    margin: 0 auto 12px;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.app-staff-avatar.caregiver {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    box-shadow: 0 4px 12px rgba(17, 153, 142, 0.3);
}

.app-staff-name {
    font-size: 0.9rem;
    font-weight: 600;
    color: #2c3e50;
    margin: 0 0 8px 0;
}

.app-staff-distance {
    font-size: 0.75rem;
    color: #6c757d;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
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
    margin: 0;
}

/* Pagination */
.app-pagination {
    margin-top: 16px;
    display: flex;
    justify-content: center;
}

/* Floating Action Button */
.app-fab {
    position: fixed;
    bottom: 90px;
    right: 20px;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
    z-index: 999;
    text-decoration: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.app-fab:active {
    transform: scale(0.9);
    box-shadow: 0 2px 10px rgba(102, 126, 234, 0.3);
}

/* Bottom Navigation */
.app-bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: space-around;
    align-items: center;
    padding: 8px 0;
    padding-bottom: max(8px, env(safe-area-inset-bottom));
    z-index: 1000;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
}

.app-nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    text-decoration: none;
    color: #6c757d;
    padding: 8px 16px;
    transition: color 0.2s ease;
    flex: 1;
}

.app-nav-item i {
    font-size: 1.2rem;
}

.app-nav-item span {
    font-size: 0.7rem;
    font-weight: 600;
}

.app-nav-item.active {
    color: #667eea;
}

.app-nav-item.active i {
    color: #667eea;
}

/* Desktop View - Hide Mobile Elements */
@media (min-width: 768px) {
    .mobile-app-container {
        padding-bottom: 0;
    }
    
    .app-content {
        padding: 24px;
        padding-bottom: 24px;
    }
    
    .app-stats-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }
    
    .app-requests-list {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    
    .app-staff-carousel {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        overflow: visible;
    }
    
    .app-staff-card {
        width: 100%;
    }
}

/* Header Styles (Desktop) */
.patient-header-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.patient-avatar-large {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: var(--patient-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
}

.patient-name {
    font-size: 1.75rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
}

.patient-subtitle {
    font-size: 0.9rem;
}

.badge-patient {
    background: var(--patient-gradient);
    color: white;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-weight: 600;
}

.profile-badge {
    background: #f8f9fa;
    color: #6c757d;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.8rem;
}

/* Stat Cards */
.stat-card-modern {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
    height: 100%;
}

.stat-card-modern:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    flex-shrink: 0;
}

.stat-primary .stat-icon {
    background: var(--primary-gradient);
}

.stat-info .stat-icon {
    background: var(--info-gradient);
}

.stat-success .stat-icon {
    background: var(--success-gradient);
}

.stat-warning .stat-icon {
    background: var(--warning-gradient);
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
    line-height: 1;
    margin-bottom: 0.3rem;
}

.stat-label {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Modern Card */
.modern-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.modern-card:hover {
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
}

.modern-card-header {
    padding: 1.2rem 1.5rem;
}

.modern-card-body {
    padding: 1.5rem;
}

/* Mobile-First Service Request Cards */
.service-request-card-mobile {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.service-request-card-mobile:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.card-header-mobile {
    padding: 0.75rem 1rem;
    color: white;
    font-weight: 600;
}

.card-header-mobile.status-pending {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.card-header-mobile.status-assigned {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.card-header-mobile.status-in_progress {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.card-header-mobile.status-completed {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.service-status-icon {
    font-size: 1.2rem;
}

.badge-status-mobile {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 12px;
    font-weight: 600;
}

.card-body-mobile {
    padding: 1rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.card-title-mobile {
    font-size: 1rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.75rem;
}

.card-details-mobile {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.detail-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: #6c757d;
}

.detail-row i {
    width: 18px;
    color: #667eea;
    text-align: center;
}

.card-footer-mobile {
    margin-top: auto;
    padding-top: 0.75rem;
    border-top: 1px solid #e9ecef;
}

.service-request-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.service-request-icon.status-pending {
    background: var(--warning-gradient);
}

.service-request-icon.status-assigned {
    background: var(--info-gradient);
}

.service-request-icon.status-in_progress {
    background: var(--primary-gradient);
}

.service-request-icon.status-completed {
    background: var(--success-gradient);
}

.service-request-content {
    flex: 1;
}

.service-request-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.service-request-title {
    font-size: 1rem;
    font-weight: 600;
    color: #2c3e50;
}

.badge-status-pending {
    background: #ffc107;
    color: #000;
}

.badge-status-assigned {
    background: #17a2b8;
    color: white;
}

.badge-status-in_progress {
    background: #667eea;
    color: white;
}

.badge-status-completed {
    background: #28a745;
    color: white;
}

.service-request-details {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    font-size: 0.85rem;
    color: #6c757d;
}

.detail-item {
    display: flex;
    align-items: center;
}

.service-request-actions {
    display: flex;
    gap: 0.5rem;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
}

.empty-state-icon {
    font-size: 4rem;
    color: #dee2e6;
    margin-bottom: 1rem;
}

.empty-state-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.empty-state-text {
    color: #6c757d;
    margin-bottom: 1.5rem;
}

/* Mobile-First Quick Action Cards */
.quick-action-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1rem 0.5rem;
    background: #f8f9fa;
    border-radius: 12px;
    text-decoration: none;
    color: #2c3e50;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    min-height: 100px;
}

.quick-action-card:hover {
    background: white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-3px);
    border-color: #667eea;
    color: #2c3e50;
    text-decoration: none;
}

.quick-action-icon-mobile {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.quick-action-title-mobile {
    font-size: 0.85rem;
    font-weight: 600;
    text-align: center;
    margin: 0;
    line-height: 1.2;
}

/* Mobile Profile Progress */
.profile-progress-mobile {
    padding: 0.5rem 0;
}

.progress-bar-wrapper-mobile {
    height: 12px;
    background: #e9ecef;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 0.75rem;
}

.progress-bar-fill-mobile {
    height: 100%;
    background: var(--success-gradient);
    border-radius: 10px;
    transition: width 0.3s ease;
}

.progress-text-mobile {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.85rem;
    color: #6c757d;
    flex-wrap: wrap;
    gap: 0.5rem;
}

/* Mobile-First Responsive Design */
@media (max-width: 768px) {
    .container-fluid {
        padding: 0.75rem;
    }
    
    .patient-header-card {
        padding: 1rem;
        border-radius: 12px;
    }
    
    .patient-name {
        font-size: 1.25rem;
    }
    
    .patient-subtitle {
        font-size: 0.8rem;
    }
    
    .stat-card-modern {
        padding: 1rem;
        flex-direction: row;
        gap: 0.75rem;
    }
    
    .stat-value {
        font-size: 1.5rem;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    }
    
    .stat-label {
        font-size: 0.75rem;
    }
    
    .modern-card-body {
        padding: 1rem;
    }
    
    .quick-action-card {
        min-height: 90px;
        padding: 0.75rem 0.25rem;
    }
    
    .quick-action-icon-mobile {
        width: 45px;
        height: 45px;
        font-size: 1.3rem;
    }
    
    .quick-action-title-mobile {
        font-size: 0.75rem;
    }
    
    .card-body-mobile {
        padding: 0.75rem;
    }
    
    .card-title-mobile {
        font-size: 0.9rem;
    }
    
    .detail-row {
        font-size: 0.8rem;
    }
}

@media (max-width: 576px) {
    .container-fluid {
        padding: 0.5rem;
    }
    
    .patient-name {
        font-size: 1.1rem;
    }
    
    .stat-value {
        font-size: 1.3rem;
    }
    
    .stat-icon {
        width: 45px;
        height: 45px;
        font-size: 1.1rem;
    }
    
    .stat-card-modern {
        padding: 0.75rem;
    }
    
    .modern-card-header {
        padding: 0.75rem 1rem;
    }
    
    .modern-card-header h5 {
        font-size: 0.95rem;
    }
    
    .service-request-card-mobile {
        border-radius: 10px;
    }
    
    .card-header-mobile {
        padding: 0.5rem 0.75rem;
    }
    
    .card-body-mobile {
        padding: 0.75rem;
    }
    
    .quick-action-card {
        min-height: 80px;
        padding: 0.5rem 0.25rem;
    }
    
    .quick-action-icon-mobile {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
    }
    
    .quick-action-title-mobile {
        font-size: 0.7rem;
    }
}

/* Staff Cards */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.staff-section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
}

/* Horizontal Scrolling Carousel */
.staff-carousel-container {
    position: relative;
    width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scroll-behavior: smooth;
    padding-bottom: 1rem;
    margin-bottom: 1rem;
}

/* Hide scrollbar but keep functionality */
.staff-carousel-container::-webkit-scrollbar {
    height: 8px;
}

.staff-carousel-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.staff-carousel-container::-webkit-scrollbar-thumb {
    background: #667eea;
    border-radius: 10px;
}

.staff-carousel-container::-webkit-scrollbar-thumb:hover {
    background: #764ba2;
}

/* For Firefox */
.staff-carousel-container {
    scrollbar-width: thin;
    scrollbar-color: #667eea #f1f1f1;
}

.staff-carousel {
    display: flex;
    gap: 1rem;
    padding: 0.5rem 0.25rem;
}

.staff-card-carousel {
    flex: 0 0 auto;
    width: 85vw; /* Mobile: 1 card per screen (85% of viewport) */
    min-width: 280px;
    max-width: 320px;
}

/* Tablet: Show 2 cards at a time */
@media (min-width: 576px) {
    .staff-card-carousel {
        width: calc(50vw - 2rem); /* 2 cards visible */
        min-width: 280px;
        max-width: 350px;
    }
}

/* Desktop: Show 3 cards at a time */
@media (min-width: 992px) {
    .staff-card-carousel {
        width: calc(33.333vw - 2rem); /* 3 cards visible */
        min-width: 300px;
        max-width: 380px;
    }
}

.staff-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border: 2px solid transparent;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.staff-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border-color: #667eea;
}

.staff-avatar {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.8rem;
    margin: 0 auto 1rem;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.staff-avatar.caregiver {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
}

.staff-info {
    flex: 1;
    text-align: center;
    margin-bottom: 1rem;
}

.staff-name {
    font-size: 1rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.75rem;
}

.staff-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.staff-detail-item {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: #6c757d;
}

.staff-detail-item i {
    color: #667eea;
    width: 16px;
}

.staff-pricing {
    margin-top: auto;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}

.staff-price {
    font-size: 1.1rem;
    font-weight: 700;
    color: #28a745;
    margin-top: 0.25rem;
}

.staff-action {
    margin-top: 1rem;
}

/* Mobile Activity Feed */
.activity-feed-mobile {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.activity-item-mobile {
    display: flex;
    gap: 0.75rem;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 10px;
    transition: all 0.2s ease;
}

.activity-item-mobile:hover {
    background: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.activity-icon-mobile {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.85rem;
    flex-shrink: 0;
}

.activity-content-mobile {
    flex: 1;
    min-width: 0;
}

.activity-message-mobile {
    font-size: 0.85rem;
    color: #2c3e50;
    margin-bottom: 0.25rem;
    line-height: 1.4;
    word-wrap: break-word;
}

.activity-link-mobile {
    color: #667eea;
    text-decoration: none;
    transition: color 0.2s ease;
}

.activity-link-mobile:hover {
    color: #764ba2;
    text-decoration: underline;
}

.activity-time-mobile {
    font-size: 0.7rem;
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.activity-time-mobile i {
    font-size: 0.65rem;
}

/* Quick Actions Bar */
.modern-card .btn-lg {
    font-size: 1rem;
    padding: 0.75rem 1.5rem;
}

@media (max-width: 768px) {
    .staff-card {
        margin-bottom: 1rem;
    }
    
    .staff-avatar {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }

    .modern-card .btn-lg {
        font-size: 0.9rem;
        padding: 0.6rem 1.2rem;
    }

    .activity-item {
        padding: 0.5rem;
    }

    .activity-icon {
        width: 35px;
        height: 35px;
        font-size: 0.85rem;
    }
}

/* Subscription Status Banner */
.app-subscription-banner {
    margin-bottom: 20px;
}

.subscription-status-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 16px;
    border-left: 4px solid;
    transition: transform 0.2s ease;
}

.subscription-status-card:active {
    transform: scale(0.98);
}

.subscription-status-card.active {
    border-left-color: #28a745;
    background: linear-gradient(135deg, #f0fff4 0%, #ffffff 100%);
}

.subscription-status-card.inactive {
    border-left-color: #ffc107;
    background: linear-gradient(135deg, #fffbf0 0%, #ffffff 100%);
}

.subscription-status-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.subscription-status-card.active .subscription-status-icon {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
}

.subscription-status-card.inactive .subscription-status-icon {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    color: white;
}

.subscription-status-content {
    flex: 1;
    min-width: 0;
}

.subscription-status-title {
    font-size: 1rem;
    font-weight: 700;
    color: #212529;
    margin-bottom: 6px;
}

.subscription-status-text {
    font-size: 0.9rem;
    color: #495057;
    margin-bottom: 6px;
    line-height: 1.4;
}

.subscription-status-note {
    font-size: 0.8rem;
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 4px;
}

.subscription-status-action {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #667eea;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    flex-shrink: 0;
    transition: all 0.2s ease;
    cursor: pointer;
    position: relative;
    z-index: 10;
}

.subscription-status-action.btn-subscribe {
    padding: 8px 16px;
    width: auto;
    height: auto;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.subscription-status-action:hover {
    background: #764ba2;
    transform: scale(1.05);
    color: white;
    text-decoration: none;
}

/* Subscribe Now Prominent Card */
.app-subscribe-card {
    margin-bottom: 24px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
}

.subscribe-card-content {
    display: flex;
    align-items: center;
    gap: 20px;
    color: white;
}

.subscribe-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    flex-shrink: 0;
    backdrop-filter: blur(10px);
}

.subscribe-text {
    flex: 1;
    min-width: 0;
}

.subscribe-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 8px;
    color: white;
}

.subscribe-description {
    font-size: 0.9rem;
    opacity: 0.95;
    margin: 0;
    line-height: 1.5;
}

.subscribe-btn {
    background: white;
    color: #667eea;
    padding: 12px 24px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    transition: all 0.2s ease;
    flex-shrink: 0;
    white-space: nowrap;
    cursor: pointer;
    position: relative;
    z-index: 10;
}

.subscribe-btn:hover {
    background: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    color: #667eea;
    text-decoration: none;
}

@media (max-width: 768px) {
    .subscribe-card-content {
        flex-direction: column;
        text-align: center;
    }
    
    .subscribe-btn {
        width: 100%;
        justify-content: center;
    }
    
    .subscription-status-card {
        flex-wrap: wrap;
    }
    
    .subscription-status-action {
        width: 100%;
        border-radius: 12px;
        margin-top: 12px;
    }
}

/* Modern Search Section */
.modern-search-section {
    margin-bottom: 24px;
}

.modern-search-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 8px 30px rgba(102, 126, 234, 0.25);
    position: relative;
    overflow: hidden;
}

.modern-search-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: pulse 3s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 0.3; }
    50% { opacity: 0.6; }
}

.search-card-header {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 20px;
    position: relative;
    z-index: 1;
}

.search-icon-wrapper {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    flex-shrink: 0;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.search-header-text {
    flex: 1;
    min-width: 0;
}

.search-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: white;
    margin: 0 0 6px 0;
    line-height: 1.3;
}

.search-subtitle {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.9);
    margin: 0;
    line-height: 1.4;
}

.modern-search-form {
    position: relative;
    z-index: 1;
}

.search-input-wrapper {
    margin-bottom: 12px;
}

.search-input-container {
    display: flex;
    align-items: center;
    gap: 12px;
    background: white;
    border-radius: 14px;
    padding: 4px 4px 4px 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}

.search-input-container:focus-within {
    box-shadow: 0 6px 25px rgba(0, 0, 0, 0.2);
    transform: translateY(-2px);
}

.search-input-icon {
    color: #667eea;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.modern-search-input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 1rem;
    font-weight: 500;
    color: #2c3e50;
    padding: 14px 0;
    background: transparent;
    min-width: 0;
}

.modern-search-input::placeholder {
    color: #adb5bd;
    font-weight: 400;
}

.modern-search-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 12px;
    padding: 14px 24px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    white-space: nowrap;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.modern-search-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.modern-search-btn:active {
    transform: translateY(0);
}

.modern-search-btn i {
    font-size: 1rem;
}

.saved-pincode-info {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 16px;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    margin-top: 12px;
}

.saved-pincode-content {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 0;
}

.saved-icon {
    color: #28a745;
    font-size: 1.1rem;
    flex-shrink: 0;
    background: white;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.saved-text {
    color: white;
    font-size: 0.9rem;
    font-weight: 500;
}

.saved-text strong {
    font-weight: 700;
    color: white;
}

.change-pincode-link {
    display: flex;
    align-items: center;
    gap: 6px;
    color: white;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 600;
    padding: 6px 12px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.change-pincode-link:hover {
    background: rgba(255, 255, 255, 0.3);
    color: white;
    text-decoration: none;
    transform: translateY(-1px);
}

.change-pincode-link i {
    font-size: 0.8rem;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .modern-search-card {
        padding: 20px;
        border-radius: 16px;
    }
    
    .search-card-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 12px;
    }
    
    .search-icon-wrapper {
        width: 50px;
        height: 50px;
        font-size: 1.3rem;
    }
    
    .search-title {
        font-size: 1.25rem;
    }
    
    .search-subtitle {
        font-size: 0.85rem;
    }
    
    .search-input-container {
        flex-direction: column;
        padding: 12px;
        gap: 8px;
    }
    
    .search-input-icon {
        display: none;
    }
    
    .modern-search-input {
        width: 100%;
        padding: 12px;
        text-align: center;
        font-size: 1.1rem;
        letter-spacing: 2px;
    }
    
    .modern-search-btn {
        width: 100%;
        justify-content: center;
        padding: 14px;
    }
    
    .saved-pincode-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .change-pincode-link {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 576px) {
    .modern-search-card {
        padding: 16px;
    }
    
    .search-title {
        font-size: 1.1rem;
    }
    
    .search-subtitle {
        font-size: 0.8rem;
    }
    
    .search-icon-wrapper {
        width: 45px;
        height: 45px;
        font-size: 1.2rem;
    }
}
</style>
@endsection
