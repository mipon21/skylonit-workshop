<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'order_id',
        'project_type',
        'payment_method',
        'contract_amount',
        'contract_date',
        'delivery_date',
        'status',
        'exclude_from_overhead_profit',
        'developer_sales_mode',
        'sales_commission_enabled',
        'sales_percentage',
        'developer_percentage',
        'is_public',
        'is_featured',
        'short_description',
        'featured_image_path',
        'tech_stack',
        'guest_description',
    ];

    protected $casts = [
        'contract_amount' => 'float',
        'contract_date' => 'date',
        'delivery_date' => 'date',
        'exclude_from_overhead_profit' => 'boolean',
        'developer_sales_mode' => 'boolean',
        'sales_commission_enabled' => 'boolean',
        'sales_percentage' => 'float',
        'developer_percentage' => 'float',
        'is_public' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /** Sales Commission Applicable: default true for all projects (including when null/legacy). */
    public function getSalesCommissionEnabledAttribute($value): bool
    {
        if ($value === null) {
            return true;
        }
        return (bool) $value;
    }

    /** Display ID e.g. SLN-000033 for use in UI. */
    public function getFormattedIdAttribute(): string
    {
        return 'SLN-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate next project code (SLN-XXXXXX) by reusing the first gap in the sequence.
     * If project SLN-000037 is deleted, the next created project gets SLN-000037, not SLN-000038.
     * Used when adding a new project; field is auto-filled and readonly.
     */
    public static function generateNextProjectCode(): string
    {
        $used = collect(static::pluck('id')->all());

        foreach (static::whereNotNull('project_code')->pluck('project_code') as $code) {
            if (preg_match('/^SLN-(\d+)$/i', trim($code), $m)) {
                $used->push((int) $m[1]);
            }
        }

        $used = $used->unique()->filter(fn ($n) => $n >= 1)->values();

        $nextNumber = 1;
        while ($used->contains($nextNumber)) {
            $nextNumber++;
        }

        return 'SLN-' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** Additional clients linked to this project (excluding the primary client). */
    public function additionalClients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'project_clients')->withTimestamps();
    }

    /** Developers assigned to this project. */
    public function developers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_developers')->withTimestamps();
    }

    /** Sales persons assigned to this project. */
    public function sales(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_sales')->withTimestamps();
    }

    /** Scope: projects assigned to this developer. */
    public function scopeForDeveloper($query, int $userId)
    {
        return $query->whereHas('developers', fn ($q) => $q->where('users.id', $userId));
    }

    /** Scope: projects assigned to this sales user. */
    public function scopeForSales($query, int $userId)
    {
        return $query->whereHas('sales', fn ($q) => $q->where('users.id', $userId));
    }

    /** Scope: projects visible to this client (primary or additional). */
    public function scopeForClient($query, int $clientId)
    {
        return $query->where(function ($q) use ($clientId) {
            $q->where('client_id', $clientId)
                ->orWhereHas('additionalClients', fn ($q2) => $q2->where('clients.id', $clientId));
        });
    }

    /** Whether this client (by id) has access to the project (primary or additional). */
    public function hasClientAccess(int $clientId): bool
    {
        return $this->client_id === $clientId
            || $this->additionalClients()->where('clients.id', $clientId)->exists();
    }

    /** Whether this user (developer) is assigned to the project. */
    public function hasDeveloperAssigned(int $userId): bool
    {
        return $this->developers()->where('users.id', $userId)->exists();
    }

    /** Whether this user (sales) is assigned to the project. */
    public function hasSalesAssigned(int $userId): bool
    {
        return $this->sales()->where('users.id', $userId)->exists();
    }

    /** Payment status label for Sales: Paid / Unpaid (no amounts). */
    public function getPaymentStatusLabelAttribute(): string
    {
        return $this->due <= 0 ? 'Paid' : ($this->total_paid > 0 ? 'Partial' : 'Unpaid');
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

    public function projectActivities(): HasMany
    {
        return $this->hasMany(ProjectActivity::class)->orderByDesc('created_at');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class)->orderByDesc('created_at');
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
        // Treat null/legacy/advance as "first" taken
        $hasFirst = $this->payments()->whereIn('payment_type', [Payment::TYPE_FIRST, Payment::TYPE_ADVANCE, null])->exists();

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

    /**
     * Total of ALL expenses. Use for revenue distribution (Developer, Sales, Overhead, Profit).
     */
    public function getExpenseTotalAttribute(): float
    {
        return round($this->expenses()->sum('amount'), 2);
    }

    /**
     * Total of PUBLIC expenses only. Use for client-facing totals and payment context.
     */
    public function getPublicExpenseTotalAttribute(): float
    {
        return round($this->expenses()->where('is_public', true)->sum('amount'), 2);
    }

    /** Net base = Contract − ALL Expenses. Base for revenue split. */
    public function getNetBaseAttribute(): float
    {
        return round($this->contract_amount - $this->expense_total, 2);
    }

    /** True when net_base < 0 (show warning in UI). */
    public function getIsNetBaseNegativeAttribute(): bool
    {
        return $this->net_base < 0;
    }

    /** Distribution service for Base-based calculations and override hierarchy. */
    protected function distributionService(): \App\Services\ProjectDistributionService
    {
        return app(\App\Services\ProjectDistributionService::class);
    }

    /** Overhead: 20% of Base when standard mode; 0 when Developer–Sales (75/25) mode. */
    public function getOverheadAttribute(): float
    {
        $breakdown = $this->distributionService()->getBreakdown($this);
        return $breakdown['overhead'];
    }

    /** Sales: from breakdown (Base-based; 0 if sales commission disabled in standard mode). */
    public function getSalesAttribute(): float
    {
        $breakdown = $this->distributionService()->getBreakdown($this);
        return $breakdown['sales'];
    }

    /** Developer: from breakdown (Base-based). */
    public function getDeveloperAttribute(): float
    {
        $breakdown = $this->distributionService()->getBreakdown($this);
        return $breakdown['developer'];
    }

    /** Profit = remainder. Zero when net_base < 0 or when Developer–Sales mode. */
    public function getProfitAttribute(): float
    {
        if ($this->net_base < 0) {
            return 0.0;
        }
        $breakdown = $this->distributionService()->getBreakdown($this);
        return $breakdown['profit'];
    }

    /** Whether this project uses Developer–Sales (75/25) mode (contributes 0 to profit pool). */
    public function getIsDeveloperSalesModeAttribute(): bool
    {
        return $this->distributionService()->isDeveloperSalesMode($this);
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

    /**
     * Overhead amount that counts as "paid" for dashboards and fund balance.
     * Only when the project's overhead payout status is paid (or partial with amount_paid).
     */
    public function getPaidOverheadAttribute(): float
    {
        return $this->amountCountedAsPaid(ProjectPayout::TYPE_OVERHEAD, $this->realized_overhead);
    }

    /** Sales amount that counts as "paid" (only when payout status is paid/partial). */
    public function getPaidSalesAttribute(): float
    {
        return $this->amountCountedAsPaid(ProjectPayout::TYPE_SALES, $this->realized_sales);
    }

    /** Developer amount that counts as "paid" (only when payout status is paid/partial). */
    public function getPaidDeveloperAttribute(): float
    {
        return $this->amountCountedAsPaid(ProjectPayout::TYPE_DEVELOPER, $this->realized_developer);
    }

    /** Profit amount that counts as "paid" for dashboards and profit pool (only when payout status is paid/partial). */
    public function getPaidProfitAttribute(): float
    {
        return $this->amountCountedAsPaid(ProjectPayout::TYPE_PROFIT, $this->realized_profit);
    }

    /**
     * Amount to count as "paid" for a payout type: full realized when status is paid,
     * amount_paid when partial, zero when not_paid/upcoming/due.
     */
    protected function amountCountedAsPaid(string $type, float $realizedAmount): float
    {
        $payout = $this->getPayoutFor($type);
        if (!$payout) {
            return 0.0;
        }
        if ($payout->status === ProjectPayout::STATUS_PAID) {
            return round($realizedAmount, 2);
        }
        if ($payout->status === ProjectPayout::STATUS_PARTIAL && $payout->amount_paid !== null) {
            return round((float) $payout->amount_paid, 2);
        }
        return 0.0;
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
