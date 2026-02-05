<?php

namespace App\Events;

use App\Models\ProjectLink;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LinkCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ProjectLink $link,
        public bool $sendEmail
    ) {
    }
}
