@extends('auth::layout')

@section('title', 'Patient Rewards - ' . $staff->name)

@section('head')
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <style>
        .pr-stat-card {
            border-radius: 12px;
            padding: 1.35rem 1.25rem;
            color: #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        .pr-stat-card.purple { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .pr-stat-card.teal { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .pr-stat-card.amber { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .pr-stat-card.blue { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
        .pr-stat-card .pv { font-size: 1.85rem; font-weight: 700; }
        .pr-stat-card .pl { font-size: 0.88rem; opacity: 0.92; }
        .pr-link-box {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 1rem;
        }
        .referral-code-readable {
            color: #0f172a !important;
            background-color: #e2e8f0 !important;
            font-weight: 600;
            font-size: 0.95rem;
        }
        .main-content .card-header.bg-primary .pr-header-pill {
            background-color: #f8f9fa !important;
            color: #0f172a !important;
            border: 1px solid rgba(255, 255, 255, 0.35);
        }
    </style>
@endsection

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <h2 class="h4 mb-1">
                        <i class="fas fa-gift me-2 text-primary"></i>
                        Patient reward details — {{ $staff->name }}
                    </h2>
                    <p class="text-muted mb-0">
                        <span class="badge bg-{{ $staff->role === 'nurse' ? 'info' : 'success' }}">{{ ucfirst($staff->role) }}</span>
                        <span class="ms-2">ID: {{ $staff->unique_id }}</span>
                        @if($leaderboardRank)
                            <span class="ms-2 badge bg-dark bg-opacity-10 text-dark border">Leaderboard #{{ $leaderboardRank }}</span>
                        @endif
                    </p>
                </div>
                <a href="{{ route('admin.rewards.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to rewards
                </a>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-4 mb-3 mb-lg-0">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center text-white fw-bold"
                         style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); font-size: 2rem;">
                        {{ strtoupper(substr($staff->name, 0, 2)) }}
                    </div>
                    <h4 class="mb-1">{{ $staff->name }}</h4>
                    <p class="text-muted mb-3">{{ ucfirst($staff->role) }}</p>
                    <div class="text-start small">
                        <div class="mb-2"><strong>Email:</strong> {{ $staff->email }}</div>
                        <div class="mb-2"><strong>Phone:</strong> {{ $staff->phone ?: '—' }}</div>
                    </div>
                    <div class="mt-3 pt-3 border-top">
                        <span class="badge bg-primary fs-6 px-3 py-2">
                            Patient rewards total: ₹{{ number_format($rewardStats['total_amount'], 2) }}
                            <span class="fw-normal">({{ number_format($rewardStats['total_points']) }} pts)</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Quick links &amp; context</h5>
                    @if($leaderboardRank)
                        <span class="badge rounded-pill fw-bold px-3 py-2 pr-header-pill">Rank #{{ $leaderboardRank }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Patient details are submitted by this staff member from the rewards flow. OTP verification controls whether an entry counts as verified.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-bold mb-1">Filtered admin list (this staff only)</label>
                        <div class="pr-link-box">
                            <input type="text" class="form-control form-control-sm" readonly id="adminFilterUrl" value="{{ $adminFilterUrl }}">
                            <button type="button" class="btn btn-sm btn-primary mt-2" id="copyFilterUrl">
                                <i class="fas fa-copy me-1"></i>Copy link
                            </button>
                            <a href="{{ $adminFilterUrl }}" class="btn btn-sm btn-outline-primary mt-2 ms-0 ms-sm-2">Open</a>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.referrals.staff', $staff) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-share-alt me-1"></i>Referral details (same staff)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="pr-stat-card purple">
                <div class="pv">{{ number_format($rewardStats['submissions_count']) }}</div>
                <div class="pl"><i class="fas fa-file-medical me-1"></i>Total submissions</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="pr-stat-card teal">
                <div class="pv">{{ number_format($rewardStats['verified_count']) }}</div>
                <div class="pl"><i class="fas fa-check-circle me-1"></i>Verified</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="pr-stat-card amber">
                <div class="pv">{{ number_format($rewardStats['pending_count']) }}</div>
                <div class="pl"><i class="fas fa-hourglass-half me-1"></i>Pending OTP</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="pr-stat-card blue">
                <div class="pv">₹{{ number_format($rewardStats['total_amount'], 2) }}</div>
                <div class="pl"><i class="fas fa-rupee-sign me-1"></i>Total value ({{ number_format($rewardStats['total_points']) }} pts)</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Submission history</h5>
                </div>
                <div class="card-body p-0">
                    @if($rewards->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Patient</th>
                                        <th>Contact</th>
                                        <th>Location</th>
                                        <th>Hospital</th>
                                        <th class="text-center">Reward</th>
                                        <th class="text-center">Status</th>
                                        <th>Submitted</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rewards as $reward)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $reward->patient_name }}</div>
                                                @if($reward->patient_age)
                                                    <small class="text-muted">Age {{ $reward->patient_age }}</small>
                                                @endif
                                                @if($reward->treatment_details)
                                                    <div class="small text-muted">{{ Str::limit($reward->treatment_details, 48) }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="font-monospace d-block">{{ $reward->patient_phone }}</small>
                                                @if($reward->patient_email)
                                                    <small class="text-muted text-break d-block">{{ $reward->patient_email }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ Str::limit($reward->patient_address ?? '—', 40) }}</small>
                                                @if($reward->patient_pincode)
                                                    <div class="small text-muted">{{ $reward->patient_pincode }}</div>
                                                @endif
                                            </td>
                                            <td><small>{{ Str::limit($reward->hospital_name, 32) }}</small></td>
                                            <td class="text-center">
                                                @php
                                                    $staffMobileOk = (bool) $staff->hasVerifiedPhone();
                                                    $rewardBlockers = \App\Modules\Payments\Services\StaffEarningStatusResolver::patientRewardBlockers($reward, $staffMobileOk);
                                                    $countsForStaff = \App\Modules\Payments\Services\StaffEarningStatusResolver::patientRewardCountsForStaff($reward, $staffMobileOk);
                                                @endphp
                                                @if($countsForStaff || $reward->payment_processed)
                                                    <span class="fw-semibold text-success">+{{ $reward->reward_points }}</span>
                                                    <div class="small text-muted">₹{{ number_format($reward->reward_amount, 2) }}</div>
                                                @else
                                                    <span class="fw-semibold text-muted">0</span>
                                                    <div class="small text-muted">Not credited</div>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @include('services::staff.partials.payout-status-blockers', ['blockers' => $rewardBlockers, 'compact' => true, 'align' => 'center'])
                                            </td>
                                            <td>
                                                <div class="fw-semibold small">{{ $reward->created_at->format('M d, Y') }}</div>
                                                <small class="text-muted">{{ $reward->created_at->format('h:i A') }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                            No patient reward submissions for this staff member.
                        </div>
                    @endif
                </div>
                @if($rewards->hasPages())
                    <div class="card-footer bg-white border-top-0">
                        {{ $rewards->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('copyFilterUrl')?.addEventListener('click', function () {
        var el = document.getElementById('adminFilterUrl');
        if (!el) return;
        el.select();
        el.setSelectionRange(0, 99999);
        try {
            navigator.clipboard.writeText(el.value);
            alert('Link copied.');
        } catch (e) {
            try {
                document.execCommand('copy');
                alert('Link copied.');
            } catch (err) {
                alert('Copy failed — select the field and copy manually.');
            }
        }
    });
</script>
@endsection
