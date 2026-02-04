<?php

namespace App\Http\Controllers;

use App\Models\Bug;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BugController extends Controller
{
    private function authorizeProjectForClient(Project $project): void
    {
        if (Auth::user()->isClient() && (! Auth::user()->client || $project->client_id !== Auth::user()->client->id)) {
            abort(403, 'You do not have access to this project.');
        }
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeProjectForClient($project);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'severity' => ['required', 'in:minor,major,critical'],
            'status' => ['required', 'in:open,in_progress,resolved'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,png,jpg,jpeg,zip,txt', 'max:10240'],
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('bug-attachments/' . $project->id, 'local');
            $validated['attachment_path'] = $path;
        }
        unset($validated['attachment']);

        $project->bugs()->create($validated);
        return redirect()->route('projects.show', $project)->withFragment('bugs')->with('success', 'Bug report added.');
    }

    public function downloadAttachment(Project $project, Bug $bug): StreamedResponse
    {
        $this->authorizeProjectForClient($project);
        if ($bug->project_id !== $project->id || ! $bug->attachment_path) {
            abort(404);
        }
        $name = basename($bug->attachment_path);
        return Storage::download($bug->attachment_path, $name);
    }

    public function update(Request $request, Project $project, Bug $bug): RedirectResponse
    {
        if ($bug->project_id !== $project->id) {
            abort(404);
        }
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'severity' => ['sometimes', 'required', 'in:minor,major,critical'],
            'status' => ['sometimes', 'required', 'in:open,in_progress,resolved'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,png,jpg,jpeg,zip,txt', 'max:10240'],
            'remove_attachment' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('remove_attachment') || $request->hasFile('attachment')) {
            if ($bug->attachment_path) {
                Storage::delete($bug->attachment_path);
            }
            $validated['attachment_path'] = null;
        }
        if ($request->hasFile('attachment')) {
            $validated['attachment_path'] = $request->file('attachment')->store('bug-attachments/' . $project->id, 'local');
        }
        unset($validated['attachment'], $validated['remove_attachment']);

        $bug->update($validated);
        return redirect()->route('projects.show', $project)->withFragment('bugs')->with('success', 'Bug updated.');
    }

    public function destroy(Project $project, Bug $bug): RedirectResponse
    {
        if ($bug->project_id !== $project->id) {
            abort(404);
        }
        if ($bug->attachment_path) {
            Storage::delete($bug->attachment_path);
        }
        $bug->delete();
        return redirect()->route('projects.show', $project)->withFragment('bugs')->with('success', 'Bug deleted.');
    }
}
