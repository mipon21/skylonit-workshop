<?php

namespace App\Events;

use App\Models\SupportPackage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportPackagePaymentSuccess
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public SupportPackage $supportPackage
    ) {
    }
}
