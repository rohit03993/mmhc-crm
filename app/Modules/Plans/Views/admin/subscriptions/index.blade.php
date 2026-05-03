@extends('auth::layout')

@section('title', 'Subscription Management')

@section('head')
    @php
        $currentStatus = request('status', 'all');
        $tabBase = array_filter(['user_id' => request('user_id')]);
    @endphp
    <style>
        .sub-admin-stat {
            border-radius: 12px;
            padding: 1.25rem 1.15rem;
            color: #fff;
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
        }
        .sub-admin-stat .sv { font-size: 1.65rem; font-weight: 700; }
        .sub-admin-stat .sl { font-size: 0.86rem; opacity: 0.92; }
        .sub-admin-stat.total { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .sub-admin-stat.active { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .sub-admin-stat.pending { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .sub-admin-stat.expired { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); }
        .sub-page-guide {
            border-left: 4px solid #2563eb;
            background: #f8fafc;
            border-radius: 0 10px 10px 0;
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
        }
        .sub-section-label {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #64748b;
        }
        .main-content .card-header.bg-primary .sub-lb-pill {
            background-color: #f8f9fa !important;
            color: #0f172a !important;
            border: 1px solid rgba(255, 255, 255, 0.35);
        }
        .main-content .card-header.bg-secondary .sub-log-pill {
            background-color: #f8f9fa !important;
            color: #0f172a !important;
        }
        .filter-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 8px;
            margin-bottom: 1rem;
        }
        .filter-tab {
            padding: 8px 16px;
            text-decoration: none;
            color: #475569;
            border-radius: 8px 8px 0 0;
            font-weight: 600;
            transition: all 0.2s;
        }
        .filter-tab:hover { color: #2563eb; }
        .filter-tab.active {
            color: #2563eb;
            background: #f1f5f9;
            border-bottom: 2px solid #2563eb;
        }
        .subscription-card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, 0.35);
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.06);
            padding: 1.15rem 1.25rem;
            margin-bottom: 1rem;
        }
        .subscription-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e9ecef;
            margin-bottom: 12px;
        }
        .subscription-user-name { font-size: 1.05rem; font-weight: 700; margin-bottom: 2px; color: #0f172a; }
        .subscription-plan-name { color: #64748b; font-size: 0.9rem; margin-bottom: 0; }
        .payment-proof-section {
            padding: 12px;
            background: #f1f5f9;
            border-radius: 8px;
        }
        .payment-actions {
            padding: 12px;
            background: #e0f2fe;
            border-radius: 8px;
            border: 1px solid #7dd3fc;
            margin-top: 12px;
        }
        .subscription-card-footer {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            padding-top: 12px;
            border-top: 1px solid #e9ecef;
            margin-top: 12px;
        }
        .sub-filter-box {
            background: #fff;
            border-radius: 12px;
            padding: 1.15rem 1.25rem;
            margin-bottom: 1.25rem;
            border: 1px solid rgba(148, 163, 184, 0.25);
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        }
    </style>
@endsection

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h2 class="h4 mb-1">
                <i class="fas fa-list-alt me-2 text-primary"></i>
                Subscription management
            </h2>
            <p class="text-muted small mb-0">
                Subscriber revenue leaderboard, then every subscription record.
                <a href="#sub-leaderboard" class="text-decoration-none">Leaderboard</a>
                ·
                <a href="#sub-records" class="text-decoration-none">All subscriptions</a>
            </p>
        </div>
        <a href="{{ route('admin.plans') }}" class="btn btn-primary">
            <i class="fas fa-cog me-2"></i>Manage plans
        </a>
    </div>

    @if(isset($stats))
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="sub-admin-stat total">
                <div class="sv">{{ $stats['total_subscriptions'] }}</div>
                <div class="sl"><i class="fas fa-list me-1"></i>Total</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="sub-admin-stat active">
                <div class="sv">{{ $stats['active_subscriptions'] }}</div>
                <div class="sl"><i class="fas fa-check-circle me-1"></i>Active</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="sub-admin-stat pending">
                <div class="sv">{{ $stats['pending_subscriptions'] }}</div>
                <div class="sl"><i class="fas fa-clock me-1"></i>Pending</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="sub-admin-stat expired">
                <div class="sv">{{ $stats['expired_subscriptions'] }}</div>
                <div class="sl"><i class="fas fa-times-circle me-1"></i>Expired</div>
            </div>
        </div>
    </div>
    @endif

    @if($topSubscriber ?? null)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="mb-2 opacity-90"><i class="fas fa-trophy me-2"></i>Top subscriber (active plans only — ₹)</h6>
                                <h5 class="mb-1 fw-bold">{{ $topSubscriber->name }}</h5>
                                <p class="mb-0 small opacity-90">
                                    {{ (int) $topSubscriber->active_subscription_count }} active
                                    @if((int) ($topSubscriber->total_subscription_count ?? 0) > (int) ($topSubscriber->active_subscription_count ?? 0))
                                        · {{ (int) $topSubscriber->total_subscription_count }} total lifetime rows
                                    @endif
                                    · ₹{{ number_format((float) ($topSubscriber->active_revenue_total ?? 0), 2) }} from active plans
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0 d-flex flex-wrap gap-2 justify-content-md-end">
                                <a href="{{ route('admin.subscriptions.subscriber', $topSubscriber) }}" class="btn btn-light btn-sm">
                                    <i class="fas fa-eye me-1"></i>Full details
                                </a>
                                <a href="{{ route('admin.subscriptions', array_filter(['user_id' => $topSubscriber->id, 'status' => 'all'])) }}" class="btn btn-outline-light btn-sm">
                                    <i class="fas fa-filter me-1"></i>List only
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="sub-filter-box">
        <p class="small text-muted mb-2">
            <i class="fas fa-info-circle me-1 text-primary"></i>
            <strong>Subscriber filter</strong> narrows the subscription cards below. Status tabs still apply. The leaderboard lists only subscribers with a <strong>currently active</strong> plan (paid period not ended); ranks use active-plan ₹ only.
        </p>
        <form method="GET" action="{{ route('admin.subscriptions') }}" class="row g-2 align-items-end">
            <input type="hidden" name="status" value="{{ $currentStatus }}">
            <div class="col-md-6 col-lg-5">
                <label class="form-label fw-semibold mb-1">Focus on subscriber</label>
                <select name="user_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All subscribers</option>
                    @foreach($subscriberLeaderboard as $subRow)
                        <option value="{{ $subRow->id }}" {{ ($filterUser && (int) $filterUser->id === (int) $subRow->id) ? 'selected' : '' }}>
                            {{ $subRow->name }} — {{ (int) $subRow->active_subscription_count }} active · ₹{{ number_format((float) ($subRow->active_revenue_total ?? 0), 2) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                @if($filterUser)
                    <a href="{{ route('admin.subscriptions', array_filter(['status' => $currentStatus])) }}" class="btn btn-outline-secondary">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <div class="sub-page-guide mb-4">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="sub-section-label mb-1">Summary</div>
                <div class="fw-semibold text-dark"><i class="fas fa-medal text-warning me-1"></i> Subscriber leaderboard</div>
                <p class="small text-muted mb-0">One row per user with at least one <strong>active</strong> plan — counts and ₹ include <strong>active</strong> subscriptions only (ranked by active ₹).</p>
            </div>
            <div class="col-md-6">
                <div class="sub-section-label mb-1">Detail</div>
                <div class="fw-semibold text-dark"><i class="fas fa-file-invoice-dollar text-secondary me-1"></i> Subscription records</div>
                <p class="small text-muted mb-0">One card per subscription row — status, payment, dates, and actions.</p>
            </div>
        </div>
    </div>

    <div class="row mb-4" id="sub-leaderboard">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
                    <div>
                        <div class="sub-section-label text-white-50 mb-1">Summary · per subscriber</div>
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Subscriber revenue leaderboard</h5>
                        <small class="text-white-50">Active plans only (status active &amp; end date in future). Ranked by ₹ from those rows, then active count</small>
                    </div>
                    <span class="badge rounded-pill fw-bold px-3 py-2 fs-6 shadow-sm sub-lb-pill">
                        {{ $subscriberLeaderboard->count() }} with active plan
                    </span>
                </div>
                <div class="card-body">
                    @if($subscriberLeaderboard->isEmpty())
                        <p class="text-muted text-center py-4 mb-0">No subscribers with a currently active plan.</p>
                    @else
                        <p class="text-muted small mb-2">
                            Showing {{ $leaderboardPaginator->firstItem() }}–{{ $leaderboardPaginator->lastItem() }} of {{ $leaderboardPaginator->total() }}
                        </p>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width:6%">Rank</th>
                                        <th style="width:28%">Subscriber</th>
                                        <th style="width:10%">Role</th>
                                        <th class="text-center" style="width:10%">Status</th>
                                        <th class="text-center" style="width:16%">Plans</th>
                                        <th class="text-end" style="width:12%">₹ active</th>
                                        <th class="text-center" style="width:10%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($leaderboardPaginator as $row)
                                        @php $gRank = ($leaderboardPaginator->firstItem() ?? 0) + $loop->index; @endphp
                                        <tr class="{{ $gRank === 1 ? 'table-warning' : '' }}">
                                            <td class="text-center">
                                                @if($gRank === 1)
                                                    <span class="badge bg-warning text-dark"><i class="fas fa-trophy me-1"></i>#1</span>
                                                @else
                                                    <span class="badge bg-secondary">#{{ $gRank }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                                         style="width:40px;height:40px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);font-size:0.85rem;">
                                                        {{ strtoupper(substr($row->name, 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">{{ $row->name }}</div>
                                                        <small class="text-muted">{{ $row->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-secondary">{{ ucfirst($row->role) }}</span></td>
                                            <td class="text-center">
                                                <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Active</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="fw-semibold">{{ (int) $row->active_subscription_count }} active</div>
                                                @if((int) ($row->total_subscription_count ?? 0) > (int) ($row->active_subscription_count ?? 0))
                                                    <small class="text-muted">{{ (int) $row->total_subscription_count }} total history</small>
                                                @endif
                                            </td>
                                            <td class="text-end fw-bold text-primary">₹{{ number_format((float) ($row->active_revenue_total ?? 0), 2) }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.subscriptions.subscriber', $row) }}" class="btn btn-sm btn-primary"><i class="fas fa-eye me-1"></i>View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $leaderboardPaginator->onEachSide(1)->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div id="sub-records">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h5 class="mb-0 text-dark"><i class="fas fa-layer-group me-2 text-secondary"></i>All subscription records</h5>
            @if($subscriptions->total() > 0)
                <span class="badge rounded-pill bg-light text-dark border px-3 py-2">{{ $subscriptions->total() }} {{ $subscriptions->total() === 1 ? 'record' : 'records' }}</span>
            @endif
        </div>

        <div class="filter-tabs">
            <a href="{{ route('admin.subscriptions', array_merge($tabBase, ['status' => 'all'])) }}"
               class="filter-tab {{ $currentStatus === 'all' || $currentStatus === '' ? 'active' : '' }}">
                All ({{ $counts['all'] ?? 0 }})
            </a>
            <a href="{{ route('admin.subscriptions', array_merge($tabBase, ['status' => 'pending'])) }}"
               class="filter-tab {{ $currentStatus === 'pending' ? 'active' : '' }}">
                Pending ({{ $counts['pending'] ?? 0 }})
            </a>
            <a href="{{ route('admin.subscriptions', array_merge($tabBase, ['status' => 'active'])) }}"
               class="filter-tab {{ $currentStatus === 'active' ? 'active' : '' }}">
                Active ({{ $counts['active'] ?? 0 }})
            </a>
            <a href="{{ route('admin.subscriptions', array_merge($tabBase, ['status' => 'expired'])) }}"
               class="filter-tab {{ $currentStatus === 'expired' ? 'active' : '' }}">
                Expired ({{ $counts['expired'] ?? 0 }})
            </a>
        </div>

        <div class="subscriptions-list">
            @forelse($subscriptions as $subscription)
            <div class="subscription-card">
                <div class="subscription-card-header">
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            @if(isset($rankByUserId[$subscription->user_id]))
                                <span class="badge bg-dark bg-opacity-10 text-dark border" title="Subscriber revenue rank">#{{ $rankByUserId[$subscription->user_id] }}</span>
                            @endif
                            <h5 class="subscription-user-name mb-0">
                                <a href="{{ route('admin.subscriptions.subscriber', $subscription->user_id) }}" class="text-decoration-none text-dark">
                                    {{ $subscription->user->name }}
                                </a>
                            </h5>
                        </div>
                        <p class="subscription-plan-name mb-0 mt-1">{{ $subscription->plan->name }}</p>
                    </div>
                    <span class="badge bg-{{ $subscription->status_color }}">{{ $subscription->status_display }}</span>
                </div>

                <div class="subscription-card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Amount</small>
                            <strong>₹{{ number_format($subscription->total_amount, 0) }}</strong>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Payment status</small>
                            <strong class="text-{{ $subscription->payment_status === 'paid' ? 'success' : 'warning' }}">
                                {{ ucfirst(str_replace('_', ' ', $subscription->payment_status)) }}
                            </strong>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Start</small>
                            <strong>{{ $subscription->start_date->format('M d, Y') }}</strong>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">End</small>
                            <strong>{{ $subscription->end_date->format('M d, Y') }}</strong>
                        </div>
                    </div>

                    @if($subscription->payment_screenshot || $subscription->transaction_id)
                    <div class="payment-proof-section mt-3">
                        <strong class="d-block mb-2"><i class="fas fa-receipt me-2"></i>Payment proof</strong>
                        @if($subscription->payment_screenshot)
                        <a href="{{ route('subscriptions.payment-screenshot', $subscription->id) }}"
                           target="_blank"
                           class="btn btn-sm btn-outline-primary me-2">
                            <i class="fas fa-image me-1"></i>Screenshot
                        </a>
                        @endif
                        @if($subscription->transaction_id)
                        <code class="small">{{ $subscription->transaction_id }}</code>
                        @endif
                    </div>
                    @endif

                    @if($subscription->payment_status !== 'paid' && ($subscription->payment_screenshot || $subscription->transaction_id))
                    <div class="payment-actions mt-3">
                        <form action="{{ route('admin.subscriptions.verify-payment', $subscription) }}"
                              method="POST"
                              class="d-inline me-2">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-check-circle me-1"></i>Verify payment
                            </button>
                        </form>
                        <button type="button"
                                class="btn btn-danger btn-sm"
                                onclick="showRejectModal({{ $subscription->id }})">
                            <i class="fas fa-times-circle me-1"></i>Reject
                        </button>
                    </div>
                    @endif
                </div>

                <div class="subscription-card-footer">
                    <a href="{{ route('admin.subscriptions.view', $subscription) }}"
                       class="btn btn-primary btn-sm">
                        <i class="fas fa-eye me-1"></i>View details
                    </a>
                    <small class="text-muted">Created {{ $subscription->created_at->format('M d, Y') }}</small>
                </div>
            </div>
            @empty
            <div class="alert alert-info border-0 shadow-sm">
                <i class="fas fa-info-circle me-2"></i>No subscriptions match this filter.
            </div>
            @endforelse
        </div>

        @if($subscriptions->hasPages())
        <div class="mt-4">
            {{ $subscriptions->links() }}
        </div>
        @endif
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label for="rejection_reason" class="form-label">Reason</label>
                    <textarea name="rejection_reason"
                              id="rejection_reason"
                              class="form-control"
                              rows="4"
                              required
                              placeholder="Reason for rejection…"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function showRejectModal(subscriptionId) {
    const form = document.getElementById('rejectForm');
    form.action = '/admin/subscriptions/' + subscriptionId + '/reject-payment';
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}
</script>
@endsection
