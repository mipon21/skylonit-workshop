<?php

namespace App\Console\Commands;

use App\Services\ProfitPoolService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RunProfitDistributionCommand extends Command
{
    protected $signature = 'profit:distribute
                            {--month= : Month (1-12)}
                            {--year= : Year (e.g. 2026)}
                            {--last-month : Use last month (default)}';

    protected $description = 'Run monthly profit distribution: aggregate profit pool, pay active investors (capped), record distributions, exit investors at cap.';

    public function handle(ProfitPoolService $service): int
    {
        if ($this->option('last-month') || (!$this->option('month') && !$this->option('year'))) {
            $date = Carbon::now()->subMonth();
            $year = (int) $date->format('Y');
            $month = (int) $date->format('n');
        } else {
            $year = (int) ($this->option('year') ?: Carbon::now()->format('Y'));
            $month = (int) ($this->option('month') ?: Carbon::now()->format('n'));
        }

        if ($month < 1 || $month > 12) {
            $this->error('Invalid month.');
            return self::FAILURE;
        }

        $this->info("Running profit distribution for {$year}-" . str_pad((string) $month, 2, '0', STR_PAD_LEFT));

        $results = $service->runDistributionForMonth($year, $month);

        $this->info('Profit pool: ' . number_format($results['profit_pool'], 2));
        $this->info('Total investor share: ' . number_format($results['total_investor_share'], 2));
        $this->info('Founder share: ' . number_format($results['founder_share'], 2));

        foreach ($results['distributions'] ?? [] as $d) {
            $this->line("  - {$d['investor_name']}: " . number_format($d['payout'], 2) . ($d['exited'] ? ' (exited)' : ''));
        }

        $this->info('Done.');
        return self::SUCCESS;
    }
}
