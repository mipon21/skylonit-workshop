<?php

namespace App\Events;

use App\Models\ProjectPayout;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PayoutStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ProjectPayout $payout
    ) {
    }
}
