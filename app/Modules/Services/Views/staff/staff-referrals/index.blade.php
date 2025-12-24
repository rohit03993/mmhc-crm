@extends('auth::layout')

@section('title', 'Staff Referrals - Staff Dashboard')

@section('content')
<!-- Mobile Header -->
<div class="app-mobile-header d-md-none">
    <div class="d-flex align-items-center">
        <a href="{{ route('staff.dashboard') }}" class="btn btn-link text-white p-0 me-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h5 class="text-white mb-0">Staff Referrals</h5>
    </div>
</div>

<div class="container-fluid px-3 py-4">
    <!-- Stats Banner -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="stats-card-modern bg-info">
                <div class="stats-icon">
                    <i class="fas fa-user-friends"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value">{{ $referralStats['completed_referrals'] }}</div>
                    <div class="stats-label">Completed Referrals</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stats-card-modern bg-success">
                <div class="stats-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value">{{ $referralStats['total_reward_points'] }}</div>
                    <div class="stats-label">Total Points</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stats-card-modern bg-warning">
                <div class="stats-icon">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value">₹{{ number_format($referralStats['total_reward_amount'], 2) }}</div>
                    <div class="stats-label">Total Earnings</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Referral Link Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="referral-link-card-modern">
                <div class="referral-link-header">
                    <i class="fas fa-share-alt me-2"></i>
                    <h5 class="mb-0">Your Staff Referral Link</h5>
                </div>
                <div class="referral-link-body">
                    <p class="mb-3">Share this link with nurses and caregivers. When they register using your link, you earn <strong>1 point (₹10)</strong> per successful referral.</p>
                    <div class="input-group-modern">
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="referralLink" 
                               value="{{ $referralLink }}" 
                               readonly>
                        <button class="btn btn-primary btn-lg" 
                                type="button" 
                                onclick="copyReferralLink()">
                            <i class="fas fa-copy me-2"></i>Copy Link
                        </button>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle me-1"></i>You can share this link multiple times. Each successful registration earns you rewards.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Referrals List -->
    <div class="row">
        <div class="col-12">
            <div class="section-header mb-3">
                <h5 class="section-title">
                    <i class="fas fa-history me-2"></i>Referral History
                </h5>
            </div>

            @if($referrals->count() > 0)
                <div class="referrals-list-cards">
                    @foreach($referrals as $referral)
                        <div class="referral-entry-card-modern">
                            <div class="referral-entry-header-modern">
                                <div class="referral-entry-info">
                                    <div class="referral-entry-name">
                                        @if($referral->referred)
                                            <i class="fas fa-user-check me-2 text-success"></i>{{ $referral->referred->name }}
                                        @else
                                            <i class="fas fa-clock me-2 text-warning"></i>Pending Registration
                                        @endif
                                    </div>
                                    <div class="referral-entry-meta">
                                        <span class="badge bg-secondary me-2">Code: {{ $referral->referral_code }}</span>
                                        <span class="badge bg-{{ $referral->status === 'completed' ? 'success' : ($referral->status === 'pending' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($referral->status) }}
                                        </span>
                                    </div>
                                </div>
                                @if($referral->status === 'completed')
                                <div class="referral-entry-badge-modern">
                                    <span class="badge-points">+{{ $referral->reward_points }} pts</span>
                                    <span class="badge-amount">₹{{ number_format($referral->reward_amount, 2) }}</span>
                                </div>
                                @endif
                            </div>
                            @if($referral->completed_at)
                            <div class="referral-entry-details-modern">
                                <div class="detail-row">
                                    <i class="fas fa-calendar-check me-2 text-success"></i>
                                    <span>Completed: {{ $referral->completed_at->format('M d, Y') }} • {{ $referral->completed_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $referrals->links() }}
                </div>
            @else
                <div class="empty-state-modern">
                    <div class="empty-state-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <h5>No Referrals Yet</h5>
                    <p class="text-muted">Share your referral link to start earning rewards!</p>
                </div>
            @endif
        </div>
    </div>
</div>

@include('auth::components.bottom-nav')

<style>
.stats-card-modern {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
    border-top: 4px solid;
}

.stats-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    color: white;
}

.bg-info .stats-icon { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.bg-success .stats-icon { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
.bg-warning .stats-icon { background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); }

.stats-content {
    flex: 1;
}

.stats-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #212529;
    line-height: 1.2;
}

.stats-label {
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.referral-link-card-modern {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    overflow: hidden;
    border-top: 4px solid #4facfe;
}

.referral-link-header {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    font-size: 1.1rem;
    font-weight: 600;
}

.referral-link-body {
    padding: 1.5rem;
}

.input-group-modern {
    display: flex;
    gap: 0.5rem;
}

.input-group-modern .form-control {
    flex: 1;
    font-family: monospace;
}

.referral-entry-card-modern {
    background: white;
    border-radius: 16px;
    padding: 1.25rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    margin-bottom: 1rem;
    transition: all 0.3s ease;
    border-left: 4px solid #4facfe;
}

.referral-entry-card-modern:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.12);
}

.referral-entry-header-modern {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f0f0f0;
}

.referral-entry-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.5rem;
}

.referral-entry-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.referral-entry-badge-modern {
    text-align: right;
}

.badge-points {
    display: block;
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.badge-amount {
    display: block;
    color: #28a745;
    font-weight: 700;
    font-size: 1rem;
}

.referral-entry-details-modern {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.detail-row {
    display: flex;
    align-items: center;
    font-size: 0.85rem;
    color: #6c757d;
}

.empty-state-modern {
    text-align: center;
    padding: 3rem 1rem;
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.empty-state-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    margin: 0 auto 1.5rem;
}

@media (max-width: 768px) {
    .stats-card-modern {
        padding: 1.25rem;
    }
    
    .stats-icon {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
    }
    
    .stats-value {
        font-size: 1.5rem;
    }
    
    .input-group-modern {
        flex-direction: column;
    }
}
</style>

<script>
function copyReferralLink() {
    const referralLinkInput = document.getElementById('referralLink');
    referralLinkInput.select();
    referralLinkInput.setSelectionRange(0, 99999);
    
    try {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(referralLinkInput.value).then(() => {
                showCopySuccess();
            });
        } else {
            document.execCommand('copy');
            showCopySuccess();
        }
    } catch (err) {
        alert('Failed to copy referral link. Please copy manually.');
    }
}

function showCopySuccess() {
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check me-2"></i>Copied!';
    btn.classList.add('btn-success');
    btn.classList.remove('btn-primary');
    
    setTimeout(() => {
        btn.innerHTML = originalHTML;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-primary');
    }, 2000);
}
</script>
@endsection

