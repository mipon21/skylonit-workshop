<?php

namespace App\Console\Commands;

use App\Services\GoogleSheetsSyncService;
use Illuminate\Console\Command;

class GoogleSheetsBidirectionalSyncCommand extends Command
{
    protected $signature = 'sync:google-sheets';

    protected $description = 'Bidirectional sync: ERP ↔ Google Sheets (Projects tab). ERP is source of truth for revenue. Runs every 5 minutes when scheduled.';

    public function handle(GoogleSheetsSyncService $sync): int
    {
        if (! $sync->isEnabled()) {
            $this->warn('Google Sheets sync is disabled or not configured.');

            return self::SUCCESS;
        }

        $this->info('Running bidirectional sync...');
        $results = $sync->runBidirectionalSync();

        if (! empty($results['errors'])) {
            foreach ($results['errors'] as $err) {
                $this->error($err);
            }
        }

        $erp = $results['erp_to_sheet'] ?? [];
        $sheet = $results['sheet_to_erp'] ?? [];
        $this->info(sprintf('ERP → Sheet: %d synced.', $erp['synced'] ?? 0));
        $this->info(sprintf('Sheet → ERP: %d projects updated, %d payments created.', $sheet['updated'] ?? 0, $sheet['payments_created'] ?? 0));

        if (! empty($erp['errors'])) {
            foreach ($erp['errors'] as $e) {
                $this->warn($e);
            }
        }
        if (! empty($sheet['errors'])) {
            foreach ($sheet['errors'] as $e) {
                $this->warn($e);
            }
        }

        $this->info('Sync finished.');

        return self::SUCCESS;
    }
}
