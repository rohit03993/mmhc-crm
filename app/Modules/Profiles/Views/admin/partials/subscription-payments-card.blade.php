@if(isset($subscriptionPaymentHistory) && $subscriptionPaymentHistory->isNotEmpty())
<div class="apv-card mb-4">
    <h2 class="apv-card__title d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span><i class="fas fa-receipt me-2 text-success"></i>Payments &amp; subscriptions</span>
        <span class="badge bg-light text-dark border rounded-pill">{{ $subscriptionPaymentHistory->count() }} paid</span>
    </h2>
    <p class="small text-muted mb-3">Money received from this user (membership or healthcare plans). Amounts reflect actual payment after coupons.</p>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 apv-table">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Plan</th>
                    <th class="text-end">Paid</th>
                    <th>Method</th>
                    <th>Processed by</th>
                    <th class="text-end">Proof</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subscriptionPaymentHistory as $row)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $row['paid_at']?->format('d M Y') ?? '—' }}</div>
                            @if($row['transaction_id'])
                                <small class="text-muted d-block text-truncate" style="max-width: 10rem;" title="{{ $row['transaction_id'] }}">Txn: {{ $row['transaction_id'] }}</small>
                            @endif
                        </td>
                        <td><span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">{{ $row['type_label'] }}</span></td>
                        <td>
                            <div class="fw-semibold">{{ $row['plan_name'] }}</div>
                            @if($row['coupon_code'])
                                <small class="text-muted">Coupon {{ $row['coupon_code'] }} (−₹{{ number_format($row['discount_amount'], 2) }})</small>
                            @endif
                        </td>
                        <td class="text-end">
                            <span class="fw-semibold text-success">₹{{ number_format($row['paid_amount'], 2) }}</span>
                            @if($row['discount_amount'] > 0 && $row['list_amount'] > $row['paid_amount'])
                                <small class="text-muted d-block">List ₹{{ number_format($row['list_amount'], 2) }}</small>
                            @endif
                        </td>
                        <td>{{ $row['method_label'] }}</td>
                        <td><small>{{ $row['verified_by_label'] }}</small></td>
                        <td class="text-end text-nowrap">
                            <a href="{{ $row['admin_detail_url'] }}" class="btn btn-sm btn-outline-secondary rounded-pill me-1">Details</a>
                            @if($row['invoice_url'])
                                <a href="{{ $row['invoice_url'] }}" class="btn btn-sm btn-outline-primary rounded-pill" target="_blank" rel="noopener">
                                    <i class="fas fa-file-invoice"></i> Invoice
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@elseif(in_array($user->role ?? '', ['student', 'patient'], true))
<div class="apv-card mb-4">
    <h2 class="apv-card__title"><i class="fas fa-receipt me-2 text-muted"></i>Payments &amp; subscriptions</h2>
    <p class="small text-muted mb-0">No completed subscription payments on record yet.</p>
</div>
@endif
