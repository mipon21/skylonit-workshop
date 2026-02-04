<?php

namespace App\Observers;

use App\Jobs\SyncExpenseToSheetJob;
use App\Models\Expense;

class ExpenseObserver
{
    public function saved(Expense $expense): void
    {
        SyncExpenseToSheetJob::dispatch($expense);
    }
}
