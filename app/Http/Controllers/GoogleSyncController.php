<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SyncLog;
use App\Services\GoogleSheetsSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GoogleSyncController extends Controller
{
    public function index(GoogleSheetsSyncService $sync): View
    {
        $sheetId = config('google_sheets.spreadsheet_id');
        $lastSync = Setting::get('google_sheets_last_sync_at');
        $enabled = $sync->isEnabled();

        $logs = SyncLog::query()
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('google-sync.index', [
            'sheetId' => $sheetId,
            'lastSync' => $lastSync,
            'enabled' => $enabled,
            'logs' => $logs,
        ]);
    }

    public function syncNow(Request $request, GoogleSheetsSyncService $sync): RedirectResponse
    {
        if (! $sync->isEnabled()) {
            return redirect()->route('google-sync.index')->with('error', 'Google Sheets sync is disabled or not configured.');
        }

        $results = $sync->runBidirectionalSync();

        $erp = $results['erp_to_sheet'] ?? [];
        $sheet = $results['sheet_to_erp'] ?? [];
        $synced = $erp['synced'] ?? 0;
        $updated = $sheet['updated'] ?? 0;
        $paymentsCreated = $sheet['payments_created'] ?? 0;

        $msg = sprintf('Sync completed. ERP → Sheet: %d projects. Sheet → ERP: %d projects updated, %d payments created.', $synced, $updated, $paymentsCreated);

        if (! empty($results['errors'])) {
            return redirect()->route('google-sync.index')->with('error', $msg . ' Errors: ' . implode(' ', $results['errors']));
        }

        return redirect()->route('google-sync.index')->with('success', $msg);
    }
}
