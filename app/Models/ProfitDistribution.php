<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfitDistribution extends Model
{
    protected $table = 'profit_distributions';

    protected $fillable = [
        'investor_id',
        'period',
        'profit_pool_amount',
        'investor_share_amount',
        'founder_share_amount',
    ];

    protected $casts = [
        'profit_pool_amount' => 'float',
        'investor_share_amount' => 'float',
        'founder_share_amount' => 'float',
    ];

    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class, 'investor_id');
    }
}
