@extends('auth::layout')

@section('title', 'Subscriber — ' . $user->name)

@section('head')
    <style>
        .sub-stat {
            border-radius: 12px;
            padding: 1.25rem;
            color: #fff;
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
        }
        .sub-stat.purple { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .sub-stat.teal { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .sub-stat.amber { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .sub-stat.blue { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
        .sub-stat .sv { font-size: 1.65rem; font-weight: 700; }
        .sub-stat .sl { font-size: 0.85rem; opacity: 0.92; }
        .sub-link-box {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 1rem;
        }
        .main-content .card-header.bg-primary .sub-header-pill {
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
                        <i class="fas fa-user-circle me-2 text-primary"></i>
                        Subscriber — {{ $user->name }}
                    </h2>
                    <p class="text-muted mb-0">
                        <span class="badge bg-secondary">{{ ucfirst($user->role) }}</span>
                        <span class="ms-2">ID: {{ $user->unique_id }}</span>
                        @if($leaderboardRank)
                            <span class="ms-2 badge bg-dark bg-opacity-10 text-dark border">Revenue rank #{{ $leaderboardRank }}</span>
                        @endif
                    </p>
                </div>
                <a href="{{ route('admin.subscriptions') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to subscriptions
                </a>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-4 mb-3 mb-lg-0">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center text-white fw-bold rounded-circle"
                         style="width: 96px; height: 96px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); font-size: 1.85rem;">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="text-muted small mb-3">{{ $user->email }}</p>
                    <p class="small mb-0 text-muted">Phone: {{ $user->phone ?: '—' }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0"><i class="fas fa-link me-2"></i>Quick links</h5>
                    @if($leaderboardRank)
                        <span class="badge rounded-pill fw-bold px-3 py-2 sub-header-pill">Rank #{{ $leaderboardRank }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Open the main admin list filtered to this subscriber, or jump into a specific subscription row.
                    </p>
                    <div class="sub-link-box mb-3">
                        <label class="form-label fw-bold small mb-1">Filtered subscription list</label>
                        <input type="text" class="form-control form-control-sm" readonly id="adminFilterUrl" value="{{ $adminFilterUrl }}">
                        <div class="mt-2 d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-primary" id="copyFilterUrl">Copy</button>
                            <a href="{{ $adminFilterUrl }}" class="btn btn-sm btn-outline-primary">Open</a>
                        </div>
                    </div>
                    <a href="{{ route('admin.profiles.view', $user) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-id-card me-1"></i>Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="sub-stat purple">
                <div class="sv">{{ number_format($subscriberStats['subscription_count']) }}</div>
                <div class="sl"><i class="fas fa-list me-1"></i>Subscriptions</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="sub-stat teal">
                <div class="sv">{{ number_format($subscriberStats['active_count']) }}</div>
                <div class="sl"><i class="fas fa-check-circle me-1"></i>Active</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="sub-stat amber">
                <div class="sv">{{ number_format($subscriberStats['pending_count']) }}</div>
                <div class="sl"><i class="fas fa-clock me-1"></i>Pending</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="sub-stat blue">
                <div class="sv">₹{{ number_format($subscriberStats['active_revenue_total'] ?? 0, 2) }}</div>
                <div class="sl"><i class="fas fa-bolt me-1"></i>Active plans total · all-time ₹{{ number_format($subscriberStats['lifetime_total'], 2) }}</div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Subscription history</h5>
        </div>
        <div class="card-body p-0">
            @if($subscriptions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Plan</th>
                                <th>Status</th>
                                <th class="text-end">Amount</th>
                                <th>Payment</th>
                                <th>Period</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subscriptions as $sub)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $sub->plan->name }}</div>
                                        <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $sub->payment_frequency)) }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $sub->status_color }}">{{ $sub->status_display }}</span>
                                    </td>
                                    <td class="text-end fw-semibold">₹{{ number_format($sub->total_amount, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $sub->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ ucfirst(str_replace('_', ' ', $sub->payment_status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $sub->start_date->format('M d, Y') }} → {{ $sub->end_date->format('M d, Y') }}</small>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.subscriptions.view', $sub) }}" class="btn btn-sm btn-primary">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center text-muted py-5">No rows.</div>
            @endif
        </div>
        @if($subscriptions->hasPages())
            <div class="card-footer bg-white">{{ $subscriptions->links() }}</div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('copyFilterUrl')?.addEventListener('click', function () {
        var el = document.getElementById('adminFilterUrl');
        if (!el) return;
        el.select();
        try {
            navigator.clipboard.writeText(el.value);
            alert('Link copied.');
        } catch (e) {
            document.execCommand('copy');
            alert('Link copied.');
        }
    });
</script>
@endsection
