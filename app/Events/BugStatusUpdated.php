<?php

namespace App\Events;

use App\Models\Bug;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BugStatusUpdated
{
    use Dispatchable, SerializesModels;

    /** User ID who updated the status (for client notification: only when developer). */
    public function __construct(
        public Bug $bug,
        public bool $sendEmail,
        public string $oldStatus,
        public string $newStatus,
        public ?int $updatedByUserId = null
    ) {
    }
}
