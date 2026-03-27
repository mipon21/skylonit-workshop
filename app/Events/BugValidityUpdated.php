<?php

namespace App\Events;

use App\Models\Bug;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BugValidityUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Bug $bug,
        public bool $oldIsValid,
        public bool $newIsValid,
        public ?int $updatedByUserId = null
    ) {
    }
}
