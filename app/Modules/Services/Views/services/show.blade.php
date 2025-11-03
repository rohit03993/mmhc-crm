@extends('auth::layout')

@section('title', 'Service Request Details - MMHC CRM')

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
                    <h2 class="text-primary">Service Request Details</h2>
                    <p class="text-muted">View your service request information</p>
                </div>
                <a href="{{ route('services.my-requests') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Requests
                </a>
            </div>

            <div class="row">
                <!-- Service Information -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Service Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Service Type</label>
                                    <div class="fw-bold">{{ $serviceRequest->serviceType->name }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Status</label>
                                    <div>
                                        <span class="badge 
                                            @if($serviceRequest->status === 'pending') bg-warning
                                            @elseif($serviceRequest->status === 'assigned') bg-info
                                            @elseif($serviceRequest->status === 'in_progress') bg-primary
                                            @elseif($serviceRequest->status === 'completed') bg-success
                                            @else bg-secondary
                                            @endif fs-6">
                                            {{ ucfirst(str_replace('_', ' ', $serviceRequest->status)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Duration</label>
                                    <div class="fw-bold">{{ $serviceRequest->duration_days }} days</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Total Amount</label>
                                    <div class="fw-bold text-success fs-5">₹{{ number_format($serviceRequest->total_amount) }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Start Date</label>
                                    <div class="fw-bold">{{ $serviceRequest->start_date->format('M d, Y') }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">End Date</label>
                                    <div class="fw-bold">{{ $serviceRequest->end_date->format('M d, Y') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location & Contact -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Location & Contact</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Service Location</label>
                                    <div class="fw-bold">{{ $serviceRequest->location }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Contact Person</label>
                                    <div class="fw-bold">{{ $serviceRequest->contact_person }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Contact Phone</label>
                                    <div class="fw-bold">{{ $serviceRequest->contact_phone }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Preferred Staff Type</label>
                                    <div class="fw-bold">{{ ucfirst($serviceRequest->preferred_staff_type) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes & Requirements -->
                    @if($serviceRequest->notes || $serviceRequest->special_requirements)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Additional Information</h5>
                        </div>
                        <div class="card-body">
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
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Assigned Staff -->
                    @if($serviceRequest->assignedStaff)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Assigned Staff</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <i class="fas fa-user-{{ $serviceRequest->assignedStaff->isNurse() ? 'nurse' : 'md' }} fa-3x text-{{ $serviceRequest->assignedStaff->isNurse() ? 'info' : 'success' }}"></i>
                            </div>
                            <h6 class="text-center">{{ $serviceRequest->assignedStaff->name }}</h6>
                            <div class="text-center mb-2">
                                <span class="badge bg-{{ $serviceRequest->assignedStaff->isNurse() ? 'info' : 'success' }}">
                                    {{ ucfirst($serviceRequest->assignedStaff->role) }}
                                </span>
                            </div>
                            <div class="text-center text-muted small">
                                ID: {{ $serviceRequest->assignedStaff->unique_id }}
                            </div>
                            @if($serviceRequest->assignedStaff->qualification)
                            <div class="mt-2">
                                <small class="text-muted">Qualification:</small>
                                <div class="fw-bold">{{ $serviceRequest->assignedStaff->qualification }}</div>
                            </div>
                            @endif
                            @if($serviceRequest->assignedStaff->experience)
                            <div class="mt-2">
                                <small class="text-muted">Experience:</small>
                                <div class="fw-bold">{{ $serviceRequest->assignedStaff->experience }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Timeline -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Request Timeline</h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6>Request Submitted</h6>
                                        <small class="text-muted">{{ $serviceRequest->created_at->format('M d, Y g:i A') }}</small>
                                    </div>
                                </div>
                                
                                @if($serviceRequest->assigned_at)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h6>Staff Assigned</h6>
                                        <small class="text-muted">{{ $serviceRequest->assigned_at->format('M d, Y g:i A') }}</small>
                                    </div>
                                </div>
                                @endif
                                
                                @if($serviceRequest->started_at)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-warning"></div>
                                    <div class="timeline-content">
                                        <h6>Service Started</h6>
                                        <small class="text-muted">{{ $serviceRequest->started_at->format('M d, Y g:i A') }}</small>
                                    </div>
                                </div>
                                @endif
                                
                                @if($serviceRequest->completed_at)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6>Service Completed</h6>
                                        <small class="text-muted">{{ $serviceRequest->completed_at->format('M d, Y g:i A') }}</small>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Actions</h5>
                        </div>
                        <div class="card-body">
                            @if($serviceRequest->status === 'pending')
                            <button class="btn btn-warning btn-sm w-100 mb-2" onclick="cancelRequest({{ $serviceRequest->id }})">
                                <i class="fas fa-times me-1"></i>Cancel Request
                            </button>
                            @endif
                            
                            <a href="{{ route('services.my-requests') }}" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="fas fa-arrow-left me-1"></i>Back to Requests
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content h6 {
    margin-bottom: 5px;
    font-size: 0.9rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: -29px;
    top: 15px;
    bottom: 0;
    width: 2px;
    background-color: #dee2e6;
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
