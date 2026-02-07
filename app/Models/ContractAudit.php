<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractAudit extends Model
{
    public const ACTION_UPLOADED = 'uploaded';
    public const ACTION_VIEWED = 'viewed';
    public const ACTION_SIGNED = 'signed';

    public $timestamps = false;

    protected $fillable = [
        'contract_id',
        'action',
        'user_id',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
