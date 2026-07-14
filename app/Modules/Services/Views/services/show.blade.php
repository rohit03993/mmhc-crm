@extends('auth::layout')

@section('title', 'Service Request Details - MMHC CRM')

@section('head')
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    @if(auth()->user()->isPatient())
        @include('services::partials.mobile-assets')
    @endif
@endsection

@php
    $backUrl = auth()->user()->isAdmin()
        ? route('admin.service-requests')
        : route('services.my-requests');
    $statusLabel = ucfirst(str_replace('_', ' ', $serviceRequest->status));
    $statusClass = match ($serviceRequest->status) {
        'pending' => 'sr-pill--warning',
        'pending_approval' => 'sr-pill--warning',
        'assigned' => 'sr-pill--info',
        'in_progress' => 'sr-pill--primary',
        'completed' => 'sr-pill--success',
        'cancelled' => 'sr-pill--muted',
        default => 'sr-pill--muted',
    };
@endphp

@section('content')
<div class="sr-detail hc-mobile-shell" data-mmhc-ptr>
    {{-- Mobile top bar --}}
    <div class="sr-detail__mobile-bar mmhc-inverse-surface d-md-none">
        <a href="{{ $backUrl }}" class="sr-detail__icon-btn" aria-label="Back">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <div class="sr-detail__mobile-title">Request #{{ $serviceRequest->id }}</div>
            <div class="sr-detail__mobile-sub">{{ $serviceRequest->serviceType->name }}</div>
        </div>
    </div>

    <div class="sr-detail__inner">
        {{-- Page header (desktop) --}}
        <div class="sr-detail__page-head d-none d-md-flex">
            <div>
                <h1 class="sr-detail__h1">Service request</h1>
                <p class="sr-detail__lede">
                    <span class="text-muted">#{{ $serviceRequest->id }}</span>
                    <span class="sr-detail__dot">·</span>
                    {{ $serviceRequest->serviceType->name }}
                </p>
            </div>
            <a href="{{ $backUrl }}" class="btn btn-light border shadow-sm rounded-pill px-4">
                <i class="fas fa-arrow-left me-2"></i>Back to list
            </a>
        </div>

        <div class="row g-4 align-items-start">
            {{-- Main column --}}
            <div class="col-lg-8">
                {{-- Hero status + amount --}}
                <div class="sr-card sr-card--hero mb-4">
                    <div class="sr-hero__top">
                        <div class="sr-hero__left">
                            <div class="sr-hero__icon sr-hero__icon--{{ $serviceRequest->status }}">
                                <i class="fas fa-{{ $serviceRequest->status === 'pending' ? 'clock' : ($serviceRequest->status === 'assigned' ? 'user-check' : ($serviceRequest->status === 'in_progress' ? 'play-circle' : 'check-circle')) }}"></i>
                            </div>
                            <div>
                                <h2 class="sr-hero__title">{{ $serviceRequest->serviceType->name }}</h2>
                                <span class="sr-pill {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>
                        </div>
                        <div class="sr-hero__amount">
                            <span class="sr-hero__amount-label">Visit charge</span>
                            <span class="sr-hero__amount-value">
                                @if($serviceRequest->isCoveredBySubscription())
                                    <span class="text-success">FREE</span>
                                @else
                                    ₹{{ number_format((float) $serviceRequest->total_amount, 0) }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <div class="sr-card mb-4">
                    <h3 class="sr-card__title"><i class="fas fa-wallet me-2 text-success"></i>Payment</h3>
                    @include('services::partials.payment-summary', ['serviceRequest' => $serviceRequest])
                </div>

                <div class="sr-card mb-4">
                    <h3 class="sr-card__title"><i class="fas fa-info-circle me-2 text-primary"></i>Service information</h3>
                    <div class="row g-3 g-md-4">
                        <div class="col-6 col-md-3">
                            <div class="sr-kv">
                                <span class="sr-kv__label">Duration</span>
                                <span class="sr-kv__val">{{ $serviceRequest->duration_days }} {{ $serviceRequest->duration_days == 1 ? 'day' : 'days' }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="sr-kv">
                                <span class="sr-kv__label">Start date</span>
                                <span class="sr-kv__val">{{ $serviceRequest->start_date->format('M d, Y') }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="sr-kv">
                                <span class="sr-kv__label">End date</span>
                                <span class="sr-kv__val">{{ $serviceRequest->end_date->format('M d, Y') }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="sr-kv">
                                <span class="sr-kv__label">Staff type</span>
                                <span class="sr-kv__val">{{ ucfirst($serviceRequest->preferred_staff_type) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sr-card mb-4">
                    <h3 class="sr-card__title"><i class="fas fa-map-marker-alt me-2 text-danger"></i>Location &amp; contact</h3>
                    <div class="sr-stack">
                        <div class="sr-line">
                            <i class="fas fa-map-marker-alt sr-line__icon"></i>
                            <div>
                                <span class="sr-kv__label d-block">Service location</span>
                                <span class="sr-kv__val">{{ $serviceRequest->location }}</span>
                            </div>
                        </div>
                        <div class="sr-line">
                            <i class="fas fa-user sr-line__icon"></i>
                            <div>
                                <span class="sr-kv__label d-block">Contact person</span>
                                <span class="sr-kv__val">{{ $serviceRequest->contact_person }}</span>
                            </div>
                        </div>
                        <div class="sr-line">
                            <i class="fas fa-phone sr-line__icon"></i>
                            <div>
                                <span class="sr-kv__label d-block">Contact phone</span>
                                <span class="sr-kv__val">{{ $serviceRequest->contact_phone }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($serviceRequest->notes || $serviceRequest->special_requirements)
                <div class="sr-card mb-4">
                    <h3 class="sr-card__title"><i class="fas fa-sticky-note me-2 text-warning"></i>Additional information</h3>
                    @if($serviceRequest->notes)
                    <div class="sr-note mb-3">
                        <span class="sr-kv__label d-block mb-1">Notes</span>
                        <p class="sr-note__body mb-0">{{ $serviceRequest->notes }}</p>
                    </div>
                    @endif
                    @if($serviceRequest->special_requirements)
                    <div class="sr-note">
                        <span class="sr-kv__label d-block mb-1">Special requirements</span>
                        <p class="sr-note__body mb-0">{{ $serviceRequest->special_requirements }}</p>
                    </div>
                    @endif
                </div>
                @endif

                <div class="sr-card sr-card--timeline mb-4 mb-lg-0">
                    <h3 class="sr-card__title"><i class="fas fa-stream me-2 text-info"></i>Request timeline</h3>
                    <ul class="sr-timeline list-unstyled mb-0">
                        <li class="sr-timeline__item">
                            <span class="sr-timeline__dot sr-timeline__dot--primary"></span>
                            <div>
                                <strong class="sr-timeline__label">Request submitted</strong>
                                <div class="sr-timeline__meta">{{ $serviceRequest->created_at->format('M d, Y g:i A') }}</div>
                            </div>
                        </li>
                        @if($serviceRequest->assigned_at)
                        <li class="sr-timeline__item">
                            <span class="sr-timeline__dot sr-timeline__dot--info"></span>
                            <div>
                                <strong class="sr-timeline__label">Staff assigned</strong>
                                <div class="sr-timeline__meta">{{ $serviceRequest->assigned_at->format('M d, Y g:i A') }}</div>
                            </div>
                        </li>
                        @endif
                        @if($serviceRequest->started_at)
                        <li class="sr-timeline__item">
                            <span class="sr-timeline__dot sr-timeline__dot--warning"></span>
                            <div>
                                <strong class="sr-timeline__label">Service started</strong>
                                <div class="sr-timeline__meta">{{ $serviceRequest->started_at->format('M d, Y g:i A') }}</div>
                            </div>
                        </li>
                        @endif
                        @if($serviceRequest->completed_at)
                        <li class="sr-timeline__item">
                            <span class="sr-timeline__dot sr-timeline__dot--success"></span>
                            <div>
                                <strong class="sr-timeline__label">Service completed</strong>
                                <div class="sr-timeline__meta">{{ $serviceRequest->completed_at->format('M d, Y g:i A') }}</div>
                            </div>
                        </li>
                        @endif
                        @if($serviceRequest->cancelled_at)
                        <li class="sr-timeline__item">
                            <span class="sr-timeline__dot sr-timeline__dot--muted"></span>
                            <div>
                                <strong class="sr-timeline__label">Request cancelled</strong>
                                <div class="sr-timeline__meta">{{ $serviceRequest->cancelled_at->format('M d, Y g:i A') }}</div>
                                @if($serviceRequest->cancellation_reason)
                                <div class="sr-timeline__meta mt-1">{{ $serviceRequest->cancellation_reason }}</div>
                                @endif
                            </div>
                        </li>
                        @endif
                    </ul>
                </div>

                @if($serviceRequest->canBeCancelledByPatient())
                <div class="d-md-none mt-3">
                    @include('services::services.partials.cancel-request-form', [
                        'serviceRequest' => $serviceRequest,
                        'compact' => false,
                    ])
                </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                <div class="sr-sidebar">
                    @if(auth()->user()->isAdmin())
                        @include('services::partials.admin-record-collection', ['serviceRequest' => $serviceRequest])
                    @endif

                    @if($serviceRequest->assignedStaff)
                    <div class="sr-card mb-4">
                        <h3 class="sr-card__title"><i class="fas fa-user-md me-2 text-success"></i>Assigned staff</h3>
                        <div class="sr-staff">
                            <div class="sr-staff__avatar {{ $serviceRequest->assignedStaff->isNurse() ? 'sr-staff__avatar--nurse' : 'sr-staff__avatar--caregiver' }}">
                                <i class="fas fa-user-{{ $serviceRequest->assignedStaff->isNurse() ? 'nurse' : 'md' }}"></i>
                            </div>
                            <div class="sr-staff__body">
                                <div class="sr-staff__name">{{ $serviceRequest->assignedStaff->name }}</div>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <span class="badge rounded-pill {{ $serviceRequest->assignedStaff->isNurse() ? 'bg-primary' : 'bg-success' }}">
                                        {{ ucfirst($serviceRequest->assignedStaff->role) }}
                                    </span>
                                    <span class="badge rounded-pill bg-light text-dark border">{{ $serviceRequest->assignedStaff->unique_id }}</span>
                                </div>
                                @if($serviceRequest->assignedStaff->qualification)
                                <div class="sr-staff__meta">
                                    <i class="fas fa-graduation-cap me-1 text-muted"></i>{{ $serviceRequest->assignedStaff->qualification }}
                                </div>
                                @endif
                                @if($serviceRequest->assignedStaff->experience)
                                <div class="sr-staff__meta">
                                    <i class="fas fa-briefcase me-1 text-muted"></i>
                                    @if(is_numeric($serviceRequest->assignedStaff->experience))
                                        {{ $serviceRequest->assignedStaff->experience }} years experience
                                    @else
                                        {{ $serviceRequest->assignedStaff->experience }} experience
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="sr-card sr-card--muted mb-4">
                        <h3 class="sr-card__title text-muted"><i class="fas fa-user-clock me-2"></i>Assigned staff</h3>
                        <p class="mb-0 text-muted small">No staff assigned yet.</p>
                    </div>
                    @endif

                    <div class="sr-card">
                        <h3 class="sr-card__title"><i class="fas fa-bolt me-2 text-secondary"></i>Actions</h3>
                        @if(auth()->user()->isPatient() && $serviceRequest->requiresVisitPayment() && ! $serviceRequest->isVisitPaymentSettled() && ! $serviceRequest->isCancelled())
                        <a href="{{ route('services.pay', $serviceRequest) }}" class="btn btn-success w-100 rounded-3 mb-2 fw-semibold">
                            <i class="fas fa-credit-card me-2"></i>Pay ₹{{ number_format((float) $serviceRequest->total_amount, 2) }}
                        </a>
                        @endif
                        @if($serviceRequest->canBeCancelledByPatient())
                        <div class="mb-2 d-none d-md-block">
                            @include('services::services.partials.cancel-request-form', [
                                'serviceRequest' => $serviceRequest,
                                'compact' => false,
                            ])
                        </div>
                        @elseif($serviceRequest->isCancelled())
                        <div class="alert alert-secondary small mb-2">
                            <i class="fas fa-ban me-1"></i>This request was cancelled
                            @if($serviceRequest->cancelled_at)
                                on {{ $serviceRequest->cancelled_at->format('M d, Y g:i A') }}.
                            @else
                                .
                            @endif
                            @if($serviceRequest->cancellation_reason)
                                <div class="mt-1"><strong>Reason:</strong> {{ $serviceRequest->cancellation_reason }}</div>
                            @endif
                        </div>
                        @endif
                        <a href="{{ $backUrl }}" class="btn btn-outline-secondary w-100 rounded-3">
                            <i class="fas fa-arrow-left me-2"></i>Back to list
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.sr-detail {
    --sr-surface: #ffffff;
    --sr-page: #f1f5f9;
    --sr-border: #e2e8f0;
    --sr-text: #0f172a;
    --sr-muted: #64748b;
    background: var(--sr-page);
    min-height: 100vh;
    margin: -0.5rem -1rem 0;
    padding-bottom: 5rem;
}
@media (min-width: 768px) {
    .sr-detail { margin: 0; padding-bottom: 2rem; }
}
.sr-detail__inner {
    max-width: 1140px;
    margin: 0 auto;
    padding: 1rem 1rem 2rem;
}
@media (min-width: 768px) {
    .sr-detail__inner { padding: 1.5rem 1.25rem 2rem; }
}
.sr-detail__mobile-bar {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: linear-gradient(135deg, #0f766e 0%, #0d9488 55%, #14b8a6 100%);
    color: #fff;
}
.sr-detail__icon-btn {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 999px;
    background: rgba(255,255,255,0.2);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.sr-detail__mobile-title { font-weight: 700; font-size: 0.95rem; }
.sr-detail__mobile-sub { font-size: 0.8rem; opacity: 0.9; }
.sr-detail__page-head {
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.sr-detail__h1 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--sr-text);
    margin: 0 0 0.25rem;
    letter-spacing: -0.02em;
}
.sr-detail__lede {
    margin: 0;
    color: var(--sr-muted);
    font-size: 0.95rem;
}
.sr-detail__dot { opacity: 0.5; }

.sr-card {
    background: var(--sr-surface);
    border: 1px solid var(--sr-border);
    border-radius: 1rem;
    padding: 1.25rem 1.35rem;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}
.sr-card--hero {
    border: none;
    box-shadow: 0 4px 24px rgba(15, 23, 42, 0.08);
}
.sr-card--muted { background: #f8fafc; }
.sr-card__title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--sr-text);
    margin: 0 0 1rem;
    letter-spacing: -0.01em;
}
.sr-card--timeline .sr-card__title { margin-bottom: 1.25rem; }

.sr-hero__top {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}
@media (min-width: 576px) {
    .sr-hero__top {
        flex-direction: row;
        align-items: flex-start;
        justify-content: space-between;
    }
}
.sr-hero__left {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}
.sr-hero__icon {
    width: 3.25rem;
    height: 3.25rem;
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.35rem;
    flex-shrink: 0;
}
.sr-hero__icon--pending { background: linear-gradient(135deg, #f59e0b, #d97706); }
.sr-hero__icon--assigned { background: linear-gradient(135deg, #3b82f6, #2563eb); }
.sr-hero__icon--in_progress { background: linear-gradient(135deg, #6366f1, #4f46e5); }
.sr-hero__icon--completed { background: linear-gradient(135deg, #10b981, #059669); }
.sr-hero__icon--cancelled,
.sr-hero__icon--rejected { background: linear-gradient(135deg, #64748b, #475569); }
.sr-hero__title {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--sr-text);
    margin: 0 0 0.5rem;
    line-height: 1.25;
}
.sr-pill {
    display: inline-block;
    padding: 0.2rem 0.65rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: capitalize;
}
.sr-pill--warning { background: #fef3c7; color: #92400e; }
.sr-pill--info { background: #e0f2fe; color: #0369a1; }
.sr-pill--primary { background: #eef2ff; color: #4338ca; }
.sr-pill--success { background: #d1fae5; color: #047857; }
.sr-pill--muted { background: #f1f5f9; color: #475569; }

.sr-hero__amount {
    text-align: left;
    padding-top: 1rem;
    border-top: 1px solid var(--sr-border);
}
@media (min-width: 576px) {
    .sr-hero__amount {
        text-align: right;
        padding-top: 0;
        border-top: none;
        border-left: 1px solid var(--sr-border);
        padding-left: 1.5rem;
        min-width: 8.5rem;
    }
}
.sr-hero__amount-label {
    display: block;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--sr-muted);
    margin-bottom: 0.35rem;
}
.sr-hero__amount-value {
    font-size: 1.75rem;
    font-weight: 800;
    color: #059669;
    letter-spacing: -0.03em;
    line-height: 1;
}

.sr-kv__label {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--sr-muted);
}
.sr-kv__val {
    display: block;
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--sr-text);
    margin-top: 0.2rem;
}

.sr-stack { display: flex; flex-direction: column; gap: 1.1rem; }
.sr-line {
    display: flex;
    gap: 0.85rem;
    align-items: flex-start;
}
.sr-line__icon {
    color: #0d9488;
    margin-top: 0.15rem;
    width: 1.1rem;
    text-align: center;
}

.sr-note__body {
    font-size: 0.95rem;
    color: #334155;
    line-height: 1.55;
    background: #f8fafc;
    border-radius: 0.65rem;
    padding: 0.85rem 1rem;
    border: 1px solid var(--sr-border);
}

.sr-timeline { position: relative; padding-left: 0.25rem; }
.sr-timeline__item {
    position: relative;
    padding-left: 1.75rem;
    padding-bottom: 1.35rem;
}
.sr-timeline__item:last-child { padding-bottom: 0; }
.sr-timeline__item::before {
    content: '';
    position: absolute;
    left: 0.45rem;
    top: 1.35rem;
    bottom: -0.25rem;
    width: 2px;
    background: var(--sr-border);
}
.sr-timeline__item:last-child::before { display: none; }
.sr-timeline__dot {
    position: absolute;
    left: 0;
    top: 0.2rem;
    width: 1rem;
    height: 1rem;
    border-radius: 999px;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px var(--sr-border);
}
.sr-timeline__dot--primary { background: #6366f1; }
.sr-timeline__dot--info { background: #3b82f6; }
.sr-timeline__dot--warning { background: #f59e0b; }
.sr-timeline__dot--success { background: #10b981; }
.sr-timeline__label { font-size: 0.92rem; color: var(--sr-text); }
.sr-timeline__meta { font-size: 0.82rem; color: var(--sr-muted); margin-top: 0.15rem; }

.sr-staff { display: flex; gap: 1rem; align-items: flex-start; }
.sr-staff__avatar {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.35rem;
    flex-shrink: 0;
}
.sr-staff__avatar--nurse { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
.sr-staff__avatar--caregiver { background: linear-gradient(135deg, #0f766e, #14b8a6); }
.sr-staff__name { font-weight: 700; font-size: 1.05rem; color: var(--sr-text); margin-bottom: 0.25rem; }
.sr-staff__meta { font-size: 0.85rem; color: var(--sr-muted); margin-top: 0.35rem; }

@media (min-width: 992px) {
    .sr-sidebar {
        position: sticky;
        top: 5.5rem;
    }
}
</style>
@endsection
