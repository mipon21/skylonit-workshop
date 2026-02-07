<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Illuminate\Support\Carbon;

class GoogleSheetsService
{
    protected ?Sheets $service = null;

    protected ?string $spreadsheetId = null;

    public function __construct()
    {
        $this->spreadsheetId = config('google_sheets.spreadsheet_id');
    }

    public function isEnabled(): bool
    {
        if (! config('google_sheets.enabled') || ! $this->spreadsheetId) {
            return false;
        }
        if (config('google_sheets.client_email') && config('google_sheets.private_key')) {
            return true;
        }

        return file_exists(config('google_sheets.credentials_path'));
    }

    protected function getClient(): GoogleClient
    {
        $client = new GoogleClient();
        $client->setApplicationName(config('app.name'));
        $client->setScopes([Sheets::SPREADSHEETS]);
        $client->setAccessType('offline');

        $email = config('google_sheets.client_email');
        $key = config('google_sheets.private_key');
        if ($email && $key !== null) {
            $key = is_string($key) ? str_replace('\n', "\n", $key) : $key;
            $client->setAuthConfig([
                'client_email' => $email,
                'private_key' => $key,
            ]);
        } else {
            $client->setAuthConfig(config('google_sheets.credentials_path'));
        }

        return $client;
    }

    protected function getService(): Sheets
    {
        if ($this->service === null) {
            $this->service = new Sheets($this->getClient());
        }

        return $this->service;
    }

    /**
     * Get all rows from a sheet (e.g. "Projects!A:Z").
     * First row is treated as header.
     */
    public function getRows(string $sheetTabName): array
    {
        if (! $this->isEnabled()) {
            return [];
        }

        try {
            $range = "'{$sheetTabName}'!A:Z";
            $response = $this->getService()->spreadsheets_values->get(
                $this->spreadsheetId,
                $range
            );

            return $response->getValues() ?? [];
        } catch (\Throwable $e) {
            report($e);

            return [];
        }
    }

    /**
     * Get all rows from Projects tab including ERP columns (A:AL).
     */
    public function getProjectsRows(): array
    {
        if (! $this->isEnabled()) {
            return [];
        }

        $tab = config('google_sheets.tabs.projects');
        try {
            $range = "'{$tab}'!A:AL";
            $response = $this->getService()->spreadsheets_values->get(
                $this->spreadsheetId,
                $range
            );

            return $response->getValues() ?? [];
        } catch (\Throwable $e) {
            report($e);

            return [];
        }
    }

    /**
     * Find row index (1-based) where column AA (erp_project_id) matches.
     */
    public function findProjectRowIndexByErpId(int $erpProjectId): ?int
    {
        $rows = $this->getProjectsRows();
        if (empty($rows)) {
            return null;
        }

        $col = config('google_sheets.projects_sheet_columns.erp_project_id', 26);
        foreach ($rows as $index => $row) {
            $val = $row[$col] ?? null;
            if ((string) $val === (string) $erpProjectId) {
                return $index + 1;
            }
        }

        return null;
    }

    /**
     * Find row index (1-based) where column A (erp_id) matches (legacy tabs).
     */
    public function findRowIndexByErpId(string $sheetTabName, string|int $erpId): ?int
    {
        $rows = $this->getRows($sheetTabName);
        if (empty($rows)) {
            return null;
        }

        foreach ($rows as $index => $row) {
            $first = $row[0] ?? null;
            if ((string) $first === (string) $erpId) {
                return $index + 1; // 1-based row number
            }
        }

        return null;
    }

    /**
     * Update a single row by 1-based row index.
     */
    public function updateRow(string $sheetTabName, int $rowIndex, array $values): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        try {
            $range = "'{$sheetTabName}'!A{$rowIndex}";
            $row = array_values(array_map(fn ($v) => $v === null ? '' : $v, $values));
            $body = new ValueRange(['values' => [$row]]);
            $this->getService()->spreadsheets_values->update(
                $this->spreadsheetId,
                $range,
                $body,
                ['valueInputOption' => 'USER_ENTERED']
            );

            return true;
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }

    /**
     * Format row for Google Sheets API - must be sequential numeric array, not object.
     * API rejects payload when values serialize as object (e.g. {"0":"x"} instead of ["x"]).
     */
    protected function formatRowForSheet(array $values): array
    {
        $row = [];
        foreach (array_values($values) as $v) {
            $row[] = $v === null || $v === '' ? '' : (is_scalar($v) ? $v : (string) $v);
        }

        return $row;
    }

    /**
     * Append one row to the sheet.
     */
    public function appendRow(string $sheetTabName, array $values): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        try {
            $range = "'{$sheetTabName}'!A:Z";
            $row = $this->formatRowForSheet($values);
            $body = new ValueRange(['values' => [$row]]);
            $this->getService()->spreadsheets_values->append(
                $this->spreadsheetId,
                $range,
                $body,
                ['valueInputOption' => 'USER_ENTERED', 'insertDataOption' => 'INSERT_ROWS']
            );

            return true;
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }

    /**
     * Update one row in Projects tab (A:AL). Uses 1-based row index.
     */
    public function updateProjectRow(int $rowIndex, array $values): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        $tab = config('google_sheets.tabs.projects');
        try {
            $range = "'{$tab}'!A{$rowIndex}:AL{$rowIndex}";
            $row = $this->formatRowForSheet($values);
            $body = new ValueRange(['values' => [$row]]);
            $this->getService()->spreadsheets_values->update(
                $this->spreadsheetId,
                $range,
                $body,
                ['valueInputOption' => 'USER_ENTERED']
            );

            return true;
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }

    /**
     * Append one row to Projects tab (A:AL).
     */
    public function appendProjectRow(array $values): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        $tab = config('google_sheets.tabs.projects');
        try {
            $range = "'{$tab}'!A:AL";
            $row = $this->formatRowForSheet($values);
            $body = new ValueRange(['values' => [$row]]);
            $this->getService()->spreadsheets_values->append(
                $this->spreadsheetId,
                $range,
                $body,
                ['valueInputOption' => 'USER_ENTERED', 'insertDataOption' => 'INSERT_ROWS']
            );

            return true;
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }

    /**
     * Sync project to sheet: upsert by erp_project_id (column AA). Full row A:AL per production schema.
     * ERP owns: expense_total, net_base, overhead, sales, developer, profit, settlement flags, updated_at.
     */
    public function syncProjectToSheet(\App\Models\Project $project): bool
    {
        $payments = $project->payments()->orderBy('id')->get();
        $advance = $payments->whereIn('payment_type', ['advance', 'first'])->where('payment_status', \App\Models\Payment::PAYMENT_STATUS_PAID)->sum('amount')
            ?: $payments->whereIn('payment_type', ['advance', 'first'])->sum('amount');
        $middleAmounts = $payments->where('payment_type', 'middle')->pluck('amount')->map(fn ($a) => round((float) $a, 2))->values()->all();
        $finalPay = $payments->where('payment_type', 'final')->sum('amount');
        $tips = $payments->where('payment_type', 'tip')->sum('amount');

        $paymentStatus = $project->total_paid >= $project->contract_amount ? 'Paid' : ($project->total_paid > 0 ? 'Partial' : 'Due');

        $overheadPayout = $project->getPayoutFor(\App\Models\ProjectPayout::TYPE_OVERHEAD);
        $salesPayout = $project->getPayoutFor(\App\Models\ProjectPayout::TYPE_SALES);
        $developerPayout = $project->getPayoutFor(\App\Models\ProjectPayout::TYPE_DEVELOPER);
        $profitPayout = $project->getPayoutFor(\App\Models\ProjectPayout::TYPE_PROFIT);

        $row = [
            $project->id, // A SL
            $project->project_name,
            $project->project_code ?? $project->formatted_id,
            $project->order_id ?? '',
            $project->contract_date?->format('Y-m-d') ?? '',
            $project->delivery_date?->format('Y-m-d') ?? '',
            $project->project_type ?? '',
            $project->payment_method ?? '',
            $project->contract_amount,
            $advance,
            $project->due,
            implode(', ', $middleAmounts),
            $finalPay,
            $tips,
            $project->expense_total,
            $project->due, // P Balance
            '', // Q Company Share (legacy)
            '', // R Sales Share (legacy)
            $paymentStatus,
            $project->status ?? '',
            $project->client?->name ?? '',
            $project->client?->phone ?? '',
            $project->client?->address ?? '',
            $project->client?->email ?? '',
            $project->client?->fb_link ?? '',
            $project->client?->kyc ?? '',
            $project->id, // AA erp_project_id
            $project->updated_at?->toIso8601String() ?? Carbon::now()->toIso8601String(),
            $project->expense_total,
            $project->net_base,
            $project->overhead,
            $project->sales,
            $project->developer,
            $project->profit,
            $overheadPayout?->status ?? '',
            $salesPayout?->status ?? '',
            $developerPayout?->status ?? '',
            $profitPayout?->status ?? '',
        ];

        $existing = $this->findProjectRowIndexByErpId($project->id);
        if ($existing !== null) {
            return $this->updateProjectRow($existing, $row);
        }

        return $this->appendProjectRow($row);
    }

    public function syncPaymentToSheet(\App\Models\Payment $payment): bool
    {
        $tab = config('google_sheets.tabs.payments');
        $row = [
            $payment->id,
            $payment->updated_at?->toIso8601String() ?? Carbon::now()->toIso8601String(),
            $payment->project_id,
            $payment->amount,
            $payment->note ?? '',
            $payment->payment_date?->format('Y-m-d'),
            $payment->status ?? 'completed',
        ];

        $existing = $this->findRowIndexByErpId($tab, $payment->id);
        if ($existing !== null) {
            return $this->updateRow($tab, $existing, $row);
        }

        return $this->appendRow($tab, $row);
    }

    public function syncExpenseToSheet(\App\Models\Expense $expense): bool
    {
        $tab = config('google_sheets.tabs.expenses');
        $row = [
            $expense->id,
            $expense->updated_at?->toIso8601String() ?? Carbon::now()->toIso8601String(),
            $expense->project_id,
            $expense->amount,
            $expense->note ?? '',
        ];

        $existing = $this->findRowIndexByErpId($tab, $expense->id);
        if ($existing !== null) {
            return $this->updateRow($tab, $existing, $row);
        }

        return $this->appendRow($tab, $row);
    }

    public function syncDocumentToSheet(\App\Models\Document $document): bool
    {
        $tab = config('google_sheets.tabs.documents');
        $row = [
            $document->id,
            $document->updated_at?->toIso8601String() ?? Carbon::now()->toIso8601String(),
            $document->project_id,
            $document->title,
            $document->file_path,
        ];

        $existing = $this->findRowIndexByErpId($tab, $document->id);
        if ($existing !== null) {
            return $this->updateRow($tab, $existing, $row);
        }

        return $this->appendRow($tab, $row);
    }

    public function syncTaskToSheet(\App\Models\Task $task): bool
    {
        $tab = config('google_sheets.tabs.tasks');
        $row = [
            $task->id,
            $task->updated_at?->toIso8601String() ?? Carbon::now()->toIso8601String(),
            $task->project_id,
            $task->title,
            $task->description ?? '',
            $task->status,
            $task->priority ?? '',
            $task->due_date?->format('Y-m-d'),
        ];

        $existing = $this->findRowIndexByErpId($tab, $task->id);
        if ($existing !== null) {
            return $this->updateRow($tab, $existing, $row);
        }

        return $this->appendRow($tab, $row);
    }

    public function syncBugToSheet(\App\Models\Bug $bug): bool
    {
        $tab = config('google_sheets.tabs.bugs');
        $row = [
            $bug->id,
            $bug->updated_at?->toIso8601String() ?? Carbon::now()->toIso8601String(),
            $bug->project_id,
            $bug->title,
            $bug->description ?? '',
            $bug->severity,
            $bug->status,
        ];

        $existing = $this->findRowIndexByErpId($tab, $bug->id);
        if ($existing !== null) {
            return $this->updateRow($tab, $existing, $row);
        }

        return $this->appendRow($tab, $row);
    }

    public function syncNoteToSheet(\App\Models\ProjectNote $note): bool
    {
        $tab = config('google_sheets.tabs.notes');
        $row = [
            $note->id,
            $note->updated_at?->toIso8601String() ?? Carbon::now()->toIso8601String(),
            $note->project_id,
            $note->title,
            $note->body ?? '',
            $note->visibility,
            $note->created_by ?? '',
        ];

        $existing = $this->findRowIndexByErpId($tab, $note->id);
        if ($existing !== null) {
            return $this->updateRow($tab, $existing, $row);
        }

        return $this->appendRow($tab, $row);
    }
}
