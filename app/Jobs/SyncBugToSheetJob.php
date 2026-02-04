<?php

namespace App\Jobs;

use App\Models\Bug;
use App\Services\GoogleSheetsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncBugToSheetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Bug $bug
    ) {}

    public function handle(GoogleSheetsService $sheets): void
    {
        if (! $sheets->isEnabled()) {
            return;
        }

        $sheets->syncBugToSheet($this->bug);
    }
}
