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

    protected $fillable = [
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

    /** Whether this investor has reached return cap (should be exited). */
    public function hasReachedCap(): bool
    {
        return $this->returned_amount >= $this->return_cap_amount;
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
