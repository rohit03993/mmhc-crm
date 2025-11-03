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
                        <div class="mb-4">
                            <h5 class="text-primary mb-3">Select Service Type</h5>
                            <div class="row">
                                @foreach($serviceTypes as $serviceType)
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <div class="card service-type-card" data-service-type="{{ $serviceType->id }}">
                                        <div class="card-body text-center">
                                            <div class="service-icon mb-2">
                                                @if($serviceType->duration_hours == 24)
                                                    <i class="fas fa-clock fa-2x text-primary"></i>
                                                @elseif($serviceType->duration_hours == 12)
                                                    <i class="fas fa-sun fa-2x text-warning"></i>
                                                @elseif($serviceType->duration_hours == 8)
                                                    <i class="fas fa-briefcase fa-2x text-info"></i>
                                                @else
                                                    <i class="fas fa-user-md fa-2x text-success"></i>
                                                @endif
                                            </div>
                                            <h6 class="card-title">{{ $serviceType->name }}</h6>
                                            <p class="text-muted small">{{ $serviceType->description }}</p>
                                            <div class="pricing">
                                                <div class="text-primary fw-bold">₹{{ number_format($serviceType->patient_charge) }}/day</div>
                                                <small class="text-muted">{{ $serviceType->duration_hours }} hours</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <input type="hidden" name="service_type_id" id="selected_service_type" required>
                        </div>

                        <!-- Service Details -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="preferred_staff_type" class="form-label">Preferred Staff Type</label>
                                    <select class="form-control" id="preferred_staff_type" name="preferred_staff_type" required>
                                        <option value="">Select Staff Type</option>
                                        <option value="nurse">Nurse (Licensed Professional) - Higher Quality</option>
                                        <option value="caregiver">Caregiver (General Support) - Cost Effective</option>
                                        <option value="any">Any Available Staff</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="duration_days" class="form-label">Duration (Days)</label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="duration_days" 
                                           name="duration_days" 
                                           min="7" 
                                           value="7" 
                                           required>
                                    <div class="form-text">Minimum 7 days required</div>
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
                                <p class="mb-1">Duration: <span id="selected_duration">7</span> days</p>
                                <p class="mb-1">Daily Rate: ₹<span id="daily_rate">-</span></p>
                                <hr>
                                <p class="mb-0 fw-bold">Total Amount: ₹<span id="total_amount">-</span></p>
                                <small class="text-muted">Payment required in advance for minimum 7 days</small>
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
.service-type-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.service-type-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.service-type-card.selected {
    border-color: #007bff;
    background-color: #f8f9fa;
}

.service-type-card .pricing {
    margin-top: 10px;
}

@media (max-width: 768px) {
    .service-type-card {
        margin-bottom: 15px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const serviceTypes = @json($serviceTypes);
    const serviceCards = document.querySelectorAll('.service-type-card');
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
    updateEndDate();
});
</script>
@endsection
