<?php

namespace App\Events;

use App\Models\Bug;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BugStatusUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Bug $bug,
        public bool $sendEmail,
        public string $oldStatus,
        public string $newStatus
    ) {
    }
}
