<?php

namespace App\Services;

use App\Models\Project;

/**
 * Project distribution engine: Base (B) = Contract − Expenses.
 * All percentages applied to Base; never sequentially.
 * Overhead fixed at 20%. Override hierarchy: 1) Developer–Sales (75/25), 2) Custom %, 3) Defaults.
 */
class ProjectDistributionService
{
    public const OVERHEAD_PERCENT = 20;

    public const DEFAULT_SALES_PERCENT = 25;
    public const DEFAULT_DEVELOPER_PERCENT = 40;

    public const DEV_SALES_SALES_PERCENT = 25;
    public const DEV_SALES_DEVELOPER_PERCENT = 75;

    /**
     * Whether this project uses Developer–Sales (75/25) mode (highest precedence).
     * Backward compat: existing projects with exclude_from_overhead_profit=true are treated as dev-sales.
     */
    public function isDeveloperSalesMode(Project $project): bool
    {
        return (bool) ($project->developer_sales_mode ?? $project->exclude_from_overhead_profit ?? false);
    }

    /**
     * Base amount (B) = Contract Amount − Direct Project Expenses. Non-negative for distribution.
     */
    public function getBaseAmount(Project $project): float
    {
        return round(max(0, $project->contract_amount - $project->expense_total), 2);
    }

    /**
     * Full breakdown: base, overhead, sales, developer, profit.
     * Respects override hierarchy and unused-margin-to-profit rule.
     */
    public function getBreakdown(Project $project): array
    {
        $base = $this->getBaseAmount($project);

        if ($this->isDeveloperSalesMode($project)) {
            return [
                'base' => $base,
                'overhead' => 0.0,
                'sales' => round($base * (self::DEV_SALES_SALES_PERCENT / 100), 2),
                'developer' => round($base * (self::DEV_SALES_DEVELOPER_PERCENT / 100), 2),
                'profit' => 0.0,
                'developer_sales_mode' => true,
            ];
        }

        $overhead = round($base * (self::OVERHEAD_PERCENT / 100), 2);
        $salesPercent = $project->sales_percentage ?? config('revenue.sales', 0.25) * 100;
        $developerPercent = $project->developer_percentage ?? config('revenue.developer', 0.40) * 100;
        $salesEnabled = $project->sales_commission_enabled ?? true;

        $sales = $salesEnabled
            ? round($base * ($salesPercent / 100), 2)
            : 0.0;
        $developer = round($base * ($developerPercent / 100), 2);
        $profit = round($base - $overhead - $sales - $developer, 2);

        return [
            'base' => $base,
            'overhead' => $overhead,
            'sales' => $sales,
            'developer' => $developer,
            'profit' => max(0, $profit),
            'developer_sales_mode' => false,
        ];
    }

    /**
     * Validation: when Developer–Sales mode is OFF:
     * sales_percentage >= 0, developer_percentage >= 0,
     * (sales_percentage + developer_percentage + 20) <= 100.
     */
    public function validateDistribution(?bool $developerSalesMode, ?float $salesPercent, ?float $developerPercent): array
    {
        if ($developerSalesMode) {
            return [];
        }
        $errors = [];
        $sales = $salesPercent ?? self::DEFAULT_SALES_PERCENT;
        $dev = $developerPercent ?? self::DEFAULT_DEVELOPER_PERCENT;
        if ($sales < 0) {
            $errors['sales_percentage'] = ['Sales percentage must be at least 0.'];
        }
        if ($dev < 0) {
            $errors['developer_percentage'] = ['Developer percentage must be at least 0.'];
        }
        if ($sales + $dev + self::OVERHEAD_PERCENT > 100) {
            $errors['distribution'] = ['Sales + Developer + Overhead (20%) cannot exceed 100%.'];
        }
        return $errors;
    }
}
