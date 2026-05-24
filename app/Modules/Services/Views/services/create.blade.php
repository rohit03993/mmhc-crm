@extends('auth::layout')

@section('title', 'Request Service - MMHC CRM')

@section('head')
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
@endsection

@section('content')
<!-- Mobile App View for Service Request -->
<div class="mobile-app-container">
    <!-- App Header (Mobile Only) -->
    <div class="app-header-mobile d-md-none">
        <div class="app-header-content">
            <div class="app-header-left">
                <a href="{{ route('services.index') }}" class="app-back-btn">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <div class="app-header-title">Request Service</div>
                    <div class="app-header-subtitle">Get healthcare at home</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="app-content">
        <!-- Desktop Header -->
        <div class="d-none d-md-block mb-4">
            <h2 class="text-primary">Request Nursing Service</h2>
            <p class="text-muted">Get professional healthcare at your doorstep</p>
        </div>
        
        <div class="app-form-container">

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('services.store') }}" id="serviceRequestForm">
                        @csrf
                        
                        <!-- Service Type Selection -->
                        <div class="app-form-section">
                            <div class="app-section-header mb-3">
                                <h3 class="app-section-title">
                                    <i class="fas fa-list-check me-2"></i>Choose Service Plan
                                </h3>
                                <p class="app-section-subtitle">Select the type of healthcare service</p>
                            </div>
                            <div class="app-service-type-grid">
                                @foreach($serviceTypes as $serviceType)
                                <div class="app-service-type-card">
                                    <div class="app-service-type-item" data-service-type="{{ $serviceType->id }}">
                                        <div class="app-service-type-header service-{{ $serviceType->duration_hours }}">
                                            <div class="app-service-type-icon">
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
                                            <div class="app-service-type-badge">
                                                @if($serviceType->duration_hours == 24)
                                                    Full Day
                                                @elseif($serviceType->duration_hours == 12)
                                                    Half Day
                                                @elseif($serviceType->duration_hours == 8)
                                                    Standard
                                                @else
                                                    Quick
                                                @endif
                                            </div>
                                        </div>
                                        <div class="app-service-type-body">
                                            <h5 class="app-service-type-title">{{ $serviceType->name }}</h5>
                                            <p class="app-service-type-desc">{{ $serviceType->description }}</p>
                                            
                                            <div class="app-service-type-info">
                                                <div class="app-info-item">
                                                    <i class="fas fa-clock"></i>
                                                    <span>
                                                        @if($serviceType->duration_hours == 1)
                                                            1 Hour
                                                        @else
                                                            {{ $serviceType->duration_hours }}h/Day
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="app-info-item price">
                                                    <i class="fas fa-rupee-sign"></i>
                                                    <span>
                                                        @if($serviceType->duration_hours == 1)
                                                            ₹{{ number_format($serviceType->patient_charge) }}/visit
                                                        @else
                                                            ₹{{ number_format($serviceType->patient_charge) }}/day
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="app-selection-indicator">
                                                <i class="fas fa-check-circle"></i>
                                                <span>Tap to select</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <input type="hidden" name="service_type_id" id="selected_service_type" required>
                            <div class="app-form-tip">
                                <i class="fas fa-info-circle"></i>
                                <span>Select service type that matches your care needs</span>
                            </div>
                        </div>

                        <!-- Service Details -->
                        <div class="app-form-section">
                            <div class="app-section-header mb-3">
                                <h3 class="app-section-title">Service Details</h3>
                            </div>
                            
                            <div class="app-form-group">
                                <label for="preferred_staff_type" class="app-form-label">Preferred Staff Type</label>
                                <select class="app-form-select" id="preferred_staff_type" name="preferred_staff_type" required>
                                        <option value="">Select Staff Type</option>
                                        <option value="nurse" {{ old('preferred_staff_type', $selectedStaffType ?? '') === 'nurse' ? 'selected' : '' }}>Nurse (Licensed Professional) - Higher Quality</option>
                                        <option value="caregiver" {{ old('preferred_staff_type', $selectedStaffType ?? '') === 'caregiver' ? 'selected' : '' }}>Caregiver (General Support) - Cost Effective</option>
                                        <option value="any" {{ old('preferred_staff_type', $selectedStaffType ?? '') === 'any' || (!isset($selectedStaffType) && old('preferred_staff_type') === '') ? 'selected' : '' }}>Any Available Staff</option>
                                    </select>
                                </div>
                                
                                @if($selectedStaff)
                                <!-- Selected Staff Info -->
                                <div class="app-selected-staff">
                                    <div class="app-selected-staff-header">
                                        <i class="fas fa-user-md"></i>
                                        <span>Selected Staff</span>
                                    </div>
                                    <div class="app-selected-staff-body">
                                        <h6>{{ $selectedStaff->name }}</h6>
                                        <div class="app-staff-badges">
                                            <span class="app-badge app-badge-{{ $selectedStaff->isNurse() ? 'primary' : 'success' }}">
                                                {{ $selectedStaff->isNurse() ? 'Nurse' : 'Caregiver' }}
                                            </span>
                                            @if($selectedStaff->qualification)
                                                <span class="app-badge app-badge-secondary">{{ $selectedStaff->qualification }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <input type="hidden" name="preferred_staff_id" value="{{ $selectedStaff->id }}">
                                </div>
                                @endif
                            </div>
                            
                            <div class="app-form-group">
                                <label for="duration_days" class="app-form-label">Duration (Days)</label>
                                <input type="number" 
                                       class="app-form-input" 
                                       id="duration_days" 
                                       name="duration_days" 
                                       min="1" 
                                       value="{{ old('duration_days', 1) }}" 
                                       required>
                                <div class="app-form-help">Number of days for service</div>
                            </div>
                        </div>

                        <div class="app-form-section">
                            <div class="app-form-group">
                                <label for="start_date" class="app-form-label">Start Date</label>
                                <input type="date" 
                                       class="app-form-input" 
                                       id="start_date" 
                                       name="start_date" 
                                       min="{{ date('Y-m-d') }}" 
                                       required>
                            </div>
                            
                            <div class="app-form-group">
                                <label for="end_date" class="app-form-label">End Date</label>
                                <input type="date" 
                                       class="app-form-input" 
                                       id="end_date" 
                                       name="end_date" 
                                       readonly>
                            </div>
                        </div>

                        <!-- Location Details -->
                        <div class="app-form-section">
                            <div class="app-form-group">
                                <label for="location" class="app-form-label">Service Location</label>
                                <textarea class="app-form-input" 
                                          id="location" 
                                          name="location" 
                                          rows="3" 
                                          placeholder="Enter complete address where service is needed" 
                                          required>{{ old('location') }}</textarea>
                            </div>
                        </div>

                        <!-- Contact Details -->
                        <div class="app-form-section">
                            <div class="app-form-group">
                                <label for="contact_person" class="app-form-label">Contact Person</label>
                                <input type="text" 
                                       class="app-form-input" 
                                       id="contact_person" 
                                       name="contact_person" 
                                       value="{{ old('contact_person', $user->name) }}" 
                                       required>
                            </div>
                            
                            <div class="app-form-group">
                                <label for="contact_phone" class="app-form-label">Contact Phone</label>
                                <input type="tel" 
                                       class="app-form-input" 
                                       id="contact_phone" 
                                       name="contact_phone" 
                                       value="{{ old('contact_phone', $user->phone) }}" 
                                       pattern="[0-9]{10}"
                                       maxlength="10"
                                       required>
                                <div class="app-form-help">Enter exactly 10 digits</div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="app-form-section">
                            <div class="app-form-group">
                                <label for="notes" class="app-form-label">Additional Notes</label>
                                <textarea class="app-form-input" 
                                          id="notes" 
                                          name="notes" 
                                          rows="3" 
                                          placeholder="Any specific requirements or notes">{{ old('notes') }}</textarea>
                            </div>
                            
                            <div class="app-form-group">
                                <label for="special_requirements" class="app-form-label">Special Requirements</label>
                                <textarea class="app-form-input" 
                                          id="special_requirements" 
                                          name="special_requirements" 
                                          rows="3" 
                                          placeholder="Medical conditions, special care needs, etc.">{{ old('special_requirements') }}</textarea>
                            </div>
                        </div>

                        <!-- Cost Summary -->
                        <div class="app-cost-summary">
                            <div class="app-cost-header">
                                <i class="fas fa-calculator"></i>
                                <span>Cost Summary</span>
                            </div>
                            <div id="cost_summary" class="app-cost-body">
                                <div class="app-cost-item">
                                    <span>Service:</span>
                                    <span id="selected_service_name">-</span>
                                </div>
                                <div class="app-cost-item">
                                    <span>Duration:</span>
                                    <span id="selected_duration">1</span> days
                                </div>
                                <div class="app-cost-item">
                                    <span>Daily Rate:</span>
                                    <span>₹<span id="daily_rate">-</span></span>
                                </div>
                                <div class="app-cost-total">
                                    <span>Total Amount:</span>
                                    <span>₹<span id="total_amount">-</span></span>
                                </div>
                                <div class="app-cost-note">
                                    <small>Payment after service assignment</small>
                                </div>
                            </div>
                        </div>

                        <div class="app-form-submit">
                            <button type="submit" class="app-btn-submit">
                                <i class="fas fa-paper-plane me-2"></i>
                                Submit Service Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bottom Navigation -->
    </div>
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

/* App Content */
.app-content {
    padding: 16px;
    padding-bottom: 90px !important;
    margin-top: 0;
}

.app-form-container {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.app-form-section {
    margin-bottom: 24px;
}

.app-section-header {
    margin-bottom: 12px;
}

.app-section-title {
    font-size: 1rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 4px 0;
}

.app-section-subtitle {
    font-size: 0.85rem;
    color: #6c757d;
    margin: 0;
}

/* Service Type Grid */
.app-service-type-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

.app-service-type-item {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
    overflow: hidden;
}

.app-service-type-item:active {
    transform: scale(0.98);
}

.app-service-type-item.selected {
    border-color: #667eea;
    border-width: 3px;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    background: linear-gradient(to bottom, #f8f9ff 0%, #ffffff 100%);
}

.app-service-type-header {
    padding: 16px;
    text-align: center;
    color: white;
}

.app-service-type-header.service-24 {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.app-service-type-header.service-12 {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.app-service-type-header.service-8 {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.app-service-type-header.service-1 {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.app-service-type-icon {
    font-size: 2rem;
    margin-bottom: 8px;
}

.app-service-type-badge {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

.app-service-type-body {
    padding: 16px;
}

.app-service-type-title {
    font-size: 1rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 8px 0;
}

.app-service-type-desc {
    font-size: 0.85rem;
    color: #6c757d;
    margin: 0 0 12px 0;
    line-height: 1.4;
}

.app-service-type-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 12px;
    margin-bottom: 12px;
}

.app-info-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
    color: #6c757d;
}

.app-info-item i {
    color: #667eea;
    width: 20px;
    text-align: center;
}

.app-info-item.price {
    color: #28a745;
    font-weight: 600;
}

.app-info-item.price i {
    color: #28a745;
}

.app-selection-indicator {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    color: #6c757d;
    font-size: 0.8rem;
    font-weight: 500;
}

.app-service-type-item.selected .app-selection-indicator {
    color: #667eea;
    font-weight: 600;
}

.app-service-type-item.selected .app-selection-indicator i {
    display: inline-block;
}

.app-service-type-item:not(.selected) .app-selection-indicator i {
    display: none;
}

.app-form-tip {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px;
    background: #e7f3ff;
    border-radius: 12px;
    font-size: 0.85rem;
    color: #0066cc;
    margin-top: 12px;
}

.app-form-tip i {
    color: #0066cc;
}

/* Form Elements */
.app-form-group {
    margin-bottom: 16px;
}

.app-form-label {
    display: block;
    font-size: 0.9rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
}

.app-form-input,
.app-form-select {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    font-size: 0.95rem;
    background: white;
    color: #2c3e50;
    transition: border-color 0.2s ease;
}

.app-form-input:focus,
.app-form-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.app-form-help {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 4px;
}

/* Selected Staff */
.app-selected-staff {
    background: #e7f3ff;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 16px;
}

.app-selected-staff-header {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    color: #0066cc;
    margin-bottom: 12px;
}

.app-selected-staff-body h6 {
    font-size: 1rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 8px 0;
}

.app-staff-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.app-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    color: white;
}

.app-badge-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.app-badge-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.app-badge-secondary {
    background: #6c757d;
}

/* Cost Summary */
.app-cost-summary {
    background: linear-gradient(135deg, #e7f3ff 0%, #f0f8ff 100%);
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 24px;
}

.app-cost-header {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 12px;
}

.app-cost-body {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.app-cost-item {
    display: flex;
    justify-content: space-between;
    font-size: 0.9rem;
    color: #6c757d;
}

.app-cost-total {
    display: flex;
    justify-content: space-between;
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
    padding-top: 12px;
    border-top: 1px solid #dee2e6;
    margin-top: 8px;
}

.app-cost-note {
    margin-top: 8px;
    font-size: 0.75rem;
    color: #6c757d;
}

/* Submit Button */
.app-form-submit {
    margin-top: 24px;
}

.app-btn-submit {
    width: 100%;
    padding: 16px;
    border-radius: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.app-btn-submit:active {
    transform: scale(0.98);
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
    
    .app-form-container {
        padding: 32px;
    }
    
    .app-service-type-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
}

/* Mobile Optimizations */
@media (max-width: 576px) {
    .app-service-type-icon {
        font-size: 1.5rem;
    }
    
    .app-service-type-title {
        font-size: 0.9rem;
    }
    
    .app-service-type-desc {
        font-size: 0.8rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const serviceTypes = @json($serviceTypes);
    const serviceCards = document.querySelectorAll('.service-type-card-modern');
    const selectedServiceTypeInput = document.getElementById('selected_service_type');
    const durationInput = document.getElementById('duration_days');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    // Service type selection
    const serviceCards = document.querySelectorAll('.app-service-type-item');
    serviceCards.forEach(card => {
        card.addEventListener('click', function() {
            // Remove selected class from all cards
            serviceCards.forEach(c => c.classList.remove('selected'));
            // Add selected class to clicked card
            this.classList.add('selected');
            
            const serviceTypeId = this.dataset.serviceType;
            selectedServiceTypeInput.value = serviceTypeId;
            
            // Update cost summary
            updateCostSummary();
            
            // Scroll to cost summary if needed
            const costSummary = document.querySelector('.app-cost-summary');
            if (costSummary) {
                costSummary.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });
    });
    
    // Duration change
    durationInput.addEventListener('input', function() {
        updateEndDate();
        updateCostSummary();
    });
    
    // Start date change
    startDateInput.addEventListener('change', function() {
        updateEndDate();
    });
    
    function updateEndDate() {
        const startDate = new Date(startDateInput.value);
        const duration = parseInt(durationInput.value);
        
        if (startDate && duration) {
            const endDate = new Date(startDate);
            endDate.setDate(endDate.getDate() + duration - 1);
            endDateInput.value = endDate.toISOString().split('T')[0];
        }
    }
    
    function updateCostSummary() {
        const selectedServiceTypeId = selectedServiceTypeInput.value;
        const duration = parseInt(durationInput.value);
        
        if (selectedServiceTypeId && duration) {
            const serviceType = serviceTypes.find(st => st.id == selectedServiceTypeId);
            if (serviceType) {
                document.getElementById('selected_service_name').textContent = serviceType.name;
                document.getElementById('selected_duration').textContent = duration;
                document.getElementById('daily_rate').textContent = serviceType.patient_charge;
                document.getElementById('total_amount').textContent = (serviceType.patient_charge * duration).toLocaleString();
            }
        }
    }
    
    // Set minimum start date to today
    startDateInput.min = new Date().toISOString().split('T')[0];
    startDateInput.value = new Date().toISOString().split('T')[0];
    
    // Update initial duration display
    const initialDuration = parseInt(durationInput.value) || 1;
    document.getElementById('selected_duration').textContent = initialDuration;
    
    updateEndDate();
    updateCostSummary();
});
</script>
@endsection
