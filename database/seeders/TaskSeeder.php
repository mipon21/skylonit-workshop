<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::all();
        foreach ($projects as $project) {
            if ($project->tasks()->count() > 0) {
                continue;
            }
            $titles = ['Setup repository', 'Design mockups', 'Implement API', 'Write tests', 'Deploy to staging'];
            $statuses = ['todo', 'todo', 'doing', 'done', 'done'];
            $priorities = ['high', 'medium', 'medium', 'low', 'low'];
            foreach (array_slice($titles, 0, 3) as $i => $title) {
                Task::create([
                    'project_id' => $project->id,
                    'title' => $title,
                    'description' => 'Sample task for ' . $project->project_name,
                    'status' => $statuses[$i] ?? 'todo',
                    'priority' => $priorities[$i] ?? 'medium',
                    'due_date' => now()->addDays(rand(5, 20)),
                ]);
            }
        }
    }
}
