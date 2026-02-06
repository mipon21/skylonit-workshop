<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectActivitySeeder extends Seeder
{
    /**
     * Seed demo project activities for existing projects.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $projects = Project::limit(5)->get();
        if ($projects->isEmpty()) {
            return;
        }

        $actionTypes = [
            ['action_type' => 'payment_created', 'description' => 'Payment ৳5000 created', 'visibility' => ProjectActivity::VISIBILITY_CLIENT],
            ['action_type' => 'payment_marked_paid', 'description' => 'Payment ৳5000 marked as PAID', 'visibility' => ProjectActivity::VISIBILITY_CLIENT],
            ['action_type' => 'task_created', 'description' => "Task 'Homepage UI' created", 'visibility' => ProjectActivity::VISIBILITY_CLIENT],
            ['action_type' => 'task_status_changed', 'description' => "Task 'Homepage UI' marked as Done", 'visibility' => ProjectActivity::VISIBILITY_CLIENT],
            ['action_type' => 'bug_created', 'description' => "Bug 'Login redirect' created", 'visibility' => ProjectActivity::VISIBILITY_CLIENT],
            ['action_type' => 'bug_status_changed', 'description' => "Bug 'Login redirect' marked as Resolved", 'visibility' => ProjectActivity::VISIBILITY_CLIENT],
            ['action_type' => 'document_uploaded', 'description' => "Document 'Contract.pdf' uploaded", 'visibility' => ProjectActivity::VISIBILITY_CLIENT],
            ['action_type' => 'note_created', 'description' => "Note 'Kickoff meeting' created", 'visibility' => ProjectActivity::VISIBILITY_CLIENT],
            ['action_type' => 'link_created', 'description' => "Link 'Staging URL' created", 'visibility' => ProjectActivity::VISIBILITY_CLIENT],
            ['action_type' => 'expense_created', 'description' => 'Expense ৳1200 created', 'visibility' => ProjectActivity::VISIBILITY_CLIENT],
            ['action_type' => 'project_status_changed', 'description' => 'Project status changed to Running', 'visibility' => ProjectActivity::VISIBILITY_INTERNAL],
            ['action_type' => 'invoice_generated', 'description' => 'Invoice INV-2026-0001 generated', 'visibility' => ProjectActivity::VISIBILITY_CLIENT],
        ];

        $now = now();
        $created = 0;
        foreach ($projects as $index => $project) {
            foreach (array_slice($actionTypes, 0, 4 + ($index % 5)) as $i => $item) {
                ProjectActivity::create([
                    'project_id' => $project->id,
                    'user_id' => $admin?->id,
                    'action_type' => $item['action_type'],
                    'description' => $item['description'],
                    'visibility' => $item['visibility'],
                    'created_at' => $now->copy()->subMinutes($created * 15),
                ]);
                $created++;
            }
        }
    }
}
