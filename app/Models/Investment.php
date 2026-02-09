<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Investment extends Model
{
    public const RISK_LOW = 'low';
    public const RISK_MEDIUM = 'medium';
    public const RISK_HIGH = 'high';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXITED = 'exited';

    public const CATEGORY_INVESTOR = 'investor';
    public const CATEGORY_SHAREHOLDER = 'shareholder';

    protected $fillable = [
        'category',
        'share_percent',
        'investor_name',
        'amount',
        'invested_at',
        'risk_level',
        'profit_share_percent',
        'return_cap_multiplier',
        'return_cap_amount',
        'returned_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'float',
        'invested_at' => 'date',
        'share_percent' => 'float',
        'profit_share_percent' => 'float',
        'return_cap_multiplier' => 'float',
        'return_cap_amount' => 'float',
        'returned_amount' => 'float',
    ];

    public function profitDistributions(): HasMany
    {
        return $this->hasMany(ProfitDistribution::class, 'investor_id');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isExited(): bool
    {
        return $this->status === self::STATUS_EXITED;
    }

    /** Whether this investor has reached return cap (should be exited). Shareholders never reach cap. */
    public function hasReachedCap(): bool
    {
        if ($this->category === self::CATEGORY_SHAREHOLDER) {
            return false;
        }
        return $this->returned_amount >= $this->return_cap_amount;
    }

    public function isInvestor(): bool
    {
        return $this->category === self::CATEGORY_INVESTOR;
    }

    public function isShareholder(): bool
    {
        return $this->category === self::CATEGORY_SHAREHOLDER;
    }

    public static function categoryLabel(string $category): string
    {
        return match ($category) {
            self::CATEGORY_INVESTOR => 'Investor',
            self::CATEGORY_SHAREHOLDER => 'Shareholder',
            default => ucfirst($category),
        };
    }

    /** Get all active investors eligible for profit distribution. */
    public static function getActiveInvestors(): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('category', self::CATEGORY_INVESTOR)
            ->where('status', self::STATUS_ACTIVE)
            ->get();
    }

    /** Get all shareholders (always eligible; never exit). */
    public static function getShareholders(): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('category', self::CATEGORY_SHAREHOLDER)->get();
    }

    /** Sum of share_percent across all shareholders. Must equal 100 for valid distribution. */
    public static function getShareholderTotalPercent(): float
    {
        return round((float) self::where('category', self::CATEGORY_SHAREHOLDER)->sum('share_percent'), 2);
    }

    /** Validate shareholder total equals 100 (current DB state). Returns error message or null if valid. */
    public static function validateShareholderTotal(): ?string
    {
        $total = self::getShareholderTotalPercent();
        if (abs($total - 100) > 0.01) {
            return "Shareholder total must equal 100% (current: {$total}%).";
        }
        return null;
    }

    /** Validate shareholder total for save (create or update). excludeId = id to exclude when editing. */
    public static function validateShareholderTotalForSave(?int $excludeId, float $newSharePercent): ?string
    {
        if ($newSharePercent <= 0) {
            return "Share percent must be greater than 0.";
        }
        $query = self::where('category', self::CATEGORY_SHAREHOLDER);
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }
        $othersSum = round((float) $query->sum('share_percent'), 2);
        $total = $othersSum + $newSharePercent;
        if ($total > 100.01) {
            return "Shareholder total cannot exceed 100% (would be {$total}%).";
        }
        return null;
    }

    /** Remaining amount until cap (0 if already at/over cap). */
    public function getRemainingCapAttribute(): float
    {
        $remaining = $this->return_cap_amount - $this->returned_amount;
        return round(max(0, $remaining), 2);
    }

    public static function riskLabel(string $risk): string
    {
        return match ($risk) {
            self::RISK_LOW => 'Low',
            self::RISK_MEDIUM => 'Medium',
            self::RISK_HIGH => 'High',
            default => ucfirst($risk),
        };
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_EXITED => 'Exited',
            default => ucfirst($status),
        };
    }
}
