@extends('auth::layout')

@section('title', 'Assign Staff - MMHC CRM')

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
                    <h2 class="text-primary">Assign Staff</h2>
                    <p class="text-muted">Assign healthcare staff to service request</p>
                </div>
                <a href="{{ route('admin.service-requests') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Requests
                </a>
            </div>

            <div class="row">
                <!-- Service Request Details -->
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Service Request Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-muted">Patient</label>
                                <div class="fw-bold">{{ $serviceRequest->patient->name }}</div>
                                <small class="text-muted">{{ $serviceRequest->patient->unique_id }}</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted">Service Type</label>
                                <div class="fw-bold">{{ $serviceRequest->serviceType->name }}</div>
                                <small class="text-muted">{{ $serviceRequest->serviceType->duration_hours }}h service</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted">Duration</label>
                                <div class="fw-bold">{{ $serviceRequest->duration_days }} days</div>
                                <small class="text-muted">{{ $serviceRequest->start_date->format('M d, Y') }} - {{ $serviceRequest->end_date->format('M d, Y') }}</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted">Total Amount</label>
                                <div class="fw-bold text-success fs-5">₹{{ number_format($serviceRequest->total_amount) }}</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted">Location</label>
                                <div class="fw-bold">{{ $serviceRequest->location }}</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted">Preferred Staff Type</label>
                                <div class="fw-bold">{{ ucfirst($serviceRequest->preferred_staff_type) }}</div>
                            </div>
                            
                            @if($serviceRequest->preferred_staff_id && $serviceRequest->preferredStaff)
                            <div class="mb-3">
                                <label class="form-label text-muted">Patient's Preferred Staff Member</label>
                                <div class="alert alert-info mb-0">
                                    <div class="fw-bold">{{ $serviceRequest->preferredStaff->name }}</div>
                                    <small class="text-muted">
                                        {{ $serviceRequest->preferredStaff->unique_id }} | 
                                        {{ $serviceRequest->preferredStaff->qualification ?? 'N/A' }} |
                                        {{ $serviceRequest->preferredStaff->experience ?? 'N/A' }} exp.
                                    </small>
                                    <div class="mt-2">
                                        <span class="badge bg-{{ $serviceRequest->preferredStaff->isNurse() ? 'primary' : 'success' }}">
                                            {{ $serviceRequest->preferredStaff->isNurse() ? 'Nurse' : 'Caregiver' }}
                                        </span>
                                    </div>
                                    <div class="mt-2 small text-muted">
                                        <i class="fas fa-info-circle"></i> Patient selected this staff member. You can assign them or choose another available staff.
                                    </div>
                                </div>
                            </div>
                            @endif
                            
                            @if($serviceRequest->notes)
                            <div class="mb-3">
                                <label class="form-label text-muted">Notes</label>
                                <div class="fw-bold">{{ $serviceRequest->notes }}</div>
                            </div>
                            @endif
                            
                            @if($serviceRequest->special_requirements)
                            <div class="mb-3">
                                <label class="form-label text-muted">Special Requirements</label>
                                <div class="fw-bold">{{ $serviceRequest->special_requirements }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Staff Assignment Form -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Select Staff Member</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.service-requests.assign.post', $serviceRequest) }}">
                                @csrf
                                
                                <div class="mb-4">
                                    <label for="assigned_staff_id" class="form-label">Choose Staff Member</label>
                                    <select class="form-control" id="assigned_staff_id" name="assigned_staff_id" required>
                                        <option value="">Select a staff member</option>
                                        
                                        <!-- Nurses Section -->
                                        <optgroup label="👩‍⚕️ Licensed Nurses">
                                            @foreach($availableStaff->where('role', 'nurse') as $nurse)
                                            <option value="{{ $nurse->id }}" 
                                                    data-role="nurse"
                                                    data-qualification="{{ $nurse->qualification }}"
                                                    data-experience="{{ $nurse->experience }}"
                                                    {{ $serviceRequest->preferred_staff_id == $nurse->id ? 'selected' : '' }}>
                                                {{ $nurse->name }} ({{ $nurse->unique_id }}) - {{ $nurse->qualification }} - {{ $nurse->experience }}
                                                @if($serviceRequest->preferred_staff_id == $nurse->id) ⭐ Patient's Choice @endif
                                            </option>
                                            @endforeach
                                        </optgroup>
                                        
                                        <!-- Caregivers Section -->
                                        <optgroup label="👨‍⚕️ Caregivers">
                                            @foreach($availableStaff->where('role', 'caregiver') as $caregiver)
                                            <option value="{{ $caregiver->id }}" 
                                                    data-role="caregiver"
                                                    data-qualification="{{ $caregiver->qualification }}"
                                                    data-experience="{{ $caregiver->experience }}"
                                                    {{ $serviceRequest->preferred_staff_id == $caregiver->id ? 'selected' : '' }}>
                                                {{ $caregiver->name }} ({{ $caregiver->unique_id }}) - {{ $caregiver->qualification }} - {{ $caregiver->experience }}
                                                @if($serviceRequest->preferred_staff_id == $caregiver->id) ⭐ Patient's Choice @endif
                                            </option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                    @if($serviceRequest->preferredStaff)
                                    <div class="form-text text-info mt-2">
                                        <i class="fas fa-info-circle"></i> Patient has selected <strong>{{ $serviceRequest->preferredStaff->name }}</strong> as their preferred staff member. This selection is pre-selected above.
                                    </div>
                                    @endif
                                </div>

                                <!-- Selected Staff Details -->
                                <div id="selected-staff-details" class="mb-4" style="display: none;">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6>Selected Staff Details</h6>
                                            <div id="staff-info"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pricing Information -->
                                <div class="mb-4">
                                    <h6>Pricing Information</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="text-center p-3 border rounded">
                                                <div class="fw-bold">24h Care</div>
                                                <div class="text-success" id="price-24h">₹{{ $serviceRequest->serviceType->patient_charge }}</div>
                                                <small class="text-muted">Patient pays</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center p-3 border rounded">
                                                <div class="fw-bold">12h Care</div>
                                                <div class="text-success">₹{{ $serviceRequest->serviceType->patient_charge * 0.6 }}</div>
                                                <small class="text-muted">Patient pays</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center p-3 border rounded">
                                                <div class="fw-bold">8h Care</div>
                                                <div class="text-success">₹{{ $serviceRequest->serviceType->patient_charge * 0.4 }}</div>
                                                <small class="text-muted">Patient pays</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-user-check me-2"></i>Assign Staff
                                    </button>
                                    <a href="{{ route('admin.service-requests') }}" class="btn btn-outline-secondary">
                                        Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const staffSelect = document.getElementById('assigned_staff_id');
    const staffDetails = document.getElementById('selected-staff-details');
    const staffInfo = document.getElementById('staff-info');
    
    staffSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (selectedOption.value) {
            const role = selectedOption.dataset.role;
            const qualification = selectedOption.dataset.qualification;
            const experience = selectedOption.dataset.experience;
            
            staffInfo.innerHTML = `
                <div class="row">
                    <div class="col-6">
                        <small class="text-muted">Role:</small>
                        <div class="fw-bold">${role === 'nurse' ? 'Licensed Nurse' : 'Caregiver'}</div>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Qualification:</small>
                        <div class="fw-bold">${qualification}</div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-6">
                        <small class="text-muted">Experience:</small>
                        <div class="fw-bold">${experience}</div>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Staff Type:</small>
                        <div class="fw-bold text-${role === 'nurse' ? 'info' : 'success'}">${role === 'nurse' ? 'Nurse' : 'Caregiver'}</div>
                    </div>
                </div>
            `;
            
            staffDetails.style.display = 'block';
        } else {
            staffDetails.style.display = 'none';
        }
    });
});
</script>
@endsection
