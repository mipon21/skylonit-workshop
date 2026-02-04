<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectPayout extends Model
{
    public const TYPE_OVERHEAD = 'overhead';
    public const TYPE_SALES = 'sales';
    public const TYPE_DEVELOPER = 'developer';
    public const TYPE_PROFIT = 'profit';

    public const STATUS_NOT_PAID = 'not_paid';
    public const STATUS_UPCOMING = 'upcoming';
    public const STATUS_DUE = 'due';
    public const STATUS_PAID = 'paid';
    public const STATUS_PARTIAL = 'partial';

    protected $fillable = [
        'project_id',
        'type',
        'status',
        'amount_paid',
        'paid_at',
        'note',
    ];

    protected $casts = [
        'amount_paid' => 'float',
        'paid_at' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            self::TYPE_OVERHEAD => 'Overhead',
            self::TYPE_SALES => 'Sales',
            self::TYPE_DEVELOPER => 'Developer',
            self::TYPE_PROFIT => 'Profit',
            default => ucfirst($type),
        };
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_NOT_PAID => 'Not Paid',
            self::STATUS_UPCOMING => 'Upcoming',
            self::STATUS_DUE => 'Due',
            self::STATUS_PAID => 'Paid',
            self::STATUS_PARTIAL => 'Partial',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}
