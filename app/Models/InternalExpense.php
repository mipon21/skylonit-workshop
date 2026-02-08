<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InternalExpense extends Model
{
    public const PRIMARY_FUND_OVERHEAD = 'overhead';

    public const FALLBACK_PROFIT = 'profit';
    public const FALLBACK_INVESTMENT = 'investment';

    public const FUNDED_OVERHEAD = 'overhead';
    public const FUNDED_PROFIT = 'profit';
    public const FUNDED_INVESTMENT = 'investment';

    protected $table = 'internal_expenses';

    protected $fillable = [
        'title',
        'description',
        'amount',
        'expense_date',
        'primary_fund',
        'fallback_fund',
        'funded_from',
        'investment_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'float',
        'expense_date' => 'date',
    ];

    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class, 'investment_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ledgerEntry(): HasOne
    {
        return $this->hasOne(InternalFundLedger::class, 'reference_id', 'id')
            ->where('reference_type', InternalFundLedger::REFERENCE_INTERNAL_EXPENSE);
    }

    public static function fundedFromLabel(string $fund): string
    {
        return match ($fund) {
            self::FUNDED_OVERHEAD => 'Overhead',
            self::FUNDED_PROFIT => 'Profit Pool',
            self::FUNDED_INVESTMENT => 'Investor Capital',
            default => ucfirst($fund),
        };
    }
}
