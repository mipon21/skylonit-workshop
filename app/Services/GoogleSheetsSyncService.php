<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectPayout;
use App\Models\SyncLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GoogleSheetsSyncService
{
    public function __construct(
        protected GoogleSheetsService $sheets
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->sheets->isEnabled();
    }

    /**
     * Full bidirectional sync: ERP → Sheet, then Sheet → ERP (with conflict resolution).
     */
    public function runBidirectionalSync(): array
    {
        $results = ['erp_to_sheet' => [], 'sheet_to_erp' => [], 'errors' => []];

        if (! $this->sheets->isEnabled()) {
            $results['errors'][] = 'Google Sheets sync is disabled or not configured.';

            return $results;
        }

        DB::beginTransaction();
        try {
            $results['erp_to_sheet'] = $this->pushErpToSheet();
            $results['sheet_to_erp'] = $this->pullSheetToErp();
            DB::commit();
            \App\Models\Setting::set('google_sheets_last_sync_at', now()->toIso8601String());
        } catch (\Throwable $e) {
            DB::rollBack();
            $results['errors'][] = $e->getMessage();
            $this->log('sheet_to_erp', 'project', null, SyncLog::STATUS_ERROR, $e->getMessage());
            report($e);
        }

        return $results;
    }

    /**
     * ERP → Sheet: upsert every project by erp_project_id.
     */
    public function pushErpToSheet(): array
    {
        $synced = 0;
        $errors = [];

        foreach (Project::with(['client', 'payments', 'projectPayouts'])->get() as $project) {
            try {
                if ($this->sheets->syncProjectToSheet($project)) {
                    $synced++;
                    $this->log('erp_to_sheet', 'project', $project->id, SyncLog::STATUS_SUCCESS, 'Synced');
                }
            } catch (\Throwable $e) {
                $errors[] = "Project {$project->id}: " . $e->getMessage();
                $this->log('erp_to_sheet', 'project', $project->id, SyncLog::STATUS_ERROR, $e->getMessage());
                report($e);
            }
        }

        return ['synced' => $synced, 'errors' => $errors];
    }

    /**
     * Sheet → ERP: read Projects rows, conflict by updated_at, update allowed fields, import payments & expense.
     */
    public function pullSheetToErp(): array
    {
        $rows = $this->sheets->getProjectsRows();
        if (empty($rows)) {
            return ['updated' => 0, 'payments_created' => 0, 'errors' => []];
        }

        $cols = config('google_sheets.projects_sheet_columns');
        $header = array_shift($rows);
        $updated = 0;
        $paymentsCreated = 0;
        $errors = [];

        foreach ($rows as $row) {
            $erpProjectId = isset($row[$cols['erp_project_id']]) ? (int) $row[$cols['erp_project_id']] : null;
            if (! $erpProjectId) {
                continue;
            }

            $project = Project::find($erpProjectId);
            if (! $project) {
                continue;
            }

            $sheetUpdatedAt = $this->parseUpdatedAt($row[$cols['updated_at']] ?? null);
            if (! $sheetUpdatedAt) {
                continue;
            }

            if ($project->updated_at->gt($sheetUpdatedAt)) {
                continue;
            }
            if ($project->updated_at->gte($sheetUpdatedAt)) {
                continue;
            }

            try {
                DB::transaction(function () use ($row, $cols, $project, &$updated, &$paymentsCreated) {
                    $this->applyAllowedFieldsFromSheet($project, $row, $cols);
                    $this->importExpenseFromSheet($project, $row, $cols);
                    $created = $this->importPaymentsFromSheet($project, $row, $cols);
                    $project->touch();
                    $updated++;
                    $paymentsCreated += $created;
                });
                $this->log('sheet_to_erp', 'project', $project->id, SyncLog::STATUS_SUCCESS, 'Updated from sheet');
            } catch (\Throwable $e) {
                $errors[] = "Project {$project->id}: " . $e->getMessage();
                $this->log('sheet_to_erp', 'project', $project->id, SyncLog::STATUS_ERROR, $e->getMessage());
                report($e);
            }
        }

        return ['updated' => $updated, 'payments_created' => $paymentsCreated, 'errors' => $errors];
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
     * Update only sheet-allowed fields. Forbidden: expense_total, net_base, overhead, sales, developer, profit.
     */
    protected function applyAllowedFieldsFromSheet(Project $project, array $row, array $cols): void
    {
        $project->project_name = $this->get($row, $cols['project_name'], $project->project_name);
        $project->order_id = $this->get($row, $cols['order_id'], $project->order_id);
        $project->delivery_date = $this->parseDate($this->get($row, $cols['delivery_date'], null)) ?? $project->delivery_date;
        $project->project_type = $this->get($row, $cols['project_type'], $project->project_type);
        $project->payment_method = $this->get($row, $cols['payment_method'], $project->payment_method);
        $project->status = $this->get($row, $cols['project_status'], $project->status);

        $client = $project->client;
        if ($client) {
            $client->name = $this->get($row, $cols['client_name'], $client->name);
            $client->phone = $this->get($row, $cols['client_phone'], $client->phone);
            $client->address = $this->get($row, $cols['client_address'], $client->address);
            $client->email = $this->get($row, $cols['client_email'], $client->email);
            $client->fb_link = $this->get($row, $cols['facebook_link'], $client->fb_link);
            $client->kyc = $this->get($row, $cols['kyc'], $client->kyc);
            $client->saveQuietly();
        }

        $project->saveQuietly();
    }

    /**
     * If Expense (column O) changed: create or update one Expense for this project; trigger recalculation.
     */
    protected function importExpenseFromSheet(Project $project, array $row, array $cols): void
    {
        $amount = $this->parseAmount($this->get($row, $cols['expense'], null));
        if ($amount === null) {
            return;
        }

        $expense = $project->expenses()->first();
        if ($expense) {
            $expense->amount = $amount;
            $expense->note = $expense->note ?: 'From Google Sheet';
            $expense->saveQuietly();
        } else {
            $project->expenses()->create([
                'amount' => $amount,
                'note' => 'From Google Sheet',
                'is_public' => true,
            ]);
        }
    }

    /**
     * Import payments: Advance (one), Middle (comma-separated, unlimited), Final (one), Tips (one).
     * gateway=manual, status=PAID. Dedupe by payment_hash = hash(project_id + amount + type + index).
     */
    protected function importPaymentsFromSheet(Project $project, array $row, array $cols): int
    {
        $created = 0;

        $advance = $this->parseAmount($this->get($row, $cols['advance'], null));
        if ($advance !== null && $advance > 0) {
            if ($this->createPaymentIfNotExists($project, $advance, Payment::TYPE_ADVANCE, 0)) {
                $created++;
            }
        }

        $middleStr = $this->get($row, $cols['middle_payments'], '');
        $middleAmounts = array_filter(array_map(function ($v) {
            return $this->parseAmount(trim($v));
        }, preg_split('/\s*,\s*/', (string) $middleStr)));
        foreach (array_values($middleAmounts) as $index => $amount) {
            if ($amount !== null && $amount > 0 && $this->createPaymentIfNotExists($project, $amount, Payment::TYPE_MIDDLE, $index)) {
                $created++;
            }
        }

        $finalPay = $this->parseAmount($this->get($row, $cols['final_pay'], null));
        if ($finalPay !== null && $finalPay > 0) {
            if ($this->createPaymentIfNotExists($project, $finalPay, Payment::TYPE_FINAL, 0)) {
                $created++;
            }
        }

        $tips = $this->parseAmount($this->get($row, $cols['tips'], null));
        if ($tips !== null && $tips > 0) {
            if ($this->createPaymentIfNotExists($project, $tips, Payment::TYPE_TIP, 0)) {
                $created++;
            }
        }

        return $created;
    }

    protected function createPaymentIfNotExists(Project $project, float $amount, string $type, int $index): bool
    {
        $hash = $this->paymentHash($project->id, $amount, $type, $index);
        if (Payment::where('payment_hash', $hash)->exists()) {
            return false;
        }

        $project->payments()->create([
            'payment_type' => $type,
            'amount' => round($amount, 2),
            'gateway' => Payment::GATEWAY_MANUAL,
            'payment_status' => Payment::PAYMENT_STATUS_PAID,
            'payment_hash' => $hash,
            'payment_date' => now(),
            'paid_at' => now(),
            'paid_method' => Payment::PAID_METHOD_CASH,
        ]);

        return true;
    }

    protected function paymentHash(int $projectId, float $amount, string $type, int $index): string
    {
        return hash('sha256', $projectId . '|' . round($amount, 2) . '|' . $type . '|' . $index);
    }

    protected function get(array $row, int $index, $default = '')
    {
        return $row[$index] ?? $default;
    }

    protected function parseAmount(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        $v = is_numeric($value) ? (float) $value : (float) preg_replace('/[^0-9.-]/', '', (string) $value);

        return $v;
    }

    protected function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    protected function log(string $direction, string $entity, ?int $erpProjectId, string $status, ?string $message): void
    {
        SyncLog::create([
            'direction' => $direction,
            'entity' => $entity,
            'erp_project_id' => $erpProjectId,
            'status' => $status,
            'message' => $message,
        ]);
    }
}
