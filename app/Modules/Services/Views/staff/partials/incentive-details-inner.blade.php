@php
    $tid = $tabIdPrefix ?? 'inv';
@endphp

<div class="row g-3 mb-3 idv-kpi-row row-cols-2 row-cols-lg-5">
    <div class="col">
        <div class="card h-100 border-0 rounded-4 idv-kpi idv-kpi--visits">
            <div class="card-body py-3 px-3">
                <div class="idv-kpi__label">Total visits</div>
                <div class="idv-kpi__value">{{ $serviceSummary['count'] }}</div>
                <div class="idv-kpi__hint">Latest visit no: {{ $serviceSummary['latest_service_count'] }}</div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card h-100 border-0 rounded-4 idv-kpi idv-kpi--service">
            <div class="card-body py-3 px-3">
                <div class="idv-kpi__label">Service incentive</div>
                <div class="idv-kpi__value idv-kpi__value--money text-success">₹{{ number_format($serviceSummary['final_total'], 2) }}</div>
                <div class="idv-kpi__hint">Base ₹{{ number_format($serviceSummary['base_total'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card h-100 border-0 rounded-4 idv-kpi idv-kpi--subscription">
            <div class="card-body py-3 px-3">
                <div class="idv-kpi__label">Subscription</div>
                <div class="idv-kpi__value idv-kpi__value--money">₹{{ number_format($subscriptionSummaryAmount, 2) }}</div>
                <div class="idv-kpi__hint">{{ $subscriptionSummaryCount }} records</div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card h-100 border-0 rounded-4 idv-kpi idv-kpi--referrals">
            <div class="card-body py-3 px-3">
                <div class="idv-kpi__label">Staff referrals</div>
                <div class="idv-kpi__value idv-kpi__value--money">₹{{ number_format($staffReferralTotalAmount, 2) }}</div>
                <div class="idv-kpi__hint">{{ $staffReferralTotalCount }} × ₹{{ number_format($staffReferralBasePerReferral, 0) }} base</div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card h-100 border-0 rounded-4 idv-kpi idv-kpi--patient-reward">
            <div class="card-body py-3 px-3">
                <div class="idv-kpi__label">Patient rewards</div>
                <div class="idv-kpi__value idv-kpi__value--money">₹{{ number_format($patientRewardsTotalAmount ?? 0, 2) }}</div>
                <div class="idv-kpi__hint">Unpaid ₹{{ number_format($patientRewardsPendingAmount ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
</div>
@if(isset($combinedLedgerAndPatientRewards))
<div class="d-flex flex-wrap align-items-baseline justify-content-between gap-2 mb-4 px-1">
    <span class="small text-muted text-uppercase fw-semibold" style="letter-spacing: 0.06em;">Total</span>
    <span class="h5 mb-0 text-success fw-bold">₹{{ number_format($combinedLedgerAndPatientRewards, 2) }}</span>
</div>
@endif

<ul class="nav nav-pills flex-wrap gap-1 gap-md-2 mb-3 idv-nav p-2 rounded-4" id="{{ $tid }}-tabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active rounded-pill px-3 py-2" id="{{ $tid }}-services-tab" data-bs-toggle="tab" data-bs-target="#{{ $tid }}-services-pane" type="button" role="tab" aria-controls="{{ $tid }}-services-pane" aria-selected="true">Services</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill px-3 py-2" id="{{ $tid }}-subscription-tab" data-bs-toggle="tab" data-bs-target="#{{ $tid }}-subscription-pane" type="button" role="tab" aria-controls="{{ $tid }}-subscription-pane" aria-selected="false">Subscription</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill px-3 py-2" id="{{ $tid }}-staff-ref-tab" data-bs-toggle="tab" data-bs-target="#{{ $tid }}-staff-ref-pane" type="button" role="tab" aria-controls="{{ $tid }}-staff-ref-pane" aria-selected="false">Staff referrals</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill px-3 py-2" id="{{ $tid }}-reward-tab" data-bs-toggle="tab" data-bs-target="#{{ $tid }}-reward-pane" type="button" role="tab" aria-controls="{{ $tid }}-reward-pane" aria-selected="false">Patient rewards</button>
    </li>
</ul>

<div class="tab-content idv-tab-content" id="{{ $tid }}-tabsContent">
    <div class="tab-pane fade show active" id="{{ $tid }}-services-pane" role="tabpanel" aria-labelledby="{{ $tid }}-services-tab">
        <div class="card mb-3 border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3">
                <strong>Service visit-wise incentives</strong>
                <div class="small text-muted">Each row is one visit; visit number = service_count_at_event.</div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Visit no</th>
                                <th>Service ID</th>
                                <th>Patient</th>
                                <th>Type</th>
                                <th>Base</th>
                                <th>Growth %</th>
                                <th>DtA %</th>
                                <th>Final</th>
                                <th class="pe-3">Settled</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($serviceLedgers as $ledger)
                                @php $service = $ledger->sourceServiceRequest; @endphp
                                <tr>
                                    <td class="ps-3">{{ $loop->iteration }}</td>
                                    <td><span class="badge rounded-pill bg-primary">{{ $ledger->service_count_at_event }}</span></td>
                                    <td>{{ $ledger->source_id }}</td>
                                    <td>{{ optional($service?->patient)->name ?? '—' }}</td>
                                    <td>{{ optional($service?->serviceType)->name ?? '—' }}</td>
                                    <td>₹{{ number_format((float) $ledger->base_amount, 2) }}</td>
                                    <td>{{ number_format((float) $ledger->growth_percent, 2) }}</td>
                                    <td>{{ number_format((float) $ledger->dta_percent, 2) }}</td>
                                    <td class="fw-semibold">₹{{ number_format((float) $ledger->final_amount, 2) }}</td>
                                    <td class="pe-3">
                                        <span class="badge rounded-pill {{ $ledger->payment_settled ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ $ledger->payment_settled ? 'Paid' : 'Pending' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">No service incentive records.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-2 border-top bg-light">{{ $serviceLedgers->links() }}</div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="{{ $tid }}-subscription-pane" role="tabpanel" aria-labelledby="{{ $tid }}-subscription-tab">
        <div class="card mb-3 border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3"><strong>Subscription referral incentives</strong></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Subscription ID</th>
                                <th>Patient</th>
                                <th>Plan</th>
                                <th>Base</th>
                                <th>Growth %</th>
                                <th>DtA %</th>
                                <th>Final</th>
                                <th class="pe-3">Settled</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subscriptionLedgers as $ledger)
                                @php $sub = $ledger->sourceSubscription; @endphp
                                <tr>
                                    <td class="ps-3">{{ $loop->iteration }}</td>
                                    <td>{{ $ledger->source_id }}</td>
                                    <td>{{ optional($sub?->user)->name ?? '—' }}</td>
                                    <td>{{ optional($sub?->plan)->name ?? '—' }}</td>
                                    <td>₹{{ number_format((float) $ledger->base_amount, 2) }}</td>
                                    <td>{{ number_format((float) $ledger->growth_percent, 2) }}</td>
                                    <td>{{ number_format((float) $ledger->dta_percent, 2) }}</td>
                                    <td class="fw-semibold">₹{{ number_format((float) $ledger->final_amount, 2) }}</td>
                                    <td class="pe-3">
                                        <span class="badge rounded-pill {{ $ledger->payment_settled ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ $ledger->payment_settled ? 'Paid' : 'Pending' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            @foreach($legacySubscriptions as $legacy)
                                <tr>
                                    <td class="ps-3">{{ $loop->iteration }}</td>
                                    <td>{{ $legacy->id }}</td>
                                    <td>{{ optional($legacy->user)->name ?? '—' }}</td>
                                    <td>{{ optional($legacy->plan)->name ?? '—' }}</td>
                                    <td>—</td>
                                    <td>—</td>
                                    <td>—</td>
                                    <td class="fw-semibold">₹{{ number_format((float) $legacy->referral_commission_amount, 2) }}</td>
                                    <td class="pe-3">
                                        <span class="badge rounded-pill {{ $legacy->referral_payment_processed ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ $legacy->referral_payment_processed ? 'Paid' : 'Pending' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            @if($subscriptionLedgers->total() === 0 && $legacySubscriptions->total() === 0)
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">No subscription incentive records.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="p-2 border-top bg-light">
                    <div class="mb-2">{{ $subscriptionLedgers->links() }}</div>
                    {{ $legacySubscriptions->links() }}
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="{{ $tid }}-staff-ref-pane" role="tabpanel" aria-labelledby="{{ $tid }}-staff-ref-tab">
        <div class="card mb-3 border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3"><strong>Staff referral details</strong></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Referral ID</th>
                                <th>Referred user</th>
                                <th>Completed</th>
                                <th class="pe-3">Base</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staffReferrals as $referral)
                                <tr>
                                    <td class="ps-3">{{ $loop->iteration }}</td>
                                    <td>{{ $referral->id }}</td>
                                    <td>{{ optional($referral->referred)->name ?? '—' }}</td>
                                    <td>{{ optional($referral->completed_at)->format('M d, Y') ?? '—' }}</td>
                                    <td class="pe-3">₹{{ number_format($staffReferralBasePerReferral, 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No staff referral records.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-2 border-top bg-light">{{ $staffReferrals->links() }}</div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="{{ $tid }}-reward-pane" role="tabpanel" aria-labelledby="{{ $tid }}-reward-tab">
        <div class="card mb-3 border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3"><strong>Patient reward details</strong></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Reward ID</th>
                                <th>Patient</th>
                                <th>Points</th>
                                <th class="pe-3">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($patientRewards as $reward)
                                <tr>
                                    <td class="ps-3">{{ $loop->iteration }}</td>
                                    <td>{{ $reward->id }}</td>
                                    <td>{{ $reward->patient_name ?? '—' }}</td>
                                    <td>{{ $reward->reward_points }}</td>
                                    <td class="pe-3">₹{{ number_format((float) $reward->reward_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No patient reward records.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-2 border-top bg-light">{{ $patientRewards->links() }}</div>
            </div>
        </div>
    </div>
</div>

<style>
/* Incentive KPI + tabs (profile embed + full incentive page) */
.idv-kpi {
    background: linear-gradient(165deg, #ffffff 0%, #f8fafc 100%);
    box-shadow: 0 4px 18px rgba(15, 23, 42, 0.07), 0 0 0 1px rgba(15, 23, 42, 0.04);
    transition: transform 0.18s ease, box-shadow 0.18s ease;
    position: relative;
    overflow: hidden;
}
.idv-kpi::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 4px;
    border-radius: 4px 0 0 4px;
}
.idv-kpi:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 28px rgba(15, 23, 42, 0.1), 0 0 0 1px rgba(15, 23, 42, 0.06);
}
.idv-kpi--visits::before { background: linear-gradient(180deg, #0ea5e9, #0369a1); }
.idv-kpi--service::before { background: linear-gradient(180deg, #34d399, #059669); }
.idv-kpi--subscription::before { background: linear-gradient(180deg, #a78bfa, #6d28d9); }
.idv-kpi--referrals::before { background: linear-gradient(180deg, #fb923c, #c2410c); }
.idv-kpi--patient-reward::before { background: linear-gradient(180deg, #fbbf24, #d97706); }
.idv-kpi__label {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 0.35rem;
}
.idv-kpi__value {
    font-size: 1.65rem;
    font-weight: 800;
    line-height: 1.15;
    color: #0f172a;
    letter-spacing: -0.03em;
}
.idv-kpi__value--money { font-size: 1.2rem; }
@media (min-width: 768px) {
    .idv-kpi__value--money { font-size: 1.35rem; }
}
.idv-kpi__hint { font-size: 0.78rem; color: #64748b; margin-top: 0.35rem; }
.idv-nav {
    background: #f1f5f9;
    border: 1px solid #e2e8f0 !important;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.7);
}
.idv-nav .nav-link {
    color: #475569;
    font-weight: 600;
    font-size: 0.8125rem;
    border: 1px solid transparent;
}
.idv-nav .nav-link:hover { color: #0f766e; background: rgba(255,255,255,0.65); }
.idv-nav .nav-link.active {
    color: #fff !important;
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 55%, #115e59 100%) !important;
    border-color: transparent !important;
    box-shadow: 0 3px 12px rgba(13, 148, 136, 0.35);
}
.idv-tab-content {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    padding: 0.5rem;
    box-shadow: 0 2px 12px rgba(15, 23, 42, 0.05);
}
@media (min-width: 768px) {
    .idv-tab-content { padding: 0.75rem 1rem 1rem; }
}
.idv-tab-content .card { border: 1px solid #e2e8f0 !important; }
</style>
