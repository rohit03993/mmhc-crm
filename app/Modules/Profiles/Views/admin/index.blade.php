@extends('auth::layout')

@section('title', 'All Profiles - MMHC CRM')
@section('page-title', 'All Profiles')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>
                    User Profiles
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Profile Status</th>
                                <th>Completion</th>
                                <th>Documents</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($user->profile && $user->profile->avatar_path)
                                                <img src="{{ Storage::url($user->profile->avatar_path) }}" 
                                                     class="rounded-circle me-2" 
                                                     width="40" 
                                                     height="40" 
                                                     alt="Avatar">
                                            @else
                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <strong>{{ $user->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $user->unique_id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge 
                                            @if($user->role == 'admin') bg-danger
                                            @elseif($user->role == 'caregiver') bg-primary
                                            @else bg-success @endif">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($user->profile && $user->profile->isComplete())
                                            <span class="badge bg-success">Complete</span>
                                        @else
                                            <span class="badge bg-warning">Incomplete</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->profile)
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar" 
                                                     style="width: {{ $user->profile->getCompletionPercentage() }}%">
                                                    {{ $user->profile->getCompletionPercentage() }}%
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">No profile</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $user->documents()->count() }} docs
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.profiles.view', $user) }}" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-users fa-2x mb-2"></i><br>
                                        No users found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($users->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
