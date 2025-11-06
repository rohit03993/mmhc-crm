@extends('auth::layout')

@section('title', 'Request Service - MMHC CRM')

@section('head')
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="d-flex align-items-center justify-content-center mb-3">
                            <!-- MeD Text Logo -->
                            <div class="text-center">
                                <div class="text-3xl font-bold text-gray-800 mb-1">
                                    <span class="text-3xl">M</span><span class="text-2xl italic">e</span><span class="text-3xl">D</span>
                                </div>
                                <div class="text-sm text-gray-600">Miracle Health Care</div>
                            </div>
                        </div>
                        <h2 class="text-primary">Request Nursing Service</h2>
                        <p class="text-muted">Get professional healthcare at your doorstep</p>
                    </div>

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
                        <div class="mb-5">
                            <div class="mb-3">
                                <h5 class="text-primary mb-2">
                                    <i class="fas fa-list-check me-2"></i>Choose Your Service Plan
                                </h5>
                                <p class="text-muted small mb-0">Select the type of healthcare service you need</p>
                            </div>
                            <div class="row g-3">
                                @foreach($serviceTypes as $serviceType)
                                <div class="col-12 col-md-6 col-lg-3">
                                    <div class="service-type-card-modern" data-service-type="{{ $serviceType->id }}">
                                        <div class="service-type-header">
                                            <div class="service-type-icon">
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
                                            <div class="service-type-badge">
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
                                        <div class="service-type-body">
                                            <h6 class="service-type-title">{{ $serviceType->name }}</h6>
                                            <p class="service-type-desc">{{ $serviceType->description }}</p>
                                            
                                            <div class="service-type-details">
                                                <div class="detail-row">
                                                    <span class="detail-label">
                                                        <i class="fas fa-clock me-1"></i>Duration:
                                                    </span>
                                                    <span class="detail-value">
                                                        @if($serviceType->duration_hours == 1)
                                                            1 Hour
                                                        @else
                                                            {{ $serviceType->duration_hours }} Hours/Day
                                                        @endif
                                                    </span>
                                                </div>
                                                
                                                <div class="detail-row pricing-row">
                                                    <span class="detail-label">
                                                        <i class="fas fa-rupee-sign me-1"></i>Price:
                                                    </span>
                                                    <span class="detail-value price-value">
                                                        @if($serviceType->duration_hours == 1)
                                                            ₹{{ number_format($serviceType->patient_charge) }}/visit
                                                        @else
                                                            ₹{{ number_format($serviceType->patient_charge) }}/day
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="service-type-footer">
                                                <div class="selection-indicator">
                                                    <i class="fas fa-check-circle"></i>
                                                    <span>Click to select</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <input type="hidden" name="service_type_id" id="selected_service_type" required>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <strong>Tip:</strong> Select the service type that best matches your care needs. You can choose the duration in days on the next step.
                                </small>
                            </div>
                        </div>

                        <!-- Service Details -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="preferred_staff_type" class="form-label">Preferred Staff Type</label>
                                    <select class="form-control" id="preferred_staff_type" name="preferred_staff_type" required>
                                        <option value="">Select Staff Type</option>
                                        <option value="nurse" {{ old('preferred_staff_type', $selectedStaffType ?? '') === 'nurse' ? 'selected' : '' }}>Nurse (Licensed Professional) - Higher Quality</option>
                                        <option value="caregiver" {{ old('preferred_staff_type', $selectedStaffType ?? '') === 'caregiver' ? 'selected' : '' }}>Caregiver (General Support) - Cost Effective</option>
                                        <option value="any" {{ old('preferred_staff_type', $selectedStaffType ?? '') === 'any' || (!isset($selectedStaffType) && old('preferred_staff_type') === '') ? 'selected' : '' }}>Any Available Staff</option>
                                    </select>
                                </div>
                                
                                @if($selectedStaff)
                                <!-- Selected Staff Info -->
                                <div class="alert alert-info mb-3">
                                    <h6 class="alert-heading mb-2">
                                        <i class="fas fa-user-md me-2"></i>Selected Staff Member
                                    </h6>
                                    <p class="mb-1"><strong>{{ $selectedStaff->name }}</strong></p>
                                    <p class="mb-1 small">
                                        <span class="badge bg-{{ $selectedStaff->isNurse() ? 'primary' : 'success' }}">
                                            {{ $selectedStaff->isNurse() ? 'Nurse' : 'Caregiver' }}
                                        </span>
                                        @if($selectedStaff->qualification)
                                            | {{ $selectedStaff->qualification }}
                                        @endif
                                        @if($selectedStaff->experience)
                                            | {{ $selectedStaff->experience }} years exp.
                                        @endif
                                    </p>
                                    <input type="hidden" name="preferred_staff_id" value="{{ $selectedStaff->id }}">
                                </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="duration_days" class="form-label">Duration (Days)</label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="duration_days" 
                                           name="duration_days" 
                                           min="1" 
                                           value="{{ old('duration_days', 1) }}" 
                                           required>
                                    <div class="form-text">Select the number of days for service</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="start_date" 
                                           name="start_date" 
                                           min="{{ date('Y-m-d') }}" 
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="end_date" 
                                           name="end_date" 
                                           readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Location Details -->
                        <div class="mb-3">
                            <label for="location" class="form-label">Service Location</label>
                            <textarea class="form-control" 
                                      id="location" 
                                      name="location" 
                                      rows="3" 
                                      placeholder="Enter complete address where service is needed" 
                                      required>{{ old('location') }}</textarea>
                        </div>

                        <!-- Contact Details -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contact_person" class="form-label">Contact Person</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="contact_person" 
                                           name="contact_person" 
                                           value="{{ old('contact_person', $user->name) }}" 
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contact_phone" class="form-label">Contact Phone</label>
                                    <input type="tel" 
                                           class="form-control" 
                                           id="contact_phone" 
                                           name="contact_phone" 
                                           value="{{ old('contact_phone', $user->phone) }}" 
                                           pattern="[0-9]{10}"
                                           maxlength="10"
                                           required>
                                    <div class="form-text">Enter exactly 10 digits</div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      placeholder="Any specific requirements or notes">{{ old('notes') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="special_requirements" class="form-label">Special Requirements</label>
                            <textarea class="form-control" 
                                      id="special_requirements" 
                                      name="special_requirements" 
                                      rows="3" 
                                      placeholder="Medical conditions, special care needs, etc.">{{ old('special_requirements') }}</textarea>
                        </div>

                        <!-- Cost Summary -->
                        <div class="alert alert-info">
                            <h6 class="alert-heading">Cost Summary</h6>
                            <div id="cost_summary">
                                <p class="mb-1">Service: <span id="selected_service_name">-</span></p>
                                <p class="mb-1">Duration: <span id="selected_duration">1</span> days</p>
                                <p class="mb-1">Daily Rate: ₹<span id="daily_rate">-</span></p>
                                <hr>
                                <p class="mb-0 fw-bold">Total Amount: ₹<span id="total_amount">-</span></p>
                                <small class="text-muted">Payment can be processed after service assignment</small>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>
                                Submit Service Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Modern Service Type Cards */
.service-type-card-modern {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    height: 100%;
    overflow: hidden;
    position: relative;
}

.service-type-card-modern:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border-color: #667eea;
}

.service-type-card-modern.selected {
    border-color: #667eea;
    border-width: 3px;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    background: linear-gradient(to bottom, #f8f9ff 0%, #ffffff 100%);
}

.service-type-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 1.5rem;
    text-align: center;
    color: white;
    position: relative;
}

.service-type-card-modern:nth-child(2) .service-type-header {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.service-type-card-modern:nth-child(3) .service-type-header {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.service-type-card-modern:nth-child(4) .service-type-header {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.service-type-icon {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.service-type-badge {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
    margin-top: 0.5rem;
}

.service-type-body {
    padding: 1.5rem;
}

.service-type-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.service-type-desc {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 1rem;
    min-height: 40px;
}

.service-type-details {
    margin: 1rem 0;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 12px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.detail-row:last-child {
    margin-bottom: 0;
}

.detail-label {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 500;
}

.detail-value {
    font-size: 0.9rem;
    color: #2c3e50;
    font-weight: 600;
}

.pricing-row {
    padding-top: 0.75rem;
    border-top: 1px solid #dee2e6;
    margin-top: 0.75rem;
}

.price-value {
    font-size: 1.1rem;
    color: #28a745;
    font-weight: 700;
}

.service-type-footer {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}

.selection-indicator {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    color: #6c757d;
    font-size: 0.8rem;
    font-weight: 500;
}

.service-type-card-modern.selected .selection-indicator {
    color: #667eea;
    font-weight: 600;
}

.service-type-card-modern.selected .selection-indicator i {
    display: inline-block;
}

.service-type-card-modern:not(.selected) .selection-indicator i {
    display: none;
}

@media (max-width: 768px) {
    .service-type-card-modern {
        margin-bottom: 1rem;
    }
    
    .service-type-body {
        padding: 1rem;
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
            const costSummary = document.querySelector('.alert-info');
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
