@extends('auth::layout')

@section('title', 'Reset Password - MMHC CRM')

@section('content')
<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <h1 class="h4 fw-bold text-dark mb-1">Choose a new password</h1>
                        <p class="text-muted small mb-0">Enter your new password below.</p>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger py-2 small">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('auth.reset-password.post') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="email" value="{{ $email }}">

                        <div class="mb-3">
                            <label for="email_display" class="form-label fw-semibold">Email</label>
                            <input type="email" id="email_display" class="form-control" value="{{ $email }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">New password</label>
                            <input type="password"
                                   name="password"
                                   id="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   required
                                   minlength="6"
                                   autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label fw-semibold">Confirm password</label>
                            <input type="password"
                                   name="password_confirmation"
                                   id="password_confirmation"
                                   class="form-control"
                                   required
                                   minlength="6"
                                   autocomplete="new-password">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-key me-1"></i> Update password
                        </button>
                        <a href="{{ route('auth.login') }}" class="btn btn-outline-secondary w-100 btn-sm">Back to sign in</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
