<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\Project;
use Illuminate\Database\Seeder;

class PaymentExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::all();

        foreach ($projects as $project) {
            if ($project->payments()->count() === 0) {
                Payment::create(['project_id' => $project->id, 'amount' => round($project->contract_amount * 0.3, 2), 'note' => 'advance']);
            }
            if ($project->expenses()->count() === 0 && $project->status === 'Running') {
                Expense::create(['project_id' => $project->id, 'amount' => 5000, 'note' => 'Hosting & domain']);
            }
        }
    }
}
