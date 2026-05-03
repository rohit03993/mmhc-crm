<?php

namespace App\Modules\Incentives\Models;

use App\Models\Core\User;
use App\Modules\Payments\Models\StaffPayment;
use App\Modules\Plans\Models\Subscription;
use App\Modules\Services\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncentiveLedger extends Model
{
    protected $table = 'incentive_ledger';

    protected $fillable = [
        'rule_set_id', 'staff_id', 'source_type', 'source_id', 'base_amount', 'service_count_at_event',
        'snapshot_visit_kind', 'snapshot_experience_tier', 'snapshot_subscriber_patient',
        'growth_percent', 'dta_percent', 'pre_adjustment_amount', 'adjustment_amount', 'adjustment_reason',
        'final_amount', 'payment_settled', 'settled_at', 'staff_payment_id',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'pre_adjustment_amount' => 'decimal:2',
        'adjustment_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'growth_percent' => 'decimal:4',
        'dta_percent' => 'decimal:4',
        'snapshot_subscriber_patient' => 'boolean',
        'payment_settled' => 'boolean',
        'settled_at' => 'datetime',
    ];

    public const SOURCE_SERVICE_REQUEST = 'service_request';

    public const SOURCE_SUBSCRIPTION_SALE = 'subscription_sale';

    public const SOURCE_REFERRAL = 'referral';

    public function ruleSet(): BelongsTo
    {
        return $this->belongsTo(IncentiveRuleSet::class, 'rule_set_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function staffPayment(): BelongsTo
    {
        return $this->belongsTo(StaffPayment::class, 'staff_payment_id');
    }

    public function sourceSubscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'source_id');
    }

    public function sourceServiceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'source_id');
    }
}
