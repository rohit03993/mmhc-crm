@extends('auth::layout')

@section('title', 'My Profile - MMHC CRM')
@section('page-title', 'My Profile')

@section('content')
<div class="row">
    <!-- Profile Overview Card -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="mb-3">
                    @if($profile->avatar_path)
                        <img src="{{ Storage::url($profile->avatar_path) }}" 
                             class="rounded-circle" 
                             width="120" 
                             height="120" 
                             alt="Profile Picture">
                    @else
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                             style="width: 120px; height: 120px;">
                            <i class="fas fa-user fa-3x text-white"></i>
                        </div>
                    @endif
                </div>
                
                <h4 class="card-title">{{ $user->name }}</h4>
                <p class="text-muted">{{ ucfirst($user->role) }}</p>
                <span class="badge bg-primary fs-6">{{ $user->unique_id }}</span>
                
                <div class="mt-3">
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" 
                             style="width: {{ $profile->getCompletionPercentage() }}%"></div>
                    </div>
                    <small class="text-muted mt-1 d-block">
                        Profile {{ $profile->getCompletionPercentage() }}% Complete
                    </small>
                </div>
                
                <div class="mt-3">
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit me-1"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Details -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user me-2"></i>
                    Profile Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Full Name</label>
                        <p class="fw-bold">{{ $user->name }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Email</label>
                        <p class="fw-bold">{{ $user->email }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Phone</label>
                        <p class="fw-bold">{{ $user->phone ?? 'Not provided' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Date of Birth</label>
                        <p class="fw-bold">{{ $user->date_of_birth ? $user->date_of_birth->format('M d, Y') : 'Not provided' }}</p>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label text-muted">Address</label>
                        <p class="fw-bold">{{ $user->address ?? 'Not provided' }}</p>
                    </div>
                </div>
                
                @if($user->role === 'caregiver')
                    <hr>
                    <h6 class="text-muted mb-3">Professional Information</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Experience</label>
                            <p class="fw-bold">{{ $profile->experience_years ? $profile->experience_years . ' years' : 'Not provided' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Specialization</label>
                            <p class="fw-bold">{{ $profile->specialization ?? 'Not provided' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Availability</label>
                            <span class="badge 
                                @if($profile->availability_status === 'available') bg-success
                                @elseif($profile->availability_status === 'busy') bg-warning
                                @else bg-secondary @endif">
                                {{ ucfirst($profile->availability_status) }}
                            </span>
                        </div>
                    </div>
                @endif
                
                @if($profile->bio)
                    <hr>
                    <h6 class="text-muted mb-3">Bio</h6>
                    <p class="text-muted">{{ $profile->bio }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Documents Section -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-alt me-2"></i>
                    Documents
                </h5>
                <a href="{{ route('documents.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-eye me-1"></i> View All Documents
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center mb-3">
                        <div class="border rounded p-3">
                            <i class="fas fa-certificate fa-2x text-primary mb-2"></i>
                            <h6>Certificates</h6>
                            <span class="badge bg-primary">{{ $user->documents()->type('certificate')->count() }}</span>
                        </div>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <div class="border rounded p-3">
                            <i class="fas fa-id-card fa-2x text-success mb-2"></i>
                            <h6>ID Proofs</h6>
                            <span class="badge bg-success">{{ $user->documents()->type('id_proof')->count() }}</span>
                        </div>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <div class="border rounded p-3">
                            <i class="fas fa-user-md fa-2x text-info mb-2"></i>
                            <h6>Medical License</h6>
                            <span class="badge bg-info">{{ $user->documents()->type('medical_license')->count() }}</span>
                        </div>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <div class="border rounded p-3">
                            <i class="fas fa-shield-alt fa-2x text-warning mb-2"></i>
                            <h6>Insurance</h6>
                            <span class="badge bg-warning">{{ $user->documents()->type('insurance')->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
