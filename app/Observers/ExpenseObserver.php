<?php

namespace App\Observers;

use App\Jobs\SyncExpenseToSheetJob;
use App\Models\Expense;
use App\Models\ProjectActivity;

class ExpenseObserver
{
    public function created(Expense $expense): void
    {
        $amount = number_format($expense->amount, 0);
        $visibility = $expense->is_public ? ProjectActivity::VISIBILITY_CLIENT : ProjectActivity::VISIBILITY_INTERNAL;
        ProjectActivity::log(
            $expense->project_id,
            'expense_created',
            "Expense ৳{$amount} created",
            $visibility
        );
    }

    public function saved(Expense $expense): void
    {
        SyncExpenseToSheetJob::dispatch($expense);
    }
}
