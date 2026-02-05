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
    ];

    protected $casts = [
        'amount' => 'float',
        'payment_date' => 'date',
    ];

    public function isCountedAsPaid(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
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
