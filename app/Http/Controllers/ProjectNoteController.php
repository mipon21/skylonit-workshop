<?php

namespace App\Http\Controllers;

use App\Events\NoteCreated;
use App\Models\Project;
use App\Models\ProjectNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectNoteController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'visibility' => ['required', 'in:internal,client'],
            'send_email' => ['nullable', 'boolean'],
        ]);
        $validated['created_by'] = auth()->id();
        $note = $project->projectNotes()->create($validated);
        event(new NoteCreated($note, $request->boolean('send_email')));
        return redirect()->route('projects.show', $project)->withFragment('notes')->with('success', 'Note added.');
    }

    public function update(Request $request, Project $project, ProjectNote $projectNote): RedirectResponse
    {
        if ($projectNote->project_id !== $project->id) {
            abort(404);
        }
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'visibility' => ['required', 'in:internal,client'],
        ]);
        $projectNote->update($validated);
        return redirect()->route('projects.show', $project)->withFragment('notes')->with('success', 'Note updated.');
    }

    public function destroy(Project $project, ProjectNote $projectNote): RedirectResponse
    {
        if ($projectNote->project_id !== $project->id) {
            abort(404);
        }
        $projectNote->delete();
        return redirect()->route('projects.show', $project)->withFragment('notes')->with('success', 'Note deleted.');
    }
}
