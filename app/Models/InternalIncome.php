<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InternalIncome extends Model
{
    public const FUND_OVERHEAD = 'overhead';
    public const FUND_PROFIT = 'profit';

    protected $table = 'internal_income';

    protected $fillable = [
        'title',
        'description',
        'amount',
        'income_date',
        'fund_type',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'float',
        'income_date' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ledgerEntry(): HasOne
    {
        return $this->hasOne(InternalFundLedger::class, 'reference_id', 'id')
            ->where('reference_type', InternalFundLedger::REFERENCE_EXTERNAL_INCOME);
    }

    public static function fundTypeLabel(string $fund): string
    {
        return match ($fund) {
            self::FUND_OVERHEAD => 'Overhead',
            self::FUND_PROFIT => 'Profit Pool',
            default => ucfirst($fund),
        };
    }
}
