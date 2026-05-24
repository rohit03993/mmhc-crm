@extends('auth::layout')

@section('title', 'Available Services - MMHC CRM')

@section('head')
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
@endsection

@section('content')
<!-- Mobile App View for Services -->
<div class="mobile-app-container">
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
                <a href="{{ route('services.create') }}" class="app-header-icon">
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
                        <div class="app-service-price">₹{{ number_format($serviceType->patient_charge) }}</div>
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

/* Service Grid */
.app-service-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
    margin-bottom: 24px;
}

.app-service-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    text-decoration: none;
    color: inherit;
    display: block;
}

.app-service-card:active {
    transform: scale(0.98);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.app-service-header {
    padding: 20px;
    text-align: center;
    color: white;
    position: relative;
}

.app-service-header.service-24 {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.app-service-header.service-12 {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.app-service-header.service-8 {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.app-service-header.service-1 {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.app-service-icon {
    font-size: 2.5rem;
    margin-bottom: 8px;
}

.app-service-badge {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

.app-service-body {
    padding: 16px;
}

.app-service-title {
    font-size: 1rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 8px 0;
}

.app-service-desc {
    font-size: 0.85rem;
    color: #6c757d;
    margin: 0 0 12px 0;
    line-height: 1.4;
}

.app-service-pricing {
    text-align: center;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 12px;
    margin-bottom: 12px;
}

.app-service-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: #28a745;
    margin-bottom: 4px;
}

.app-service-period {
    font-size: 0.75rem;
    color: #6c757d;
}

.app-service-details {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.app-service-detail-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
    color: #6c757d;
}

.app-service-detail-item i {
    color: #667eea;
    width: 20px;
    text-align: center;
}

/* App Section */
.app-section {
    margin-bottom: 24px;
}

.app-section-header {
    margin-bottom: 16px;
    padding: 0 4px;
}

.app-section-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 4px 0;
}

.app-section-subtitle {
    font-size: 0.85rem;
    color: #6c757d;
    margin: 0;
}

/* Steps Grid */
.app-steps-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.app-step-card {
    background: white;
    border-radius: 16px;
    padding: 20px 16px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    position: relative;
    transition: transform 0.2s ease;
}

.app-step-card:active {
    transform: scale(0.97);
}

.app-step-number {
    position: absolute;
    top: -12px;
    right: 12px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.app-step-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    font-size: 1.5rem;
    color: white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.app-step-icon.step-register {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.app-step-icon.step-request {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.app-step-icon.step-assign {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.app-step-icon.step-care {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.app-step-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 6px 0;
}

.app-step-desc {
    font-size: 0.8rem;
    color: #6c757d;
    margin: 0;
    line-height: 1.4;
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
    
    .app-service-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    
    .app-steps-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }
}

/* Mobile Optimizations */
@media (max-width: 576px) {
    .app-service-icon {
        font-size: 2rem;
    }
    
    .app-service-title {
        font-size: 0.9rem;
    }
    
    .app-service-desc {
        font-size: 0.8rem;
    }
    
    .app-step-icon {
        width: 48px;
        height: 48px;
        font-size: 1.3rem;
    }
    
    .app-step-title {
        font-size: 0.85rem;
    }
    
    .app-step-desc {
        font-size: 0.75rem;
    }
}
</style>
@endsection
