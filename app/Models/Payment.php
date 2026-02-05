<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    public const STATUS_UPCOMING = 'upcoming';
    public const STATUS_DUE = 'due';
    public const STATUS_COMPLETED = 'completed';

    /** Payment status for gateway/manual flow: DUE until paid via gateway or marked cash */
    public const PAYMENT_STATUS_DUE = 'DUE';
    public const PAYMENT_STATUS_PAID = 'PAID';

    public const GATEWAY_UDDOKTAPAY = 'uddoktapay';
    public const GATEWAY_MANUAL = 'manual';

    public const PAID_METHOD_GATEWAY = 'gateway';
    public const PAID_METHOD_CASH = 'cash';

    public const TYPE_FIRST = 'first';
    public const TYPE_MIDDLE = 'middle';
    public const TYPE_FINAL = 'final';

    public const PAYMENT_METHODS = [
        'DBBL',
        'BKASH',
        'NAGAD',
        'ROCKET',
        'UPAY',
        'CASH',
    ];

    protected $fillable = [
        'project_id',
        'payment_type',
        'amount',
        'payment_method',
        'note',
        'payment_date',
        'status',
        'gateway',
        'payment_status',
        'gateway_invoice_id',
        'payment_link',
        'paid_at',
        'paid_method',
    ];

    protected $casts = [
        'amount' => 'float',
        'payment_date' => 'date',
        'paid_at' => 'datetime',
    ];

    /**
     * Whether this payment counts toward total_paid (revenue). True when PAID (gateway or cash).
     */
    public function isCountedAsPaid(): bool
    {
        if ($this->payment_status === self::PAYMENT_STATUS_PAID) {
            return true;
        }
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isDue(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_DUE;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function invoice(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
