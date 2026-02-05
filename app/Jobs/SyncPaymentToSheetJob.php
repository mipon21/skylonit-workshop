<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Services\GoogleSheetsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncPaymentToSheetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Payment $payment
    ) {}

    public function handle(GoogleSheetsService $sheets): void
    {
        if (! $sheets->isEnabled()) {
            return;
        }

        try {
            $sheets->syncPaymentToSheet($this->payment);
        } catch (\Throwable $e) {
            report($e);
            // Don't rethrow – payment creation should succeed even when Google Sheets sync fails
        }
    }
}
