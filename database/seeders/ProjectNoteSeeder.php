<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectNote;
use Illuminate\Database\Seeder;

class ProjectNoteSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::all();
        $notes = [
            ['title' => 'Kickoff meeting', 'body' => "Discussed scope and timeline with client.\nNext: send design mockups by Friday.", 'visibility' => 'internal'],
            ['title' => 'API requirements', 'body' => '- REST endpoints for CRUD\n- Auth via Bearer token\n- Rate limit: 100/min', 'visibility' => 'internal'],
            ['title' => 'Client feedback', 'body' => 'Client requested dark theme option. Logged for v2.', 'visibility' => 'client'],
        ];
        foreach ($projects as $project) {
            if ($project->projectNotes()->count() > 0) {
                continue;
            }
            foreach (array_slice($notes, 0, 2) as $i => $data) {
                ProjectNote::create([
                    'project_id' => $project->id,
                    'title' => $data['title'],
                    'body' => $data['body'],
                    'visibility' => $data['visibility'],
                    'created_by' => \App\Models\User::first()?->id,
                ]);
            }
        }
    }
}
