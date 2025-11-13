@extends('auth::layout')

@section('title', 'Referral Details - ' . $staff->name)

@section('head')
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <style>
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 1.5rem;
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .stat-card.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        .stat-card.info {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }
        .stat-card.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .referral-link-box {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            margin: 1rem 0;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-user me-2 text-primary"></i>
                        Referral Details - {{ $staff->name }}
                    </h2>
                    <p class="text-muted mb-0">
                        <span class="badge bg-{{ $staff->role === 'nurse' ? 'info' : 'success' }}">{{ ucfirst($staff->role) }}</span>
                        <span class="ms-2">ID: {{ $staff->unique_id }}</span>
                    </p>
                </div>
                <a href="{{ route('admin.referrals.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Referrals
                </a>
            </div>
        </div>
    </div>

    <!-- Staff Info Card -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="avatar-circle mx-auto mb-3" style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 2rem;">
                        {{ strtoupper(substr($staff->name, 0, 2)) }}
                    </div>
                    <h4 class="mb-1">{{ $staff->name }}</h4>
                    <p class="text-muted mb-3">{{ ucfirst($staff->role) }}</p>
                    <div class="mb-2">
                        <strong>Email:</strong> {{ $staff->email }}
                    </div>
                    <div class="mb-2">
                        <strong>Phone:</strong> {{ $staff->phone }}
                    </div>
                    <div>
                        <strong>Reward Points:</strong> 
                        <span class="badge bg-primary">{{ $staff->reward_points ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-link me-2"></i>Referral Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Referral Code:</label>
                        <div>
                            <code class="bg-light p-2 rounded d-inline-block" style="font-size: 1.1rem;">{{ $referralCode }}</code>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Referral Link:</label>
                        <div class="referral-link-box">
                            <input type="text" 
                                   class="form-control" 
                                   value="{{ $referralLink }}" 
                                   readonly
                                   id="referralLink">
                            <button class="btn btn-sm btn-primary mt-2" onclick="copyReferralLink()">
                                <i class="fas fa-copy me-1"></i>Copy Link
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ $referralStats['total_referrals'] }}</div>
                <div class="stat-label">
                    <i class="fas fa-users me-2"></i>Total Referrals
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card success">
                <div class="stat-value">{{ $referralStats['completed_referrals'] }}</div>
                <div class="stat-label">
                    <i class="fas fa-check-circle me-2"></i>Completed
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card info">
                <div class="stat-value">{{ $referralStats['total_reward_points'] }}</div>
                <div class="stat-label">
                    <i class="fas fa-gift me-2"></i>Reward Points
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card warning">
                <div class="stat-value">₹{{ number_format($referralStats['total_reward_amount'], 2) }}</div>
                <div class="stat-label">
                    <i class="fas fa-rupee-sign me-2"></i>Reward Amount
                </div>
            </div>
        </div>
    </div>

    <!-- Referrals List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Referral History
                    </h5>
                </div>
                <div class="card-body">
                    @if($referrals->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Referred User</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Reward Points</th>
                                        <th>Reward Amount</th>
                                        <th>Completed At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($referrals as $referral)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle me-2" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                                        {{ strtoupper(substr($referral->referred->name, 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">{{ $referral->referred->name }}</div>
                                                        <small class="text-muted">ID: {{ $referral->referred->unique_id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $referral->referred->role === 'nurse' ? 'info' : 'success' }}">
                                                    {{ ucfirst($referral->referred->role) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($referral->status === 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @else
                                                    <span class="badge bg-warning">Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="fw-bold text-primary">{{ $referral->reward_points }}</span>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-success">₹{{ number_format($referral->reward_amount, 2) }}</span>
                                            </td>
                                            <td>
                                                @if($referral->completed_at)
                                                    {{ $referral->completed_at->format('M d, Y') }}
                                                    <br>
                                                    <small class="text-muted">{{ $referral->completed_at->format('h:i A') }}</small>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $referrals->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No referrals found for this staff member.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function copyReferralLink() {
        const referralLinkInput = document.getElementById('referralLink');
        referralLinkInput.select();
        referralLinkInput.setSelectionRange(0, 99999);
        
        try {
            document.execCommand('copy');
            alert('Referral link copied to clipboard!');
        } catch (err) {
            console.error('Failed to copy: ', err);
            alert('Failed to copy referral link. Please copy manually.');
        }
    }
</script>
@endpush
@endsection

