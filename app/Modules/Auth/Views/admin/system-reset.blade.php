@extends('auth::layout')

@section('title', 'Reset System Data - Admin')

@section('head')
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
<link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
@endsection

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">
    <div class="row">
        <div class="col-12 col-lg-10 mx-auto">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>Reset System Data
                    </h2>
                    <p class="text-muted mb-0">Danger Zone - Use with extreme caution</p>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                </a>
            </div>

            <!-- Critical Warning -->
            <div class="alert alert-danger border-danger border-3 mb-4">
                <h4 class="alert-heading">
                    <i class="fas fa-skull-crossbones me-2"></i>CRITICAL WARNING
                </h4>
                <p class="mb-2"><strong>This action will permanently delete:</strong></p>
                <ul class="mb-2">
                    <li><strong>ALL users</strong> except admin account</li>
                    <li><strong>ALL service requests</strong> and daily service records</li>
                    <li><strong>ALL caregiver rewards</strong> and patient details</li>
                    <li><strong>ALL referrals</strong> and referral tracking</li>
                    <li><strong>ALL subscriptions</strong> (user subscriptions, plans will remain)</li>
                    <li><strong>ALL staff payments</strong> history</li>
                    <li><strong>ALL profiles and documents</strong> uploaded by users</li>
                </ul>
                <p class="mb-0"><strong class="text-danger">THIS ACTION CANNOT BE UNDONE!</strong></p>
            </div>

            <!-- What Will Be Preserved -->
            <div class="alert alert-info mb-4">
                <h5 class="alert-heading">
                    <i class="fas fa-shield-alt me-2"></i>What Will Be Preserved:
                </h5>
                <ul class="mb-0">
                    <li>Admin user account</li>
                    <li>Subscription plans (healthcare_plans table)</li>
                    <li>Service types configuration</li>
                    <li>System settings and configuration</li>
                    <li>Database structure and migrations</li>
                </ul>
            </div>

            <!-- Current System Statistics -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Current System Data
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="h3 mb-0 text-primary">{{ $stats['non_admin_users'] }}</div>
                                <div class="small text-muted">Non-Admin Users</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="h3 mb-0 text-info">{{ $stats['patients'] }}</div>
                                <div class="small text-muted">Patients</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="h3 mb-0 text-success">{{ $stats['nurses'] + $stats['caregivers'] }}</div>
                                <div class="small text-muted">Staff (Nurses + Caregivers)</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="h3 mb-0 text-warning">{{ $stats['service_requests'] }}</div>
                                <div class="small text-muted">Service Requests</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="h3 mb-0 text-secondary">{{ $stats['rewards'] }}</div>
                                <div class="small text-muted">Rewards</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="h3 mb-0 text-secondary">{{ $stats['referrals'] }}</div>
                                <div class="small text-muted">Referrals</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="h3 mb-0 text-secondary">{{ $stats['subscriptions'] }}</div>
                                <div class="small text-muted">Subscriptions</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="h3 mb-0 text-secondary">{{ $stats['staff_payments'] }}</div>
                                <div class="small text-muted">Staff Payments</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reset Form -->
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bomb me-2"></i>Confirm System Reset
                    </h5>
                </div>
                <div class="card-body">
                    @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    </div>
                    @endif

                    <form action="{{ route('admin.system.reset.store') }}" method="POST" id="resetForm">
                        @csrf

                        <!-- Confirmation 1 -->
                        <div class="mb-4">
                            <label class="form-label">
                                <strong>Confirmation 1:</strong> Type "yes" to confirm you understand this will delete all user data
                            </label>
                            <input type="text" 
                                   name="confirmation_1" 
                                   class="form-control @error('confirmation_1') is-invalid @enderror" 
                                   placeholder="Type 'yes' here"
                                   required>
                            @error('confirmation_1')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirmation 2 -->
                        <div class="mb-4">
                            <label class="form-label">
                                <strong>Confirmation 2:</strong> Type "yes" again to confirm you are absolutely sure
                            </label>
                            <input type="text" 
                                   name="confirmation_2" 
                                   class="form-control @error('confirmation_2') is-invalid @enderror" 
                                   placeholder="Type 'yes' here"
                                   required>
                            @error('confirmation_2')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirmation 3 -->
                        <div class="mb-4">
                            <label class="form-label">
                                <strong>Confirmation 3:</strong> Type "RESET" (all caps) to proceed
                            </label>
                            <input type="text" 
                                   name="confirmation_text" 
                                   class="form-control @error('confirmation_text') is-invalid @enderror" 
                                   placeholder="Type 'RESET' here"
                                   required>
                            @error('confirmation_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" 
                                    class="btn btn-danger btn-lg" 
                                    id="resetButton"
                                    onclick="return confirmFinalReset(event)">
                                <i class="fas fa-exclamation-triangle me-2"></i>RESET SYSTEM DATA
                            </button>
                        </div>
                    </form>
                    
                    <!-- Progress Indicator (hidden by default) -->
                    <div id="resetProgress" class="mt-3" style="display: none;">
                        <div class="alert alert-info">
                            <div class="d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm me-3" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div>
                                    <strong>Resetting system...</strong>
                                    <div class="small">This may take a few moments. Please do not close this page.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Warning -->
            <div class="alert alert-warning mt-4">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Note:</strong> After reset, you will need to register new users, create new service requests, etc. 
                The system will be in a fresh state with only admin account remaining.
            </div>
        </div>
    </div>
</div>

<script>
function confirmFinalReset(event) {
    event.preventDefault();
    
    const confirmation1 = document.querySelector('input[name="confirmation_1"]').value.toLowerCase().trim();
    const confirmation2 = document.querySelector('input[name="confirmation_2"]').value.toLowerCase().trim();
    const confirmationText = document.querySelector('input[name="confirmation_text"]').value.trim();

    if (confirmation1 !== 'yes' || confirmation2 !== 'yes' || confirmationText !== 'RESET') {
        alert('Please fill all confirmation fields correctly before proceeding.');
        return false;
    }

    const finalConfirm = confirm(
        '⚠️ FINAL WARNING ⚠️\n\n' +
        'You are about to PERMANENTLY DELETE:\n' +
        '- All users (except admin)\n' +
        '- All service requests\n' +
        '- All rewards and referrals\n' +
        '- All subscriptions\n' +
        '- All staff payments\n' +
        '- All profiles and documents\n\n' +
        'THIS CANNOT BE UNDONE!\n\n' +
        'Are you ABSOLUTELY CERTAIN you want to proceed?'
    );

    if (!finalConfirm) {
        return false;
    }

    // Show progress indicator
    document.getElementById('resetProgress').style.display = 'block';
    
    // Disable button to prevent double submission
    const resetButton = document.getElementById('resetButton');
    resetButton.disabled = true;
    resetButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Resetting System...';

    // Submit the form
    document.getElementById('resetForm').submit();
    
    return false; // Prevent default form submission
}
</script>

<style>
.alert-danger {
    border-left: 5px solid #dc3545;
}

.alert-info {
    border-left: 5px solid #0dcaf0;
}

.card.border-danger {
    border-width: 2px !important;
}

#resetButton:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
@endsection

