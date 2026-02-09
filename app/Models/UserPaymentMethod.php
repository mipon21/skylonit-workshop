<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPaymentMethod extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'label',
        'details',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function typeOptions(): array
    {
        return [
            'bank' => 'Bank',
            'mobile_banking' => 'Mobile Banking',
            'other' => 'Other',
        ];
    }
}
