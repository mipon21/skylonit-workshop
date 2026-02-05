<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::created(function (Project $project) {
            $project->createDefaultPayouts();
        });
    }

    /** Create default payout records: Overhead and Profit = paid, Sales and Developer = not_paid. */
    public function createDefaultPayouts(): void
    {
        $defaults = [
            ProjectPayout::TYPE_OVERHEAD => ProjectPayout::STATUS_PAID,
            ProjectPayout::TYPE_SALES => ProjectPayout::STATUS_NOT_PAID,
            ProjectPayout::TYPE_DEVELOPER => ProjectPayout::STATUS_NOT_PAID,
            ProjectPayout::TYPE_PROFIT => ProjectPayout::STATUS_PAID,
        ];
        foreach ($defaults as $type => $status) {
            $this->projectPayouts()->firstOrCreate(
                ['type' => $type],
                ['status' => $status]
            );
        }
    }

    public const PROJECT_TYPES = [
        'Application',
        'Website',
        'App+Web',
        'App Bug Fix',
        'Web Bug Fix',
        'App Redesign',
        'Web Redesign',
    ];

    protected $fillable = [
        'client_id',
        'project_name',
        'project_code',
        'project_type',
        'contract_amount',
        'contract_date',
        'delivery_date',
        'status',
        'exclude_from_overhead_profit',
    ];

    protected $casts = [
        'contract_amount' => 'float',
        'contract_date' => 'date',
        'delivery_date' => 'date',
        'exclude_from_overhead_profit' => 'boolean',
    ];

    /** Display ID e.g. SLN-000033 for use in UI. */
    public function getFormattedIdAttribute(): string
    {
        return 'SLN-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function bugs(): HasMany
    {
        return $this->hasMany(Bug::class);
    }

    public function projectNotes(): HasMany
    {
        return $this->hasMany(ProjectNote::class)->orderByDesc('created_at');
    }

    public function projectPayouts(): HasMany
    {
        return $this->hasMany(ProjectPayout::class, 'project_id');
    }

    public function projectLinks(): HasMany
    {
        return $this->hasMany(ProjectLink::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /** Whether this project has a Final Payment (no more payments can be added). */
    public function hasFinalPayment(): bool
    {
        return $this->payments()->where('payment_type', Payment::TYPE_FINAL)->exists();
    }

    /** Available payment types for the next payment (first/middle/final). */
    public function availablePaymentTypes(): array
    {
        if ($this->hasFinalPayment()) {
            return [];
        }
        // Treat null/legacy payments as "first" taken
        $hasFirst = $this->payments()->whereIn('payment_type', [Payment::TYPE_FIRST, null])->exists();

        if (!$hasFirst) {
            return [
                Payment::TYPE_FIRST => 'First Payment',
                Payment::TYPE_MIDDLE => 'Middle Payment',
                Payment::TYPE_FINAL => 'Final Payment',
            ];
        }
        // Multiple middle payments allowed – Middle stays available until Final is chosen
        return [
            Payment::TYPE_MIDDLE => 'Middle Payment',
            Payment::TYPE_FINAL => 'Final Payment',
        ];
    }

    /** Get payout record for a type (overhead, sales, developer, profit), or default not_paid. */
    public function getPayoutFor(string $type): ?ProjectPayout
    {
        return $this->projectPayouts()->where('type', $type)->first();
    }

    /** Total of all expenses for this project. */
    public function getExpenseTotalAttribute(): float
    {
        return round($this->expenses()->sum('amount'), 2);
    }

    /** Net base = Contract − Expenses. Base for revenue split. */
    public function getNetBaseAttribute(): float
    {
        return round($this->contract_amount - $this->expense_total, 2);
    }

    /** True when net_base < 0 (show warning in UI). */
    public function getIsNetBaseNegativeAttribute(): bool
    {
        return $this->net_base < 0;
    }

    /** When true: no Overhead/Profit; only Developer 75% and Sales 25% of (contract − expenses). */
    public function getOverheadAttribute(): float
    {
        if ($this->exclude_from_overhead_profit ?? false) {
            return 0.0;
        }
        return round(max(0, $this->net_base) * config('revenue.overhead'), 2);
    }

    public function getSalesAttribute(): float
    {
        $base = max(0, $this->net_base);
        if ($this->exclude_from_overhead_profit ?? false) {
            // 25% of (total − expense)
            return round($base * 0.25, 2);
        }
        $afterOverhead = $base - $this->overhead;
        return round($afterOverhead * config('revenue.sales'), 2);
    }

    public function getDeveloperAttribute(): float
    {
        $base = max(0, $this->net_base);
        if ($this->exclude_from_overhead_profit ?? false) {
            // 75% of (total − expense)
            return round($base * 0.75, 2);
        }
        return round($base * config('revenue.developer'), 2);
    }

    /** Profit = remainder. Zero when net_base < 0 or when exclude_from_overhead_profit. */
    public function getProfitAttribute(): float
    {
        if ($this->net_base < 0 || ($this->exclude_from_overhead_profit ?? false)) {
            return 0.0;
        }
        return round(
            $this->net_base - $this->overhead - $this->sales - $this->developer,
            2
        );
    }

    /** Only PAID (payment_status) or legacy completed (status) payments count toward total paid. */
    public function getTotalPaidAttribute(): float
    {
        return round($this->payments()->where(function ($q) {
            $q->where('payment_status', Payment::PAYMENT_STATUS_PAID)
                ->orWhere('status', Payment::STATUS_COMPLETED);
        })->sum('amount'), 2);
    }

    public function getDueAttribute(): float
    {
        return round($this->contract_amount - $this->total_paid, 2);
    }

    /**
     * Fraction of contract received (completed payments). 0 = no payment yet, 1 = fully paid.
     * Overhead, Sales, Developer, Profit "fill" based on this.
     */
    public function getRealizedRatioAttribute(): float
    {
        if ($this->contract_amount <= 0) {
            return 0.0;
        }
        return min(1.0, round($this->total_paid / $this->contract_amount, 4));
    }

    /** Overhead counted only as payments are completed (realized). */
    public function getRealizedOverheadAttribute(): float
    {
        return round($this->overhead * $this->realized_ratio, 2);
    }

    /** Sales counted only as payments are completed (realized). */
    public function getRealizedSalesAttribute(): float
    {
        return round($this->sales * $this->realized_ratio, 2);
    }

    /** Developer counted only as payments are completed (realized). */
    public function getRealizedDeveloperAttribute(): float
    {
        return round($this->developer * $this->realized_ratio, 2);
    }

    /** Profit counted only as payments are completed (realized). */
    public function getRealizedProfitAttribute(): float
    {
        return round($this->profit * $this->realized_ratio, 2);
    }

    /** Alias for expense_total (backward compatibility). */
    public function getTotalExpenseAttribute(): float
    {
        return $this->expense_total;
    }

    /** Alias for net_base (backward compatibility). */
    public function getAmountAfterExpensesAttribute(): float
    {
        return $this->net_base;
    }
}
