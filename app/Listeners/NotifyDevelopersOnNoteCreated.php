<?php

namespace App\Listeners;

use App\Events\NoteCreated;
use App\Jobs\SendTemplateMailJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyDevelopersOnNoteCreated implements ShouldQueue
{
    /** Notify project developers when a note visible to them is created (visibility client or internal_developer). */
    public function handle(NoteCreated $event): void
    {
        $note = $event->note;
        $visibility = $note->visibility ?? '';
        if (! in_array($visibility, ['client', 'internal_developer'], true)) {
            return;
        }

        $note->load(['project.developers']);
        $project = $note->project;
        foreach ($project->developers as $user) {
            if (! $user->email) {
                continue;
            }
            SendTemplateMailJob::dispatch(
                'developer_note_added',
                $user->email,
                [
                    'name' => $user->name,
                    'project_name' => $project->project_name,
                    'project_code' => $project->project_code ?? '',
                    'note_title' => $note->title,
                    'project_url' => route('projects.show', $project),
                ]
            );
        }
    }
}
