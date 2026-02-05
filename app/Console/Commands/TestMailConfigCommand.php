<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMailConfigCommand extends Command
{
    protected $signature = 'mail:test
                            {--log : Force sending to log only (same as MAIL_MAILER=log)}
                            {--no-send : Only verify config and connection, do not send}';

    protected $description = 'Verify mail config. Use MAIL_MAILER=log (or --log) to test without sending to a real inbox.';

    public function handle(): int
    {
        $this->info('Mail configuration check');
        $this->newLine();

        $mailer = config('mail.default');
        $driver = $this->option('log') ? 'log' : $mailer;

        $this->table(
            ['Key', 'Value'],
            [
                ['MAIL_MAILER', $mailer],
                ['MAIL_HOST', config('mail.mailers.smtp.host') ?: '(n/a)'],
                ['MAIL_PORT', config('mail.mailers.smtp.port') ?: '(n/a)'],
                ['MAIL_FROM_ADDRESS', config('mail.from.address')],
                ['MAIL_FROM_NAME', config('mail.from.name')],
            ]
        );

        if ($driver === 'smtp' && $this->option('no-send')) {
            return $this->checkSmtpConnection();
        }

        if ($driver === 'smtp' && ! $this->checkSmtpConnection()) {
            return 1;
        }

        if ($this->option('no-send')) {
            $this->info('Config looks OK. Run without --no-send to send a test message.');
            return 0;
        }

        $originalMailer = config('mail.default');
        if ($this->option('log')) {
            config(['mail.default' => 'log']);
        }

        try {
            Mail::raw(
                'This is a test email from ' . config('app.name') . '. If you receive this, your mail config is working.' . "\n\n" .
                'Sent at: ' . now()->toDateTimeString(),
                function ($message) {
                    $message->to(config('mail.from.address'))
                        ->subject('[' . config('app.name') . '] Mail test');
                }
            );
        } catch (\Throwable $e) {
            $this->error('Sending failed: ' . $e->getMessage());
            if ($this->option('log')) {
                config(['mail.default' => $originalMailer]);
            }
            return 1;
        }

        if ($this->option('log')) {
            config(['mail.default' => $originalMailer]);
        }

        if ($driver === 'log' || $this->option('log')) {
            $this->newLine();
            $this->info('✓ Test message was written to the log (no email sent to any inbox).');
            $this->comment('  Check: storage/logs/laravel.log');
            $this->newLine();
            $this->info('Your mail config is valid. To send real emails, set MAIL_MAILER=smtp and configure SMTP in .env.');
        } else {
            $this->newLine();
            $this->info('✓ Test email sent to: ' . config('mail.from.address'));
        }

        return 0;
    }

    private function checkSmtpConnection(): bool
    {
        $host = config('mail.mailers.smtp.host');
        $port = (int) config('mail.mailers.smtp.port', 587);

        if (! $host) {
            $this->warn('MAIL_HOST is not set. Skipping connection check.');
            return true;
        }

        $this->info("Checking SMTP connection to {$host}:{$port}...");

        $errno = 0;
        $errstr = '';
        $timeout = 5;
        $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);

        if ($fp) {
            fclose($fp);
            $this->info('✓ Connection to ' . $host . ':' . $port . ' succeeded.');
            return true;
        }

        $this->error('✗ Could not connect to ' . $host . ':' . $port . ' (' . $errstr . ').');
        $this->comment('  Check MAIL_HOST, MAIL_PORT, firewall, and that the SMTP server is running.');
        return false;
    }
}
