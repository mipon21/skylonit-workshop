<?php

namespace App\Console\Commands;

use App\Models\EmailTemplate;
use App\Services\TemplateMailService;
use Illuminate\Console\Command;

class EmailDiagnosticsCommand extends Command
{
    protected $signature = 'email:diagnose
                            {--send-test : Send a test email using client_project_created template to MAIL_FROM_ADDRESS}';

    protected $description = 'Check why email might not be working: templates, mail config, queue.';

    public function handle(TemplateMailService $templateMail): int
    {
        $this->info('Email diagnostics');
        $this->newLine();

        $issues = [];

        // 1. Email templates
        $templates = EmailTemplate::all();
        $active = $templates->where('is_active', true)->count();
        if ($templates->isEmpty()) {
            $this->warn('No email templates in database.');
            $this->line('  → Run: php artisan db:seed --class=EmailTemplateSeeder');
            $issues[] = 'Missing templates';
        } else {
            $this->info('Templates: ' . $templates->count() . ' total, ' . $active . ' active.');
            if ($active === 0) {
                $this->warn('  No template is active. Enable at least one in Settings → Email templates.');
                $issues[] = 'No active template';
            }
        }

        // 2. Mail config
        $this->newLine();
        $mailer = config('mail.default');
        $from = config('mail.from.address');
        $this->line("Mailer: <comment>{$mailer}</comment>");
        $this->line("From: <comment>{$from}</comment>");
        if ($mailer === 'smtp') {
            $host = config('mail.mailers.smtp.host');
            if (empty($host)) {
                $this->warn('MAIL_HOST is empty. Set SMTP settings in .env.');
                $issues[] = 'SMTP not configured';
            }
        }
        if ($mailer === 'log') {
            $this->comment('  Emails are written to storage/logs/laravel.log (no real sending).');
        }

        // 3. Queue
        $this->newLine();
        $queueDriver = config('queue.default');
        $this->line("Queue driver: <comment>{$queueDriver}</comment>");
        if ($queueDriver === 'database') {
            $pending = \Illuminate\Support\Facades\DB::table('jobs')->count();
            $this->line("  Pending jobs: <comment>{$pending}</comment>");
            if ($pending > 0) {
                $this->warn('  Run a worker to process queued emails: php artisan queue:work');
                $issues[] = 'Queue has jobs but no worker running';
            }
        }
        if ($queueDriver === 'sync') {
            $this->comment('  Jobs run immediately (no worker needed).');
        }

        // 4. Optional: send test
        if ($this->option('send-test')) {
            $this->newLine();
            $rendered = $templateMail->renderTemplate('client_project_created', [
                'client_name' => 'Test User',
                'project_name' => 'Test Project',
                'project_code' => 'SLN-000001',
                'login_url' => url('/login'),
            ]);
            if (! $rendered) {
                $this->error('Cannot send test: template client_project_created missing or inactive.');
            } else {
                try {
                    \App\Jobs\SendTemplateMailJob::dispatch(
                        'client_project_created',
                        $from,
                        [
                            'client_name' => 'Test User',
                            'project_name' => 'Test Project',
                            'project_code' => 'SLN-000001',
                            'login_url' => url('/login'),
                        ]
                    );
                    $this->info('Test email queued/dispatched to: ' . $from);
                    if ($queueDriver === 'database') {
                        $this->comment('  Run php artisan queue:work to process it.');
                    }
                } catch (\Throwable $e) {
                    $this->error('Dispatch failed: ' . $e->getMessage());
                    $issues[] = 'Test dispatch failed';
                }
            }
        }

        $this->newLine();
        if (! empty($issues)) {
            $this->warn('Issues found: ' . implode(', ', $issues));
            $this->line('Run php artisan mail:test to verify SMTP connection.');
            return self::FAILURE;
        }

        $this->info('No obvious issues. If emails still don’t arrive, run: php artisan mail:test');
        return self::SUCCESS;
    }
}
