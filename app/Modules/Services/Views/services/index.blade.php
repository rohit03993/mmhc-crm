@extends('auth::layout')

@section('title', 'Available Services - MMHC CRM')

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
                    <h2 class="text-primary">Available Services</h2>
                    <p class="text-muted">Choose the right healthcare service for your needs</p>
                </div>
                @if(Auth::user()->isPatient())
                <a href="{{ route('services.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Request Service
                </a>
                @endif
            </div>

            <div class="row">
                @foreach($serviceTypes as $serviceType)
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card h-100 service-card">
                        <div class="card-body text-center">
                            <div class="service-icon mb-3">
                                @if($serviceType->duration_hours == 24)
                                    <i class="fas fa-clock fa-3x text-primary"></i>
                                @elseif($serviceType->duration_hours == 12)
                                    <i class="fas fa-sun fa-3x text-warning"></i>
                                @elseif($serviceType->duration_hours == 8)
                                    <i class="fas fa-briefcase fa-3x text-info"></i>
                                @else
                                    <i class="fas fa-user-md fa-3x text-success"></i>
                                @endif
                            </div>
                            <h5 class="card-title">{{ $serviceType->name }}</h5>
                            <p class="text-muted">{{ $serviceType->description }}</p>
                            
                            <div class="pricing-section mb-3">
                                <div class="text-primary fw-bold fs-4">₹{{ number_format($serviceType->patient_charge) }}</div>
                                <small class="text-muted">per {{ $serviceType->duration_hours == 1 ? 'visit' : 'day' }}</small>
                            </div>

                            <div class="service-details">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <small class="text-muted">Duration</small>
                                        <div class="fw-bold">{{ $serviceType->duration_hours }}h</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Platform Profit</small>
                                        <div class="fw-bold text-success">₹{{ number_format($serviceType->platform_profit) }}</div>
                                    </div>
                                </div>
                            </div>

                            @if(Auth::user()->isPatient())
                            <div class="mt-3">
                                <a href="{{ route('services.create') }}?service_type={{ $serviceType->id }}" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-calendar-plus me-1"></i>Request This Service
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if(Auth::user()->isPatient())
            <div class="row mt-5">
                <div class="col-12">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5 class="text-primary">How It Works</h5>
                            <div class="row">
                                <div class="col-md-3 text-center mb-3">
                                    <div class="step-icon mb-2">
                                        <i class="fas fa-user-plus fa-2x text-primary"></i>
                                    </div>
                                    <h6>1. Register</h6>
                                    <p class="small text-muted">Create your patient account</p>
                                </div>
                                <div class="col-md-3 text-center mb-3">
                                    <div class="step-icon mb-2">
                                        <i class="fas fa-calendar-check fa-2x text-primary"></i>
                                    </div>
                                    <h6>2. Request Service</h6>
                                    <p class="small text-muted">Choose your preferred service</p>
                                </div>
                                <div class="col-md-3 text-center mb-3">
                                    <div class="step-icon mb-2">
                                        <i class="fas fa-user-md fa-2x text-primary"></i>
                                    </div>
                                    <h6>3. Get Assigned</h6>
                                    <p class="small text-muted">We assign qualified staff</p>
                                </div>
                                <div class="col-md-3 text-center mb-3">
                                    <div class="step-icon mb-2">
                                        <i class="fas fa-heart fa-2x text-primary"></i>
                                    </div>
                                    <h6>4. Receive Care</h6>
                                    <p class="small text-muted">Get professional healthcare</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
.service-card {
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.service-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border-color: #007bff;
}

.service-icon {
    margin-bottom: 1rem;
}

.pricing-section {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.5rem;
    margin: 1rem 0;
}

.service-details {
    background-color: #f8f9fa;
    padding: 0.75rem;
    border-radius: 0.5rem;
    margin-top: 1rem;
}

.step-icon {
    margin-bottom: 0.5rem;
}

@media (max-width: 768px) {
    .service-card {
        margin-bottom: 1rem;
    }
}
</style>
@endsection
