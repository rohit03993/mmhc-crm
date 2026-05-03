@extends('auth::layout')

@section('title', 'Reward Submissions')
@section('page-title', 'Caregiver & Nurse Rewards')

@section('head')
    <style>
        .rewards-admin-stat {
            border-radius: 12px;
            padding: 1.35rem 1.25rem;
            color: #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        .rewards-admin-stat.purple {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .rewards-admin-stat.teal {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        .rewards-admin-stat.blue {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }
        .rewards-admin-stat .rv { font-size: 1.75rem; font-weight: 700; }
        .rewards-admin-stat .rl { font-size: 0.88rem; opacity: 0.92; }
        .rewards-filter-section {
            background: #fff;
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .rewards-page-guide {
            border-left: 4px solid #7c3aed;
            background: #f8fafc;
            border-radius: 0 10px 10px 0;
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
        }
        .rewards-section-label {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #64748b;
        }
        .main-content .card-header.bg-primary .rewards-leaderboard-count-pill {
            background-color: #f8f9fa !important;
            color: #0f172a !important;
            border: 1px solid rgba(255, 255, 255, 0.35);
        }
        .main-content .card-header.bg-secondary .rewards-log-count-pill {
            background-color: #f8f9fa !important;
            color: #0f172a !important;
        }
        .rewards-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 0.95rem;
            flex-shrink: 0;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="row mb-4 align-items-start">
        <div class="col-lg-8">
            <h2 class="h4 mb-1">
                <i class="fas fa-gift me-2 text-primary"></i>
                Caregiver &amp; Nurse Rewards
            </h2>
            <p class="text-muted small mb-0">
                Staff leaderboard by patient-reward totals, then every submission row-by-row.
                <a href="#rewards-staff-leaderboard" class="text-decoration-none">Leaderboard</a>
                ·
                <a href="#rewards-submissions-log" class="text-decoration-none">Submissions</a>
            </p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="rewards-admin-stat purple">
                <div class="rv">{{ number_format($totalEntryCount) }}</div>
                <div class="rl"><i class="fas fa-database me-1"></i>Total entries (all time)</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="rewards-admin-stat teal">
                <div class="rv">{{ number_format($totalPoints) }}</div>
                <div class="rl"><i class="fas fa-star me-1"></i>Reward points issued</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="rewards-admin-stat blue">
                <div class="rv">₹{{ number_format($totalAmount, 2) }}</div>
                <div class="rl"><i class="fas fa-rupee-sign me-1"></i>Reward value (₹)</div>
            </div>
        </div>
    </div>

    @if($topSubmitter ?? null)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="mb-2 opacity-90"><i class="fas fa-trophy me-2"></i>Top submitter (by total ₹ from patient rewards)</h6>
                                <h5 class="mb-1 fw-bold">{{ $topSubmitter->name }}</h5>
                                <p class="mb-0 small opacity-90">
                                    {{ ucfirst($topSubmitter->role) }}
                                    · {{ (int) $topSubmitter->submissions_count }} submissions
                                    · {{ (int) $topSubmitter->patient_reward_points }} pts
                                    · ₹{{ number_format((float) ($topSubmitter->patient_reward_amount ?? 0), 2) }}
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0 d-flex flex-wrap gap-2 justify-content-md-end">
                                <a href="{{ route('admin.rewards.staff', $topSubmitter) }}" class="btn btn-light">
                                    <i class="fas fa-eye me-1"></i>Full details
                                </a>
                                <a href="{{ route('admin.rewards.index', ['user_id' => $topSubmitter->id]) }}" class="btn btn-outline-light">
                                    <i class="fas fa-table me-1"></i>Table only
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="rewards-filter-section">
        <p class="small text-muted mb-2">
            <i class="fas fa-info-circle me-1 text-primary"></i>
            <strong>Filter</strong> narrows the <strong>submissions table</strong> below. The leaderboard always shows every staff member who has entries.
        </p>
        <form method="GET" action="{{ route('admin.rewards.index') }}" class="row g-2 align-items-end">
            <div class="col-md-6 col-lg-5">
                <label class="form-label fw-semibold mb-1">Show submissions for</label>
                <select name="user_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All staff</option>
                    @foreach($staffWithSubmissions as $staff)
                        <option value="{{ $staff->id }}" {{ ($selectedStaff && (int) $selectedStaff->id === (int) $staff->id) ? 'selected' : '' }}>
                            {{ $staff->name }} ({{ $staff->role }}) — {{ (int) $staff->submissions_count }} · ₹{{ number_format((float) ($staff->patient_reward_amount ?? 0), 2) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                @if($selectedStaff)
                    <a href="{{ route('admin.rewards.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="rewards-page-guide">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="rewards-section-label mb-1">Summary</div>
                <div class="fw-semibold text-dark"><i class="fas fa-medal text-warning me-1"></i> Staff leaderboard</div>
                <p class="small text-muted mb-0">One row per nurse/caregiver — submission count, points, and total ₹ (ranked by ₹, then points).</p>
            </div>
            <div class="col-md-6">
                <div class="rewards-section-label mb-1">Detail</div>
                <div class="fw-semibold text-dark"><i class="fas fa-list-ul text-secondary me-1"></i> Patient submissions</div>
                <p class="small text-muted mb-0">One row per form submitted — patient details, hospital, and reward for that entry.</p>
            </div>
        </div>
    </div>

    <div class="row mb-4" id="rewards-staff-leaderboard">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
                    <div>
                        <div class="rewards-section-label text-white-50 mb-1">Summary · per staff</div>
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Staff patient-reward leaderboard</h5>
                        <small class="text-white-50">Ranked by total ₹ from submissions, then total points</small>
                    </div>
                    <span class="badge rounded-pill fw-bold px-3 py-2 fs-6 shadow-sm rewards-leaderboard-count-pill">
                        {{ $staffWithSubmissions->count() }} {{ $staffWithSubmissions->count() === 1 ? 'staff member' : 'staff members' }}
                    </span>
                </div>
                <div class="card-body">
                    @if($staffWithSubmissions->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                            No patient reward submissions yet.
                        </div>
                    @else
                        <p class="text-muted small mb-2">
                            Showing {{ $leaderboardPaginator->firstItem() }}–{{ $leaderboardPaginator->lastItem() }} of {{ $leaderboardPaginator->total() }}
                        </p>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width:5%">Rank</th>
                                        <th style="width:22%">Staff</th>
                                        <th style="width:10%">Role</th>
                                        <th class="text-center" style="width:12%">Submissions</th>
                                        <th class="text-center" style="width:12%">Points</th>
                                        <th class="text-center" style="width:12%">Total ₹</th>
                                        <th class="text-center" style="width:10%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($leaderboardPaginator as $staff)
                                        @php $globalRank = ($leaderboardPaginator->firstItem() ?? 0) + $loop->index; @endphp
                                        <tr class="{{ $globalRank === 1 ? 'table-warning' : '' }}">
                                            <td class="text-center">
                                                @if($globalRank === 1)
                                                    <span class="badge bg-warning text-dark"><i class="fas fa-trophy me-1"></i>#1</span>
                                                @else
                                                    <span class="badge bg-secondary">#{{ $globalRank }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rewards-avatar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                        {{ strtoupper(substr($staff->name, 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">{{ $staff->name }}</div>
                                                        <small class="text-muted">{{ $staff->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $staff->role === 'nurse' ? 'info' : 'success' }} px-2 py-2">
                                                    {{ ucfirst($staff->role) }}
                                                </span>
                                            </td>
                                            <td class="text-center fw-semibold">{{ (int) $staff->submissions_count }}</td>
                                            <td class="text-center">{{ number_format((int) $staff->patient_reward_points) }}</td>
                                            <td class="text-center">
                                                <span class="fw-bold text-primary">₹{{ number_format((float) ($staff->patient_reward_amount ?? 0), 2) }}</span>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.rewards.staff', $staff) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye me-1"></i>View
                                                </a>
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

    <div class="row" id="rewards-submissions-log">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
                    <div>
                        <div class="rewards-section-label text-white-50 mb-1">Detail · each submission</div>
                        <h5 class="mb-0">
                            <i class="fas fa-table me-2"></i>Submitted patient details
                            @if($selectedStaff)
                                <span class="fw-normal fs-6">— {{ $selectedStaff->name }}</span>
                            @endif
                        </h5>
                        <small class="text-white-50">Newest first; use the filter above to focus on one staff member</small>
                    </div>
                    @if($rewards->total() > 0)
                        <span class="badge rounded-pill fw-bold px-3 py-2 fs-6 shadow-sm rewards-log-count-pill">
                            {{ $rewards->total() }} {{ $rewards->total() === 1 ? 'row' : 'rows' }}
                        </span>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width:4%">#</th>
                                    <th style="width:18%">Submitted by</th>
                                    <th style="width:8%">Role</th>
                                    <th style="width:16%">Patient</th>
                                    <th style="width:12%">Contact</th>
                                    <th style="width:14%">Location</th>
                                    <th style="width:12%">Hospital</th>
                                    <th style="width:8%">Reward</th>
                                    <th style="width:8%">Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rewards as $reward)
                                    <tr>
                                        <td class="text-center">
                                            @if(isset($rankByUserId[$reward->user_id]))
                                                <span class="badge bg-dark bg-opacity-10 text-dark border" title="Leaderboard rank">#{{ $rankByUserId[$reward->user_id] }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.rewards.staff', $reward->user) }}" class="text-decoration-none">
                                                <div class="fw-bold text-dark">{{ $reward->user->name }}</div>
                                                <small class="text-muted text-break">{{ $reward->user->email }}</small>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ ucfirst($reward->user->role) }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $reward->patient_name }}</div>
                                            @if($reward->treatment_details)
                                                <small class="text-muted">{{ Str::limit($reward->treatment_details, 40) }}</small>
                                            @endif
                                            @if($reward->patient_age)
                                                <div class="small text-muted">Age {{ $reward->patient_age }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="font-monospace">{{ $reward->patient_phone }}</small>
                                            @if($reward->patient_email)
                                                <div class="small text-break text-muted">{{ $reward->patient_email }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ Str::limit($reward->patient_address ?? '—', 42) }}</small>
                                            @if($reward->patient_pincode)
                                                <div class="small text-muted">{{ $reward->patient_pincode }}</div>
                                            @endif
                                        </td>
                                        <td><small>{{ Str::limit($reward->hospital_name, 28) }}</small></td>
                                        <td>
                                            <span class="fw-semibold text-success">+{{ $reward->reward_points }}</span>
                                            <small class="text-muted d-block">₹{{ number_format($reward->reward_amount, 2) }}</small>
                                        </td>
                                        <td>
                                            <small class="fw-semibold">{{ $reward->created_at->format('M d, Y') }}</small>
                                            <div class="small text-muted">{{ $reward->created_at->format('h:i A') }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-5">
                                            No submissions{{ $selectedStaff ? ' for this staff member' : '' }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($rewards->hasPages())
                    <div class="card-footer bg-white border-top-0 pt-0">
                        {{ $rewards->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
