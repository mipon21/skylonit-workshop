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
        return config('google_sheets.enabled')
            && $this->spreadsheetId
            && file_exists(config('google_sheets.credentials_path'));
    }

    protected function getClient(): GoogleClient
    {
        $client = new GoogleClient();
        $client->setApplicationName(config('app.name'));
        $client->setScopes([Sheets::SPREADSHEETS]);
        $client->setAuthConfig(config('google_sheets.credentials_path'));
        $client->setAccessType('offline');

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
     * Find row index (1-based) where column A (erp_id) matches.
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
     * Append one row to the sheet.
     */
    public function appendRow(string $sheetTabName, array $values): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        try {
            $range = "'{$sheetTabName}'!A:Z";
            $row = array_values(array_map(fn ($v) => $v === null ? '' : $v, $values));
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
     * Sync project to sheet: find by erp_id or append. Never overwrite revenue from sheet.
     */
    public function syncProjectToSheet(\App\Models\Project $project): bool
    {
        $tab = config('google_sheets.tabs.projects');
        $row = [
            $project->id,
            $project->updated_at?->toIso8601String() ?? Carbon::now()->toIso8601String(),
            $project->project_name,
            $project->project_code,
            $project->client_id,
            $project->client?->name ?? '',
            $project->contract_amount,
            $project->contract_date?->format('Y-m-d'),
            $project->delivery_date?->format('Y-m-d'),
            $project->status,
            $project->expense_total,
            $project->net_base,
        ];

        $existing = $this->findRowIndexByErpId($tab, $project->id);
        if ($existing !== null) {
            return $this->updateRow($tab, $existing, $row);
        }

        return $this->appendRow($tab, $row);
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
