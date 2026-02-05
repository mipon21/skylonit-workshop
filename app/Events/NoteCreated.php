<?php

namespace App\Events;

use App\Models\ProjectNote;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NoteCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ProjectNote $note,
        public bool $sendEmail
    ) {
    }
}
