@extends('auth::layout')

@section('title', 'Available Services - MMHC CRM')

@section('head')
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    @include('services::partials.mobile-assets')
@endsection

@section('content')
<div class="mobile-app-container mmhc-page-services">
    <!-- App Header (Mobile Only) -->
    <div class="app-header-mobile d-md-none">
        <div class="app-header-content">
            <div class="app-header-left">
                <a href="{{ route('dashboard') }}" class="app-back-btn">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <div class="app-header-title">Available Services</div>
                    <div class="app-header-subtitle">Choose healthcare service</div>
                </div>
            </div>
            <div class="app-header-right">
                @if(Auth::user()->isPatient())
                <a href="{{ route('staff.index') }}" class="app-header-icon" title="Find staff">
                    <i class="fas fa-plus"></i>
                </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="app-content">
        <!-- Desktop Header -->
        <div class="d-none d-md-block mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-primary">Available Services</h2>
                    <p class="text-muted">Choose the right healthcare service for your needs</p>
                </div>
                @if(Auth::user()->isPatient())
                <a href="{{ route('staff.index') }}" class="btn btn-primary">
                    <i class="fas fa-users me-2"></i>Find Staff
                </a>
                @endif
            </div>
        </div>

        @if(Auth::user()->isPatient())
        @include('services::partials.booking-flow-steps', ['currentStep' => 'services'])
        @endif

        <!-- Service Types Grid - App Style -->
        <div class="app-service-grid">
            @foreach($serviceTypes as $serviceType)
            <a href="{{ route('staff.index') }}" class="app-service-card">
                <div class="app-service-header service-{{ $serviceType->duration_hours }}">
                    <div class="app-service-icon">
                        @if($serviceType->duration_hours == 24)
                            <i class="fas fa-clock"></i>
                        @elseif($serviceType->duration_hours == 12)
                            <i class="fas fa-sun"></i>
                        @elseif($serviceType->duration_hours == 8)
                            <i class="fas fa-briefcase"></i>
                        @else
                            <i class="fas fa-user-md"></i>
                        @endif
                    </div>
                    <div class="app-service-badge">
                        @if($serviceType->duration_hours == 24)
                            Full Day
                        @elseif($serviceType->duration_hours == 12)
                            Half Day
                        @elseif($serviceType->duration_hours == 8)
                            Standard
                        @else
                            Quick Visit
                        @endif
                    </div>
                </div>
                <div class="app-service-body">
                    <h4 class="app-service-title">{{ $serviceType->name }}</h4>
                    <p class="app-service-desc">{{ $serviceType->description }}</p>
                    
                    <div class="app-service-pricing">
                        <div class="app-service-price">â‚¹{{ number_format($serviceType->patient_charge) }}</div>
                        <small class="app-service-period">per {{ $serviceType->duration_hours == 1 ? 'visit' : 'day' }}</small>
                    </div>
                    
                    <div class="app-service-details">
                        <div class="app-service-detail-item">
                            <i class="fas fa-clock"></i>
                            <span>{{ $serviceType->duration_hours }}h duration</span>
                        </div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        <!-- How It Works - App Style Grid -->
        @if(Auth::user()->isPatient())
        <div class="app-section">
            <div class="app-section-header">
                <h2 class="app-section-title">How It Works</h2>
                <p class="app-section-subtitle">Simple steps to get healthcare</p>
            </div>
            
            <div class="app-steps-grid">
                <div class="app-step-card">
                    <div class="app-step-number">1</div>
                    <div class="app-step-icon step-register">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h4 class="app-step-title">Register</h4>
                    <p class="app-step-desc">Create your account</p>
                </div>
                
                <div class="app-step-card">
                    <div class="app-step-number">2</div>
                    <div class="app-step-icon step-request">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h4 class="app-step-title">Request</h4>
                    <p class="app-step-desc">Choose service</p>
                </div>
                
                <div class="app-step-card">
                    <div class="app-step-number">3</div>
                    <div class="app-step-icon step-assign">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <h4 class="app-step-title">Get Assigned</h4>
                    <p class="app-step-desc">Staff assigned</p>
                </div>
                
                <div class="app-step-card">
                    <div class="app-step-number">4</div>
                    <div class="app-step-icon step-care">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h4 class="app-step-title">Receive Care</h4>
                    <p class="app-step-desc">Professional care</p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Bottom Navigation -->
</div>
@endsection
