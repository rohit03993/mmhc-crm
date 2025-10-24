@extends('auth::layout')

@section('title', 'Edit Profile - MMHC CRM')
@section('page-title', 'Edit Profile')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-edit me-2"></i>
                    Edit Profile Information
                </h5>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $user->name) }}" 
                                   required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone', $user->phone) }}" 
                                   required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               value="{{ $user->email }}" 
                               readonly>
                        <small class="text-muted">Email cannot be changed</small>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  id="address" 
                                  name="address" 
                                  rows="3">{{ old('address', $user->address) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                        <input type="date" 
                               class="form-control @error('date_of_birth') is-invalid @enderror" 
                               id="date_of_birth" 
                               name="date_of_birth" 
                               value="{{ old('date_of_birth', $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '') }}">
                    </div>

                    @if($user->role === 'caregiver')
                        <hr>
                        <h6 class="text-muted mb-3">Professional Information</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="experience_years" class="form-label">Years of Experience</label>
                                <input type="number" 
                                       class="form-control @error('experience_years') is-invalid @enderror" 
                                       id="experience_years" 
                                       name="experience_years" 
                                       min="0" 
                                       max="50" 
                                       value="{{ old('experience_years', $profile->experience_years) }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="specialization" class="form-label">Specialization</label>
                                <input type="text" 
                                       class="form-control @error('specialization') is-invalid @enderror" 
                                       id="specialization" 
                                       name="specialization" 
                                       value="{{ old('specialization', $profile->specialization) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="availability_status" class="form-label">Availability Status</label>
                            <select class="form-select @error('availability_status') is-invalid @enderror" 
                                    id="availability_status" 
                                    name="availability_status">
                                <option value="available" {{ old('availability_status', $profile->availability_status) == 'available' ? 'selected' : '' }}>
                                    Available
                                </option>
                                <option value="busy" {{ old('availability_status', $profile->availability_status) == 'busy' ? 'selected' : '' }}>
                                    Busy
                                </option>
                                <option value="unavailable" {{ old('availability_status', $profile->availability_status) == 'unavailable' ? 'selected' : '' }}>
                                    Unavailable
                                </option>
                            </select>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea class="form-control @error('bio') is-invalid @enderror" 
                                  id="bio" 
                                  name="bio" 
                                  rows="4" 
                                  placeholder="Tell us about yourself...">{{ old('bio', $profile->bio) }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('profile.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Back to Profile
                        </a>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
