{{-- Nurse / caregiver: share live GPS so patients can find them nearby --}}
@php
    $staffHasGps = \App\Modules\Auth\Services\LocationService::hasUsableCoordinates(
        auth()->user()->latitude !== null ? (float) auth()->user()->latitude : null,
        auth()->user()->longitude !== null ? (float) auth()->user()->longitude : null
    );
@endphp
<style>
    .staff-location-panel--share {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 0.85rem 1rem;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
    }
    .staff-location-panel--share .staff-location-banner {
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
        margin-bottom: 0.75rem;
    }
    .staff-location-panel--share .staff-location-banner i {
        color: #4338ca;
        margin-top: 0.15rem;
    }
    .staff-location-panel--share .staff-location-banner strong {
        display: block;
        font-size: 0.9rem;
        color: #0f172a;
    }
    .staff-location-panel--share .staff-location-banner p {
        margin: 0.2rem 0 0;
        font-size: 0.8rem;
        color: #64748b;
    }
    .staff-location-panel--share .staff-location-banner--active i { color: #0f766e; }
    .staff-location-panel--share .staff-location-btn {
        appearance: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        width: 100%;
        min-height: 44px;
        border: 0;
        border-radius: 12px;
        background: linear-gradient(135deg, #4338ca 0%, #6366f1 100%);
        color: #fff;
        font-weight: 700;
        font-size: 0.9rem;
    }
    .staff-location-panel--share .staff-location-btn.is-loading { opacity: 0.75; }
    .staff-location-panel--share .staff-location-status {
        margin-top: 0.55rem;
        font-size: 0.8rem;
        color: #475569;
    }
    .staff-location-panel--share .staff-location-status--success { color: #15803d; }
    .staff-location-panel--share .staff-location-status--error { color: #b91c1c; }
</style>
<div class="staff-location-panel staff-location-panel--share mb-3"
     data-auto-locate="{{ $staffHasGps ? '0' : '1' }}">
    @if($staffHasGps)
    <div class="staff-location-banner staff-location-banner--active">
        <i class="fas fa-map-marker-alt"></i>
        <div>
            <strong>Patients can find you near your last shared location</strong>
            <p>Update your live GPS when you move so nearby booking stays accurate.</p>
        </div>
    </div>
    @else
    <div class="staff-location-banner">
        <i class="fas fa-location-crosshairs"></i>
        <div>
            <strong>Share your current location</strong>
            <p>Patients search by live GPS. Without this, you may not appear in Find staff near me.</p>
        </div>
    </div>
    @endif

    <div class="staff-location-actions">
        <button type="button"
                id="btnUseMyLocation"
                class="staff-location-btn"
                data-mode="staff"
                data-resolve-url="{{ route('staff.update-location') }}">
            <i class="fas fa-crosshairs"></i>
            <span class="btn-label">{{ $staffHasGps ? 'Update my location' : 'Share current location' }}</span>
        </button>
    </div>
    <div id="staffLocationStatus" class="staff-location-status" hidden></div>
</div>
<script src="{{ asset('js/staff-location.js') }}?v=20260724a" defer></script>
