@extends('auth::layout')

@section('title', 'View Profile - MMHC CRM')
@section('page-title', 'View Profile')

@section('content')
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-3">
                    @if($profile && $profile->avatar_path)
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
                
                @if($profile)
                    <div class="mt-3">
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" 
                                 style="width: {{ $profile->getCompletionPercentage() }}%"></div>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            Profile {{ $profile->getCompletionPercentage() }}% Complete
                        </small>
                    </div>
                @endif
            </div>
        </div>
    </div>

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
                        <p class="fw-bold">{{ $user->getFormattedDateOfBirth() }}</p>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label text-muted">Address</label>
                        <p class="fw-bold">{{ $user->address ?? 'Not provided' }}</p>
                    </div>
                </div>
                
                @if($profile)
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
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This user hasn't created a profile yet.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Documents Section -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-alt me-2"></i>
                    Documents ({{ $user->documents()->count() }})
                </h5>
            </div>
            <div class="card-body">
                @if($user->documents()->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Document</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Uploaded</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->documents as $document)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="{{ $document->file_icon }} me-2"></i>
                                                <div>
                                                    <strong>{{ $document->document_name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $document->original_name }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ $document->document_type_display }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge 
                                                @if($document->status == 'verified') bg-success
                                                @elseif($document->status == 'rejected') bg-danger
                                                @else bg-warning @endif">
                                                {{ $document->status_display }}
                                            </span>
                                        </td>
                                        <td>{{ $document->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('documents.download', $document) }}" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-file-alt fa-2x text-muted mb-2"></i>
                        <h6 class="text-muted">No documents uploaded</h6>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('admin.profiles') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>
        Back to All Profiles
    </a>
</div>
@endsection
