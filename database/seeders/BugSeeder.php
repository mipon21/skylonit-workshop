<?php

namespace Database\Seeders;

use App\Models\Bug;
use App\Models\Project;
use Illuminate\Database\Seeder;

class BugSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::all();
        foreach ($projects as $project) {
            if ($project->bugs()->count() > 0) {
                continue;
            }
            Bug::create([
                'project_id' => $project->id,
                'title' => 'Login redirects to wrong page',
                'description' => 'After login user is sent to 404.',
                'severity' => 'major',
                'status' => 'open',
            ]);
            Bug::create([
                'project_id' => $project->id,
                'title' => 'Typo on contact form',
                'description' => 'Label says "Emial" instead of "Email".',
                'severity' => 'minor',
                'status' => 'resolved',
            ]);
        }
    }
}
