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
use Illuminate\Console\Command;

class PushToGoogleSheetCommand extends Command
{
    protected $signature = 'sync:push {--entity=all : projects|payments|expenses|documents|tasks|bugs|notes|all}';

    protected $description = 'Push all ERP data to Google Sheet (ERP → Sheet). Use after sharing the sheet with the service account.';

    public function handle(GoogleSheetsService $sheets): int
    {
        if (! $sheets->isEnabled()) {
            $this->error('Google Sheets sync is disabled or not configured. Check .env and credentials file.');

            return self::FAILURE;
        }

        $credentialsPath = config('google_sheets.credentials_path');
        $credentials = @json_decode(file_get_contents($credentialsPath), true);
        $serviceEmail = $credentials['client_email'] ?? 'your-service-account@....iam.gserviceaccount.com';
        $this->line('');
        $this->info('Share your Google Sheet with this account as <comment>Editor</comment>:');
        $this->line('  <comment>' . $serviceEmail . '</comment>');
        $this->line('');

        $entity = $this->option('entity');

        try {
            if ($entity === 'all' || $entity === 'projects') {
                $count = Project::count();
                $this->info("Pushing {$count} projects...");
                foreach (Project::with('client')->get() as $project) {
                    $ok = $sheets->syncProjectToSheet($project);
                    if (! $ok) {
                        throw new \RuntimeException("Failed to push project #{$project->id} to sheet. Check storage/logs/laravel.log for details.");
                    }
                }
                $this->info("  → Projects: {$count} synced.");
            }

            if ($entity === 'all' || $entity === 'payments') {
                $count = Payment::count();
                $this->info("Pushing {$count} payments...");
                foreach (Payment::all() as $payment) {
                    if (! $sheets->syncPaymentToSheet($payment)) {
                        throw new \RuntimeException("Failed to push payment #{$payment->id}. Check logs.");
                    }
                }
                $this->info("  → Payments: {$count} synced.");
            }

            if ($entity === 'all' || $entity === 'expenses') {
                $count = Expense::count();
                $this->info("Pushing {$count} expenses...");
                foreach (Expense::all() as $expense) {
                    if (! $sheets->syncExpenseToSheet($expense)) {
                        throw new \RuntimeException("Failed to push expense #{$expense->id}. Check logs.");
                    }
                }
                $this->info("  → Expenses: {$count} synced.");
            }

            if ($entity === 'all' || $entity === 'documents') {
                $count = Document::count();
                $this->info("Pushing {$count} documents...");
                foreach (Document::all() as $document) {
                    if (! $sheets->syncDocumentToSheet($document)) {
                        throw new \RuntimeException("Failed to push document #{$document->id}. Check logs.");
                    }
                }
                $this->info("  → Documents: {$count} synced.");
            }

            if ($entity === 'all' || $entity === 'tasks') {
                $count = Task::count();
                $this->info("Pushing {$count} tasks...");
                foreach (Task::all() as $task) {
                    if (! $sheets->syncTaskToSheet($task)) {
                        throw new \RuntimeException("Failed to push task #{$task->id}. Check logs.");
                    }
                }
                $this->info("  → Tasks: {$count} synced.");
            }

            if ($entity === 'all' || $entity === 'bugs') {
                $count = Bug::count();
                $this->info("Pushing {$count} bugs...");
                foreach (Bug::all() as $bug) {
                    if (! $sheets->syncBugToSheet($bug)) {
                        throw new \RuntimeException("Failed to push bug #{$bug->id}. Check logs.");
                    }
                }
                $this->info("  → Bugs: {$count} synced.");
            }

            if ($entity === 'all' || $entity === 'notes') {
                $count = ProjectNote::count();
                $this->info("Pushing {$count} notes...");
                foreach (ProjectNote::all() as $note) {
                    if (! $sheets->syncNoteToSheet($note)) {
                        throw new \RuntimeException("Failed to push note #{$note->id}. Check logs.");
                    }
                }
                $this->info("  → Notes: {$count} synced.");
            }

            $this->newLine();
            $this->info('Push to sheet completed successfully.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error('Push failed: ' . $e->getMessage());

            if (str_contains($e->getMessage(), 'permission') || str_contains($e->getMessage(), '403') || str_contains($e->getMessage(), 'caller does not have permission')) {
                $this->line('');
                $this->line('→ Open your Google Sheet → <comment>Share</comment> → add the service account email above as <comment>Editor</comment>.');
            }

            report($e);

            return self::FAILURE;
        }
    }
}
