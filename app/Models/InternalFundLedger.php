<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalFundLedger extends Model
{
    public const FUND_OVERHEAD = 'overhead';
    public const FUND_PROFIT = 'profit';
    public const FUND_INVESTMENT = 'investment';

    public const REFERENCE_INTERNAL_EXPENSE = 'internal_expense';
    public const REFERENCE_MANUAL_ADJUSTMENT = 'manual_adjustment';

    public const DIRECTION_DEBIT = 'debit';
    public const DIRECTION_CREDIT = 'credit';

    protected $table = 'internal_fund_ledger';

    public $timestamps = true;
    const UPDATED_AT = null;

    protected $fillable = [
        'fund_type',
        'reference_type',
        'reference_id',
        'investment_id',
        'amount',
        'direction',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class, 'investment_id');
    }

    public function internalExpense(): BelongsTo
    {
        return $this->belongsTo(InternalExpense::class, 'reference_id', 'id');
    }

    public static function fundTypeLabel(string $type): string
    {
        return match ($type) {
            self::FUND_OVERHEAD => 'Overhead',
            self::FUND_PROFIT => 'Profit Pool',
            self::FUND_INVESTMENT => 'Investor Capital',
            default => ucfirst($type),
        };
    }
}
