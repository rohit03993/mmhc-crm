<?php

namespace App\Modules\Plans\Models;

use App\Models\Core\User;
use App\Modules\Plans\Support\DemoSubscriptionNotes;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'plan_id',
        'referrer_id',
        'payment_frequency',
        'status',
        'start_date',
        'end_date',
        'care_benefits_years',
        'payable_years',
        'base_amount',
        'gst_amount',
        'gst_rate',
        'total_amount',
        'paid_amount',
        'payment_status',
        'payment_provider',
        'gateway_status',
        'gateway_payload',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'razorpay_event_id',
        'webhook_received_at',
        'payment_screenshot',
        'transaction_id',
        'payment_notes',
        'payment_verified_by',
        'payment_verified_at',
        'referral_commission_amount',
        'referral_base_amount',
        'referral_growth_percent',
        'referral_dta_percent',
        'referral_payment_processed',
        'referral_payment_processed_at',
        'auto_renew',
        'notes',
        'approved_by',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'base_amount' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'gst_rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'gateway_payload' => 'array',
        'referral_commission_amount' => 'decimal:2',
        'referral_base_amount' => 'decimal:2',
        'referral_growth_percent' => 'decimal:4',
        'referral_dta_percent' => 'decimal:4',
        'referral_payment_processed' => 'boolean',
        'referral_payment_processed_at' => 'datetime',
        'auto_renew' => 'boolean',
        'approved_at' => 'datetime',
        'payment_verified_at' => 'datetime',
        'webhook_received_at' => 'datetime',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the plan that this subscription belongs to.
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get the admin who approved this subscription.
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user that verified the payment.
     */
    public function paymentVerifiedBy()
    {
        return $this->belongsTo(User::class, 'payment_verified_by');
    }

    /**
     * Get the staff member who referred this subscription.
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Get the payments for this subscription.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Scope for active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for pending subscriptions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for expired subscriptions
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Scope for cancelled subscriptions
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Check if subscription is active
     */
    public function isActive()
    {
        return $this->status === 'active' && $this->end_date > now();
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired()
    {
        return $this->end_date < now();
    }

    /**
     * Get total amount paid for this subscription
     */
    public function getTotalPaidAttribute()
    {
        return $this->payments()
            ->where('status', 'completed')
            ->sum('amount');
    }

    /**
     * Get last payment
     */
    public function getLastPaymentAttribute()
    {
        return $this->payments()
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Get whole calendar days remaining until end date (non-negative int).
     */
    public function getDaysRemainingAttribute(): int
    {
        if (! $this->end_date) {
            return 0;
        }

        $today = now()->startOfDay();
        $end = $this->end_date->copy()->startOfDay();
        if ($end->lte($today)) {
            return 0;
        }

        return (int) $today->diffInDays($end);
    }

    /**
     * Get status color for badges
     */
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'active' => 'success',
            'pending' => 'warning',
            'expired' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get human-readable status display
     */
    public function getStatusDisplayAttribute()
    {
        return match ($this->status) {
            'active' => 'Active',
            'pending' => 'Pending',
            'expired' => 'Expired',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status ?? 'Unknown'),
        };
    }

    /**
     * Payment option row from the plan that matches this subscription's frequency (same as /plans).
     */
    public function getSelectedPaymentOptionAttribute(): ?array
    {
        if (! $this->plan) {
            return null;
        }
        $options = $this->plan->payment_options;
        if (! is_array($options)) {
            return null;
        }

        return $options[$this->payment_frequency] ?? null;
    }

    /**
     * Whether payable years, benefit years, and base (ex-GST) match the plan catalogue row for this frequency.
     */
    public function catalogPaymentOptionAlignsWithSubscription(): bool
    {
        $opt = $this->selected_payment_option;
        if (! $opt) {
            return false;
        }

        $subPay = (float) $this->payable_years;
        $subCare = (float) $this->care_benefits_years;
        $optPay = (float) ($opt['payable_years'] ?? 0);
        $optCare = (float) ($opt['care_benefits_years'] ?? 0);

        if (abs($subPay - $optPay) > 0.0001 || abs($subCare - $optCare) > 0.0001) {
            return false;
        }

        $listBase = (float) ($opt['price'] ?? 0);
        $subBase = (float) $this->base_amount;

        if ($listBase > 0 && abs($subBase - $listBase) > 0.05) {
            return false;
        }

        return true;
    }

    /**
     * Normalized payload for patient + admin UIs (single source of truth for “what was sold”).
     *
     * @return array{
     *     frequency_label: string,
     *     catalog_description: ?string,
     *     show_catalog_marketing_line: bool,
     *     catalog_list_price_ex_gst: ?float,
     *     aligned_with_catalog: bool,
     *     invoice_base: float,
     *     invoice_gst: float,
     *     invoice_total: float,
     *     invoice_paid: float,
     *     gst_rate: float,
     *     payable_years: float,
     *     care_benefits_years: float,
     *     care_years_total: float,
     *     recorded_term_summary: ?string,
     *     mismatch_messages: list<string>
     * }
     */
    public function enrolledPackagePresentation(): array
    {
        if (! $this->plan) {
            return [
                'frequency_label' => ucfirst(str_replace('_', ' ', (string) $this->payment_frequency)),
                'catalog_description' => null,
                'show_catalog_marketing_line' => false,
                'catalog_list_price_ex_gst' => null,
                'aligned_with_catalog' => false,
                'invoice_base' => (float) $this->base_amount,
                'invoice_gst' => (float) $this->gst_amount,
                'invoice_total' => (float) $this->total_amount,
                'invoice_paid' => (float) $this->paid_amount,
                'gst_rate' => (float) $this->gst_rate,
                'payable_years' => (float) $this->payable_years,
                'care_benefits_years' => (float) $this->care_benefits_years,
                'care_years_total' => (float) $this->payable_years + (float) $this->care_benefits_years,
                'recorded_term_summary' => null,
                'mismatch_messages' => ['No plan is linked to this subscription.'],
            ];
        }

        $opt = $this->selected_payment_option;
        $frequencyLabel = $opt['label'] ?? ucfirst(str_replace('_', ' ', (string) $this->payment_frequency));

        $subPay = (float) $this->payable_years;
        $subCare = (float) $this->care_benefits_years;
        $subBase = (float) $this->base_amount;

        $optPay = $opt !== null ? (float) ($opt['payable_years'] ?? 0) : 0.0;
        $optCare = $opt !== null ? (float) ($opt['care_benefits_years'] ?? 0) : 0.0;
        $listBase = $opt !== null ? (float) ($opt['price'] ?? 0) : 0.0;

        $yearsMatch = $opt !== null
            && abs($subPay - $optPay) < 0.0001
            && abs($subCare - $optCare) < 0.0001;
        $priceMatch = $opt === null || $listBase <= 0 || abs($subBase - $listBase) < 0.05;
        $aligned = $opt !== null && $yearsMatch && $priceMatch;

        $mismatchMessages = [];
        if ($opt === null && $this->plan) {
            $mismatchMessages[] = 'Payment frequency «'.(string) $this->payment_frequency.'» is not defined on this plan. Correct the subscription or update plan payment options.';
        } elseif ($opt !== null && ! $aligned) {
            if (! $yearsMatch) {
                $mismatchMessages[] = sprintf(
                    'Care term on file (%s payable + %s benefit years) does not match the catalogue for %s (%s + %s years).',
                    $this->formatYearCount($subPay),
                    $this->formatYearCount($subCare),
                    $frequencyLabel,
                    $this->formatYearCount($optPay),
                    $this->formatYearCount($optCare)
                );
            }
            if (! $priceMatch && $listBase > 0) {
                $mismatchMessages[] = sprintf(
                    'Invoiced base (₹%s) does not match the catalogue list price (₹%s) for this tier.',
                    number_format($subBase, 2),
                    number_format($listBase, 2)
                );
            }
        }

        $totalCare = $subPay + $subCare;
        $recordedTermSummary = null;
        if ($totalCare > 0.0001) {
            $recordedTermSummary = sprintf(
                'Coverage term on this subscription: %s payable year(s) and %s bundled benefit year(s) (%s total), aligned with the end date below.',
                $this->formatYearCount($subPay),
                $this->formatYearCount($subCare),
                $this->formatYearCount($totalCare)
            );
        } else {
            $recordedTermSummary = 'Billed on the selected schedule; the active coverage window is defined by the start and end dates below.';
        }

        $catalogDesc = is_array($opt) ? ($opt['description'] ?? null) : null;
        $catalogDesc = is_string($catalogDesc) ? $catalogDesc : null;

        return [
            'frequency_label' => $frequencyLabel,
            'catalog_description' => $catalogDesc,
            'show_catalog_marketing_line' => $aligned && $catalogDesc !== null && $catalogDesc !== '',
            'catalog_list_price_ex_gst' => $listBase > 0 ? $listBase : null,
            'aligned_with_catalog' => $aligned,
            'invoice_base' => (float) $this->base_amount,
            'invoice_gst' => (float) $this->gst_amount,
            'invoice_total' => (float) $this->total_amount,
            'invoice_paid' => (float) $this->paid_amount,
            'gst_rate' => (float) $this->gst_rate,
            'payable_years' => $subPay,
            'care_benefits_years' => $subCare,
            'care_years_total' => $totalCare,
            'recorded_term_summary' => $recordedTermSummary,
            'mismatch_messages' => $mismatchMessages,
        ];
    }

    private function formatYearCount(float $years): string
    {
        if (abs($years - round($years)) < 0.0001) {
            return (string) (int) round($years);
        }

        return (string) $years;
    }

    /**
     * Plan feature bullets for display: omit static "N years total care" lines that conflict with the enrolled payment tier.
     *
     * @return list<string>
     */
    public function planFeaturesForDisplay(): array
    {
        $features = $this->plan->features ?? [];
        if ($features instanceof \Illuminate\Support\Collection) {
            $features = $features->all();
        }
        if (is_string($features)) {
            $decoded = json_decode($features, true);
            $features = is_array($decoded) ? $decoded : [];
        }
        if (! is_array($features)) {
            return [];
        }

        $out = [];
        foreach ($features as $feature) {
            $text = is_string($feature) ? $feature : (string) ($feature['label'] ?? $feature['name'] ?? '');
            if ($text === '') {
                continue;
            }
            if (preg_match('/\d+\s*years?\s+total\s+care\s+coverage/i', $text)) {
                continue;
            }

            $out[] = $text;
        }

        return $out;
    }

    /**
     * Row was created by a demo seeder (notes marker). Safe for demo-only prune / catalogue resync.
     */
    public function isDemoSeeded(): bool
    {
        return DemoSubscriptionNotes::isDemo($this->notes);
    }
}
