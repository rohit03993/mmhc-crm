@extends('auth::layout')

@section('title', 'Incentive rules preview - MMHC CRM')
@section('page-title', 'Incentive system')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3 py-md-4">
    {{-- Page hero --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 incentive-preview-hero">
        <div class="card-body p-4 p-md-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-shrink-0 rounded-3 d-flex align-items-center justify-content-center incentive-preview-hero__icon">
                            <i class="fas fa-sliders-h fa-lg"></i>
                        </div>
                        <div>
                            <h1 class="h4 fw-bold mb-2 text-dark">Rule book (read-only)</h1>
                            <p class="text-muted mb-0 small lh-lg mb-2">
                                This screen lists <strong>global payout rules</strong> — slabs, subscription commission %, and per–visit base rates.
                                It does <strong>not</strong> list staff or patient earnings. Those appear only after real events (e.g. admin approves a completed service, subscription referral, ledger entries).
                            </p>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <a href="{{ route('admin.users') }}" class="btn btn-primary btn-sm rounded-pill">
                                    <i class="fas fa-users me-1"></i> Manage users
                                </a>
                                <span class="text-muted small align-self-center">
                                    Open a <strong>nurse</strong> or <strong>caregiver</strong> → use their profile actions for <strong>Incentives</strong> / earnings drill-down.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="rounded-4 bg-white bg-opacity-75 border border-white border-opacity-25 p-3 text-center">
                        <div class="text-muted small text-uppercase letter-spacing-1 mb-1">Ledger rows (all staff)</div>
                        <div class="display-6 fw-bold text-primary lh-1">{{ number_format($ledgerEntryCount ?? 0) }}</div>
                        <div class="small text-muted mt-2">
                            @if(($ledgerEntryCount ?? 0) === 0)
                                No payout lines yet — approve eligible services or run flows that create ledger entries.
                            @else
                                Stored incentive ledger rows in this database.
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!$ruleSet)
        <div class="alert alert-warning border-0 shadow-sm rounded-4 d-flex align-items-start gap-3">
            <i class="fas fa-exclamation-triangle mt-1"></i>
            <div>
                <strong>No active incentive rule set.</strong>
                Load defaults locally with:
                <code class="user-select-all">php artisan db:seed --class=IncentiveRuleSetSeeder</code>
            </div>
        </div>
    @else
        {{-- Rule set meta --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">Active rule set</div>
                        <div class="fw-bold">{{ $ruleSet->name }}</div>
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis mt-2">{{ $ruleSet->code }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">Effective from</div>
                        <div class="fw-bold">{{ $ruleSet->effective_from->format('d M Y') }}</div>
                        @if($ruleSet->effective_to)
                            <div class="small text-muted mt-1">Until {{ $ruleSet->effective_to->format('d M Y') }}</div>
                        @else
                            <div class="small text-muted mt-1">No end date</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">Money rounding</div>
                        <div class="fw-bold">{{ $ruleSet->round_decimals }} decimal places</div>
                        <div class="small text-muted mt-1">Applied to calculated amounts</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h2 class="h6 fw-bold mb-0">
                            <i class="fas fa-chart-line text-primary me-2"></i>
                            Growth + DtA slabs
                        </h2>
                        <small class="text-muted">By cumulative approved services count at payout time</small>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 incentive-preview-table">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Min services</th>
                                        <th>Max (exclusive)</th>
                                        <th class="text-end">Growth %</th>
                                        <th class="text-end pe-4">DtA %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($slabs as $s)
                                    <tr>
                                        <td class="ps-4 font-monospace">{{ $s->min_inclusive }}</td>
                                        <td class="text-muted font-monospace">{{ $s->max_exclusive ?? '—' }}</td>
                                        <td class="text-end font-monospace">{{ number_format((float) $s->growth_percent, 2) }}</td>
                                        <td class="text-end pe-4 font-monospace">{{ number_format((float) $s->dta_percent, 2) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h2 class="h6 fw-bold mb-0">
                            <i class="fas fa-receipt text-primary me-2"></i>
                            Subscription sale commission
                        </h2>
                        <small class="text-muted">% of plan base (excl. GST, per your product rules)</small>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 incentive-preview-table">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Payment frequency</th>
                                        <th class="text-end pe-4">Commission %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($subscriptionRates as $r)
                                    <tr>
                                        <td class="ps-4"><span class="badge rounded-pill bg-light text-dark border">{{ $r->payment_frequency }}</span></td>
                                        <td class="text-end pe-4 font-monospace fw-semibold">{{ number_format((float) $r->commission_percent, 2) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h2 class="h6 fw-bold mb-0">
                            <i class="fas fa-user-nurse text-primary me-2"></i>
                            Service base rates (before growth + DtA)
                        </h2>
                        <small class="text-muted">Per visit or per day unit — multiplied by duration where applicable</small>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 incentive-preview-table small">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Visit kind</th>
                                        <th>Tier</th>
                                        <th>Subscriber patient</th>
                                        <th>Unit</th>
                                        <th class="text-end pe-4">₹ / unit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($serviceRates as $r)
                                    <tr>
                                        <td class="ps-4"><code class="small">{{ $r->visit_kind }}</code></td>
                                        <td><span class="badge bg-primary-subtle text-primary-emphasis">{{ $r->experience_tier }}</span></td>
                                        <td>{{ $r->is_subscriber_patient ? 'Yes' : 'No' }}</td>
                                        <td class="text-muted">{{ $r->unit }}</td>
                                        <td class="text-end pe-4 font-monospace fw-semibold">₹ {{ number_format((float) $r->rate_per_unit, 2) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .incentive-preview-hero {
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.08) 0%, rgba(99, 102, 241, 0.12) 50%, rgba(15, 23, 42, 0.04) 100%);
        border: 1px solid rgba(148, 163, 184, 0.25);
    }
    .incentive-preview-hero__icon {
        width: 3rem;
        height: 3rem;
        background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
        color: #fff;
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.35);
    }
    .incentive-preview-table thead th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        font-weight: 600;
        color: #64748b;
    }
    .letter-spacing-1 { letter-spacing: 0.06em; }
</style>
@endsection
