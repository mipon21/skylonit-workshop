<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportPackage extends Model
{
    use HasFactory;

    public const DURATION_1 = '1';
    public const DURATION_3 = '3';
    public const DURATION_6 = '6';
    public const DURATION_12 = '12';

    public const DURATIONS = [
        self::DURATION_1 => '1 Month',
        self::DURATION_3 => '3 Months',
        self::DURATION_6 => '6 Months',
        self::DURATION_12 => '12 Months',
    ];

    public const PAYMENT_STATUS_DUE = 'due';
    public const PAYMENT_STATUS_PAID = 'paid';

    protected $fillable = [
        'project_id',
        'client_id',
        'package_duration',
        'start_date',
        'end_date',
        'months_count',
        'package_label',
        'amount',
        'payment_status',
        'payment_link',
        'gateway_invoice_id',
        'invoice_number',
        'invoice_path',
        'paid_at',
        'share_cleared_at',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'amount' => 'float',
        'paid_at' => 'datetime',
        'share_cleared_at' => 'datetime',
    ];

    /** Whether the share amount has been cleared (admin internal) */
    public function isShareCleared(): bool
    {
        return $this->share_cleared_at !== null;
    }

    /** Paid packages where share is NOT yet cleared (eligible for Clear Share) */
    public function scopeEligibleForClearShare($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PAID)
            ->whereNull('share_cleared_at');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    public function isDue(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_DUE;
    }

    /** Status for display: Active, Upcoming, Expired (only meaningful when paid) */
    public function getSupportStatusAttribute(): string
    {
        if (! $this->isPaid()) {
            return $this->payment_status === self::PAYMENT_STATUS_DUE ? 'due' : 'pending';
        }
        $today = Carbon::today();
        if ($this->start_date->gt($today)) {
            return 'upcoming';
        }
        if ($this->end_date->lt($today)) {
            return 'expired';
        }
        return 'active';
    }

    public function scopeActive($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PAID)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public function scopeExpiringThisMonth($query)
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();
        return $query->where('payment_status', self::PAYMENT_STATUS_PAID)
            ->whereBetween('end_date', [$start, $end]);
    }

    /** Generate package label from duration and start date */
    public static function generatePackageLabel(string $duration, Carbon $startDate): string
    {
        $months = (int) $duration;
        $endDate = $startDate->copy()->addMonths($months)->subDay();
        $startMonth = $startDate->format('F');
        $startYear = $startDate->format('Y');
        if ($months === 1) {
            return "1 Month ({$startMonth}, {$startYear})";
        }
        $endMonth = $endDate->format('M');
        return "{$months} Months ({$startDate->format('M')}–{$endMonth}, {$startYear})";
    }

    /** Calculate end_date from start_date and duration */
    public static function calculateEndDate(Carbon $startDate, string $duration): Carbon
    {
        return $startDate->copy()->addMonths((int) $duration)->subDay();
    }
}
