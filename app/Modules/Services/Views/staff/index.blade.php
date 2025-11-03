@extends('auth::layout')

@section('title', 'Available Staff - MMHC CRM')

@section('head')
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    
    <style>
        :root {
            --nurse-gradient: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            --caregiver-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
@endsection

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="modern-page-header-staff">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                    <div>
                        <h1 class="page-title-staff">
                            <i class="fas fa-user-nurse me-2"></i>
                            Available Healthcare Staff
                        </h1>
                        <p class="page-subtitle-staff">Choose from our qualified nurses and caregivers</p>
                    </div>
                    <a href="{{ route('services.create') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus-circle me-2"></i>Request Service
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Nurses Section -->
    <div class="mb-5">
        <div class="section-header-modern section-header-nurse mb-4">
            <div class="section-header-icon">
                <i class="fas fa-user-nurse"></i>
            </div>
            <div class="section-header-content">
                <h2 class="section-title">Licensed Nurses</h2>
                <p class="section-subtitle">Professional nurses with medical qualifications</p>
            </div>
            <div class="section-header-count">
                <span class="count-badge">{{ $nurses->count() }}</span>
            </div>
        </div>
        
        @if($nurses->count() > 0)
        <div class="row g-4">
            @foreach($nurses as $nurse)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="staff-card-modern staff-card-nurse">
                    <div class="staff-card-header">
                        <div class="staff-avatar-modern nurse-avatar">
                            <i class="fas fa-user-nurse"></i>
                        </div>
                        <div class="staff-info-header">
                            <h4 class="staff-name-modern">{{ $nurse->name }}</h4>
                            <span class="staff-id-badge nurse-badge">{{ $nurse->unique_id }}</span>
                        </div>
                    </div>
                    
                    <div class="staff-card-body">
                        <div class="staff-details-modern">
                            <div class="detail-row">
                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <div class="detail-content">
                                        <div class="detail-label">Qualification</div>
                                        <div class="detail-value">{{ $nurse->qualification ?? 'Not specified' }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <i class="fas fa-briefcase"></i>
                                    </div>
                                    <div class="detail-content">
                                        <div class="detail-label">Experience</div>
                                        <div class="detail-value">{{ $nurse->experience ?? 'Not specified' }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="detail-content">
                                        <div class="detail-label">Location</div>
                                        <div class="detail-value">{{ Str::limit($nurse->address ?? 'Not specified', 35) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pricing Section -->
                        <div class="pricing-section-modern">
                            <div class="pricing-header">
                                <i class="fas fa-rupee-sign me-2"></i>
                                <span>Service Rates</span>
                            </div>
                            <div class="pricing-grid">
                                @foreach($serviceTypes as $serviceType)
                                    @if($serviceType->duration_hours != 1)
                                    <div class="pricing-item">
                                        <div class="pricing-duration">{{ $serviceType->duration_hours }}h Care</div>
                                        <div class="pricing-amount nurse-pricing">₹{{ number_format($serviceType->nurse_payout) }}/day</div>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <div class="staff-card-footer">
                        <a href="{{ route('services.create') }}?staff_type=nurse&staff_id={{ $nurse->id }}" 
                           class="btn-request-staff btn-request-nurse">
                            <i class="fas fa-calendar-plus me-2"></i>
                            Request This Nurse
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="empty-state-staff">
            <div class="empty-icon-staff">
                <i class="fas fa-user-nurse"></i>
            </div>
            <h4 class="empty-title-staff">No Nurses Available</h4>
            <p class="empty-text-staff">Currently no nurses are available. Please check back later.</p>
        </div>
        @endif
    </div>

    <!-- Caregivers Section -->
    <div class="mb-5">
        <div class="section-header-modern section-header-caregiver mb-4">
            <div class="section-header-icon">
                <i class="fas fa-user-md"></i>
            </div>
            <div class="section-header-content">
                <h2 class="section-title">Caregivers</h2>
                <p class="section-subtitle">General support staff for everyday care</p>
            </div>
            <div class="section-header-count">
                <span class="count-badge">{{ $caregivers->count() }}</span>
            </div>
        </div>
        
        @if($caregivers->count() > 0)
        <div class="row g-4">
            @foreach($caregivers as $caregiver)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="staff-card-modern staff-card-caregiver">
                    <div class="staff-card-header">
                        <div class="staff-avatar-modern caregiver-avatar">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="staff-info-header">
                            <h4 class="staff-name-modern">{{ $caregiver->name }}</h4>
                            <span class="staff-id-badge caregiver-badge">{{ $caregiver->unique_id }}</span>
                        </div>
                    </div>
                    
                    <div class="staff-card-body">
                        <div class="staff-details-modern">
                            <div class="detail-row">
                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <div class="detail-content">
                                        <div class="detail-label">Specialization</div>
                                        <div class="detail-value">{{ $caregiver->qualification ?? 'General Care' }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <i class="fas fa-briefcase"></i>
                                    </div>
                                    <div class="detail-content">
                                        <div class="detail-label">Experience</div>
                                        <div class="detail-value">{{ $caregiver->experience ?? 'Not specified' }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="detail-content">
                                        <div class="detail-label">Location</div>
                                        <div class="detail-value">{{ Str::limit($caregiver->address ?? 'Not specified', 35) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pricing Section -->
                        <div class="pricing-section-modern">
                            <div class="pricing-header">
                                <i class="fas fa-rupee-sign me-2"></i>
                                <span>Service Rates</span>
                            </div>
                            <div class="pricing-grid">
                                @foreach($serviceTypes as $serviceType)
                                    @if($serviceType->duration_hours != 1)
                                    <div class="pricing-item">
                                        <div class="pricing-duration">{{ $serviceType->duration_hours }}h Care</div>
                                        <div class="pricing-amount caregiver-pricing">₹{{ number_format($serviceType->caregiver_payout) }}/day</div>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <div class="staff-card-footer">
                        <a href="{{ route('services.create') }}?staff_type=caregiver&staff_id={{ $caregiver->id }}" 
                           class="btn-request-staff btn-request-caregiver">
                            <i class="fas fa-calendar-plus me-2"></i>
                            Request This Caregiver
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="empty-state-staff">
            <div class="empty-icon-staff">
                <i class="fas fa-user-md"></i>
            </div>
            <h4 class="empty-title-staff">No Caregivers Available</h4>
            <p class="empty-text-staff">Currently no caregivers are available. Please check back later.</p>
        </div>
        @endif
    </div>

    <!-- How It Works Section - Modern Design -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="how-it-works-modern">
                <div class="how-it-works-header">
                    <h3 class="how-it-works-title">
                        <i class="fas fa-question-circle me-2"></i>
                        How to Request Services
                    </h3>
                    <p class="how-it-works-subtitle">Simple steps to get professional healthcare</p>
                </div>
                
                <div class="steps-container">
                    <div class="step-item-modern">
                        <div class="step-number">1</div>
                        <div class="step-icon-modern step-search">
                            <i class="fas fa-search"></i>
                        </div>
                        <h5 class="step-title">Browse Staff</h5>
                        <p class="step-description">View available nurses and caregivers with their specializations</p>
                    </div>
                    
                    <div class="step-connector">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                    
                    <div class="step-item-modern">
                        <div class="step-number">2</div>
                        <div class="step-icon-modern step-calendar">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h5 class="step-title">Select Service</h5>
                        <p class="step-description">Choose service type (24h, 12h, 8h, or single visit)</p>
                    </div>
                    
                    <div class="step-connector">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                    
                    <div class="step-item-modern">
                        <div class="step-number">3</div>
                        <div class="step-icon-modern step-assign">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <h5 class="step-title">Get Assigned</h5>
                        <p class="step-description">Our admin will assign the best available staff</p>
                    </div>
                    
                    <div class="step-connector">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                    
                    <div class="step-item-modern">
                        <div class="step-number">4</div>
                        <div class="step-icon-modern step-care">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h5 class="step-title">Receive Care</h5>
                        <p class="step-description">Get professional healthcare at your doorstep</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Comprehensive Modern Styling -->
<style>
/* Page Header */
.modern-page-header-staff {
    background: white;
    padding: 2rem;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.page-title-staff {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.page-subtitle-staff {
    color: #6c757d;
    margin: 0.5rem 0 0 0;
    font-size: 0.95rem;
}

/* Section Header */
.section-header-modern {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: white;
    padding: 1.5rem;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.section-header-icon {
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

.section-header-nurse .section-header-icon {
    background: var(--nurse-gradient);
}

.section-header-caregiver .section-header-icon {
    background: var(--caregiver-gradient);
}

.section-header-content {
    flex: 1;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 0.25rem 0;
}

.section-subtitle {
    font-size: 0.9rem;
    color: #6c757d;
    margin: 0;
}

.count-badge {
    background: #f8f9fa;
    color: #2c3e50;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 700;
    font-size: 1.1rem;
}

/* Staff Card Modern */
.staff-card-modern {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border-top: 4px solid transparent;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.staff-card-modern:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

.staff-card-nurse {
    border-top-color: #3498db;
}

.staff-card-caregiver {
    border-top-color: #11998e;
}

.staff-card-header {
    padding: 1.5rem;
    background: linear-gradient(to bottom, #f8f9fa 0%, white 100%);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.staff-avatar-modern {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    color: white;
    flex-shrink: 0;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.nurse-avatar {
    background: var(--nurse-gradient);
}

.caregiver-avatar {
    background: var(--caregiver-gradient);
}

.staff-info-header {
    flex: 1;
}

.staff-name-modern {
    font-size: 1.25rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 0.5rem 0;
}

.staff-id-badge {
    display: inline-block;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    color: white;
}

.nurse-badge {
    background: var(--nurse-gradient);
}

.caregiver-badge {
    background: var(--caregiver-gradient);
}

.staff-card-body {
    padding: 1.5rem;
    flex: 1;
}

/* Staff Details */
.staff-details-modern {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.detail-row {
    display: flex;
}

.detail-item {
    display: flex;
    align-items: start;
    gap: 0.75rem;
    flex: 1;
}

.detail-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.detail-content {
    flex: 1;
}

.detail-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
    font-weight: 600;
}

.detail-value {
    font-size: 0.9rem;
    color: #2c3e50;
    font-weight: 600;
}

/* Pricing Section */
.pricing-section-modern {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    padding: 1.25rem;
    margin-top: 1.5rem;
}

.pricing-header {
    font-size: 0.85rem;
    font-weight: 700;
    color: #2c3e50;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
}

.pricing-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
}

.pricing-item {
    text-align: center;
    padding: 0.75rem;
    background: white;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.pricing-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.pricing-duration {
    font-size: 0.75rem;
    color: #6c757d;
    font-weight: 600;
    margin-bottom: 0.4rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.pricing-amount {
    font-size: 0.95rem;
    font-weight: 700;
}

.nurse-pricing {
    color: #3498db;
}

.caregiver-pricing {
    color: #11998e;
}

/* Card Footer */
.staff-card-footer {
    padding: 1.25rem 1.5rem;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
}

.btn-request-staff {
    width: 100%;
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    border: none;
    color: white;
    font-size: 0.95rem;
}

.btn-request-nurse {
    background: var(--nurse-gradient);
}

.btn-request-caregiver {
    background: var(--caregiver-gradient);
}

.btn-request-staff:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    color: white;
}

/* Empty State */
.empty-state-staff {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.empty-icon-staff {
    font-size: 5rem;
    color: #dee2e6;
    margin-bottom: 1.5rem;
}

.empty-title-staff {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.empty-text-staff {
    color: #6c757d;
    margin: 0;
}

/* How It Works Section */
.how-it-works-modern {
    background: white;
    border-radius: 16px;
    padding: 2.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.how-it-works-header {
    text-align: center;
    margin-bottom: 3rem;
}

.how-it-works-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.how-it-works-subtitle {
    color: #6c757d;
    font-size: 1rem;
}

.steps-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}

.step-item-modern {
    flex: 1;
    min-width: 180px;
    text-align: center;
    position: relative;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 16px;
    transition: all 0.3s ease;
}

.step-item-modern:hover {
    background: white;
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.step-number {
    position: absolute;
    top: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: var(--primary-gradient);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1rem;
    box-shadow: 0 4px 10px rgba(102, 126, 234, 0.3);
}

.step-icon-modern {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.75rem;
    color: white;
}

.step-search {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.step-calendar {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.step-assign {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.step-care {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.step-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.step-description {
    font-size: 0.85rem;
    color: #6c757d;
    margin: 0;
    line-height: 1.5;
}

.step-connector {
    color: #dee2e6;
    font-size: 1.5rem;
    flex-shrink: 0;
}

/* Mobile Responsiveness */
@media (max-width: 992px) {
    .steps-container {
        flex-direction: column;
    }
    
    .step-connector {
        transform: rotate(90deg);
        margin: 0.5rem 0;
    }
    
    .step-item-modern {
        min-width: 100%;
    }
}

@media (max-width: 768px) {
    .modern-page-header-staff {
        padding: 1.5rem;
    }
    
    .page-title-staff {
        font-size: 1.5rem;
    }
    
    .section-header-modern {
        flex-direction: column;
        text-align: center;
        padding: 1.25rem;
    }
    
    .section-title {
        font-size: 1.25rem;
    }
    
    .staff-card-header {
        flex-direction: column;
        text-align: center;
        padding: 1.25rem;
    }
    
    .staff-avatar-modern {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
    
    .pricing-grid {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    
    .how-it-works-modern {
        padding: 1.5rem;
    }
    
    .how-it-works-title {
        font-size: 1.5rem;
    }
}

@media (max-width: 576px) {
    .page-title-staff {
        font-size: 1.25rem;
    }
    
    .section-header-icon {
        width: 50px;
        height: 50px;
        font-size: 1.25rem;
    }
    
    .staff-card-body {
        padding: 1rem;
    }
    
    .pricing-section-modern {
        padding: 1rem;
    }
}
</style>
@endsection
