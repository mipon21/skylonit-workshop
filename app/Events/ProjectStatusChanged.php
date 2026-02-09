<?php

namespace App\Events;

use App\Models\Project;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Project $project,
        public ?string $oldStatus,
        public string $newStatus
    ) {
    }
}
