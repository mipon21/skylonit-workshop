<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\GoogleSheetsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncDocumentToSheetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Document $document
    ) {}

    public function handle(GoogleSheetsService $sheets): void
    {
        if (! $sheets->isEnabled()) {
            return;
        }

        $sheets->syncDocumentToSheet($this->document);
    }
}
