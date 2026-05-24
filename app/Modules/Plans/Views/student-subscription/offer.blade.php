@extends('auth::layout')

@section('title', 'Student membership')
@section('page-title', 'Student membership')

@section('content')
@php
    $monthly = (int) ($display['monthly_reference_inr'] ?? 100);
    $years = (int) ($display['duration_years'] ?? 10);
    $listValue = (int) ($display['list_value_inr'] ?? 12000);
    $launchPrice = (int) ($display['launch_price_inr'] ?? 1200);
    $headline = $display['headline'] ?? 'Join the Student Journey';
    $subheadline = $display['subheadline'] ?? '';
@endphp

<div class="container py-4 px-3" style="max-width: 520px;">
    <div class="text-center mb-4">
        <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
            <i class="fas fa-graduation-cap text-primary fa-2x"></i>
        </div>
        <h1 class="h4 fw-bold mb-2">{{ $headline }}</h1>
        @if($subheadline)
            <p class="text-muted small mb-0">{{ $subheadline }}</p>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif

    @if(!$plan)
        <div class="alert alert-warning">
            Student membership is not set up on this server yet. Please contact MMHC support.
        </div>
    @else
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <p class="text-muted small mb-3">Students only · One-time launch offer</p>

                <div class="bg-light rounded-3 p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted">Standard value</span>
                        <span class="text-decoration-line-through text-muted">₹{{ number_format($listValue) }}</span>
                    </div>
                    <p class="small text-muted mb-2">
                        Equivalent to <strong>₹{{ $monthly }}/month</strong> for <strong>{{ $years }} years</strong>
                        (₹{{ number_format($monthly * 12 * $years) }} total).
                    </p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">Launch offer — pay once</span>
                        <span class="fs-4 fw-bold text-success">₹{{ number_format($launchPrice) }}</span>
                    </div>
                </div>

                <ul class="list-unstyled small mb-0">
                    @foreach(($plan->features ?? []) as $feature)
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>{{ $feature }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        @if($pending)
            <a href="{{ route('subscriptions.payment-confirmation', $pending->id) }}" class="btn btn-primary btn-lg w-100 mb-2">
                <i class="fas fa-credit-card me-2"></i>Complete payment (₹{{ number_format((float) $pending->total_amount, 0) }})
            </a>
            <p class="text-center text-muted small">You already started checkout. Tap above to finish payment.</p>
        @else
            <form method="POST" action="{{ route('student-subscription.subscribe') }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="fas fa-rocket me-2"></i>Subscribe now — ₹{{ number_format($launchPrice) }} one-time
                </button>
            </form>
            <p class="text-center text-muted small mt-3 mb-0">
                After payment (online or UPI screenshot), your {{ $years }}-year student membership is activated.
                Institute enrollment approval is separate and may still be pending.
            </p>
        @endif
    @endif
</div>
@endsection
