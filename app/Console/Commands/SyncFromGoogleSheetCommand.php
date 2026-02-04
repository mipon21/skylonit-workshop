<?php

namespace App\Console\Commands;

use App\Models\Bug;
use App\Models\Document;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectNote;
use App\Models\Task;
use App\Services\GoogleSheetsService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncFromGoogleSheetCommand extends Command
{
    protected $signature = 'sync:google';

    protected $description = 'Sync from Google Sheet to ERP (sheet newer wins). Revenue fields are never updated from sheet.';

    public function handle(GoogleSheetsService $sheets): int
    {
        if (! $sheets->isEnabled()) {
            $this->warn('Google Sheets sync is disabled or not configured.');

            return self::SUCCESS;
        }

        $tabs = config('google_sheets.tabs');

        $this->syncProjects($sheets, $tabs['projects']);
        $this->syncPayments($sheets, $tabs['payments']);
        $this->syncExpenses($sheets, $tabs['expenses']);
        $this->syncDocuments($sheets, $tabs['documents']);
        $this->syncTasks($sheets, $tabs['tasks']);
        $this->syncBugs($sheets, $tabs['bugs']);
        $this->syncNotes($sheets, $tabs['notes']);

        $this->info('Sheet → ERP sync finished.');

        return self::SUCCESS;
    }

    protected function parseUpdatedAt(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Projects: never update contract_amount, expense_total, net_base (revenue).
     */
    protected function syncProjects(GoogleSheetsService $sheets, string $tabName): void
    {
        $rows = $sheets->getRows($tabName);
        if (empty($rows)) {
            return;
        }

        $header = array_shift($rows);
        foreach ($rows as $row) {
            $erpId = $row[0] ?? null;
            $sheetUpdated = $this->parseUpdatedAt($row[1] ?? null);
            if (! $erpId || ! $sheetUpdated) {
                continue;
            }

            $project = Project::find($erpId);
            if (! $project || $project->updated_at->gte($sheetUpdated)) {
                continue;
            }

            $project->project_name = $row[2] ?? $project->project_name;
            $project->project_code = $row[3] ?? $project->project_code;
            if (isset($row[4]) && $row[4] !== '') {
                $project->client_id = (int) $row[4];
            }
            $project->contract_date = isset($row[7]) && $row[7] ? $row[7] : $project->contract_date;
            $project->delivery_date = isset($row[8]) && $row[8] ? $row[8] : $project->delivery_date;
            $project->status = $row[9] ?? $project->status;
            $project->saveQuietly();
        }
    }

    protected function syncPayments(GoogleSheetsService $sheets, string $tabName): void
    {
        $rows = $sheets->getRows($tabName);
        if (empty($rows)) {
            return;
        }

        array_shift($rows);
        foreach ($rows as $row) {
            $erpId = $row[0] ?? null;
            $sheetUpdated = $this->parseUpdatedAt($row[1] ?? null);
            if (! $erpId || ! $sheetUpdated) {
                continue;
            }

            $payment = Payment::find($erpId);
            if (! $payment || $payment->updated_at->gte($sheetUpdated)) {
                continue;
            }

            $payment->project_id = (int) ($row[2] ?? $payment->project_id);
            $payment->amount = (float) ($row[3] ?? $payment->amount);
            $payment->note = $row[4] ?? $payment->note;
            if (isset($row[5]) && $row[5] !== '') {
                $payment->payment_date = $row[5];
            }
            if (isset($row[6]) && in_array($row[6], ['upcoming', 'due', 'completed'], true)) {
                $payment->status = $row[6];
            }
            $payment->saveQuietly();
        }
    }

    protected function syncExpenses(GoogleSheetsService $sheets, string $tabName): void
    {
        $rows = $sheets->getRows($tabName);
        if (empty($rows)) {
            return;
        }

        array_shift($rows);
        foreach ($rows as $row) {
            $erpId = $row[0] ?? null;
            $sheetUpdated = $this->parseUpdatedAt($row[1] ?? null);
            if (! $erpId || ! $sheetUpdated) {
                continue;
            }

            $expense = Expense::find($erpId);
            if (! $expense || $expense->updated_at->gte($sheetUpdated)) {
                continue;
            }

            $expense->project_id = (int) ($row[2] ?? $expense->project_id);
            $expense->amount = (float) ($row[3] ?? $expense->amount);
            $expense->note = $row[4] ?? $expense->note;
            $expense->saveQuietly();
        }
    }

    protected function syncDocuments(GoogleSheetsService $sheets, string $tabName): void
    {
        $rows = $sheets->getRows($tabName);
        if (empty($rows)) {
            return;
        }

        array_shift($rows);
        foreach ($rows as $row) {
            $erpId = $row[0] ?? null;
            $sheetUpdated = $this->parseUpdatedAt($row[1] ?? null);
            if (! $erpId || ! $sheetUpdated) {
                continue;
            }

            $doc = Document::find($erpId);
            if (! $doc || $doc->updated_at->gte($sheetUpdated)) {
                continue;
            }

            $doc->project_id = (int) ($row[2] ?? $doc->project_id);
            $doc->title = $row[3] ?? $doc->title;
            $doc->file_path = $row[4] ?? $doc->file_path;
            $doc->saveQuietly();
        }
    }

    protected function syncTasks(GoogleSheetsService $sheets, string $tabName): void
    {
        $rows = $sheets->getRows($tabName);
        if (empty($rows)) {
            return;
        }

        array_shift($rows);
        foreach ($rows as $row) {
            $erpId = $row[0] ?? null;
            $sheetUpdated = $this->parseUpdatedAt($row[1] ?? null);
            if (! $erpId || ! $sheetUpdated) {
                continue;
            }

            $task = Task::find($erpId);
            if (! $task || $task->updated_at->gte($sheetUpdated)) {
                continue;
            }

            $task->project_id = (int) ($row[2] ?? $task->project_id);
            $task->title = $row[3] ?? $task->title;
            $task->description = $row[4] ?? $task->description;
            $task->status = $row[5] ?? $task->status;
            $task->priority = $row[6] ?? $task->priority;
            $task->due_date = isset($row[7]) && $row[7] ? $row[7] : $task->due_date;
            $task->saveQuietly();
        }
    }

    protected function syncBugs(GoogleSheetsService $sheets, string $tabName): void
    {
        $rows = $sheets->getRows($tabName);
        if (empty($rows)) {
            return;
        }

        array_shift($rows);
        foreach ($rows as $row) {
            $erpId = $row[0] ?? null;
            $sheetUpdated = $this->parseUpdatedAt($row[1] ?? null);
            if (! $erpId || ! $sheetUpdated) {
                continue;
            }

            $bug = Bug::find($erpId);
            if (! $bug || $bug->updated_at->gte($sheetUpdated)) {
                continue;
            }

            $bug->project_id = (int) ($row[2] ?? $bug->project_id);
            $bug->title = $row[3] ?? $bug->title;
            $bug->description = $row[4] ?? $bug->description;
            $bug->severity = $row[5] ?? $bug->severity;
            $bug->status = $row[6] ?? $bug->status;
            $bug->saveQuietly();
        }
    }

    protected function syncNotes(GoogleSheetsService $sheets, string $tabName): void
    {
        $rows = $sheets->getRows($tabName);
        if (empty($rows)) {
            return;
        }

        array_shift($rows);
        foreach ($rows as $row) {
            $erpId = $row[0] ?? null;
            $sheetUpdated = $this->parseUpdatedAt($row[1] ?? null);
            if (! $erpId || ! $sheetUpdated) {
                continue;
            }

            $note = ProjectNote::find($erpId);
            if (! $note || $note->updated_at->gte($sheetUpdated)) {
                continue;
            }

            $note->project_id = (int) ($row[2] ?? $note->project_id);
            $note->title = $row[3] ?? $note->title;
            $note->body = $row[4] ?? $note->body;
            $note->visibility = $row[5] ?? $note->visibility;
            $note->created_by = isset($row[6]) && $row[6] !== '' ? (int) $row[6] : $note->created_by;
            $note->saveQuietly();
        }
    }
}
