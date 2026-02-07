<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientDevice extends Model
{
    public const PLATFORM_WEB = 'web';
    public const PLATFORM_ANDROID = 'android';
    public const PLATFORM_IOS = 'ios';

    protected $fillable = [
        'client_id',
        'fcm_token',
        'platform',
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function touchLastSeen(): void
    {
        $this->update(['last_seen_at' => now()]);
    }
}
