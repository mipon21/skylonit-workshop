<?php

namespace App\Http\Controllers;

use App\Events\LinkCreated;
use App\Models\Project;
use App\Models\ProjectLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectLinkController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:2048', 'url'],
            'login_username' => ['nullable', 'string', 'max:255'],
            'login_password' => ['nullable', 'string', 'max:255'],
            'is_public' => ['nullable', 'boolean'],
            'send_email' => ['nullable', 'boolean'],
        ]);
        $validated['is_public'] = $request->boolean('is_public');
        $link = $project->projectLinks()->create($validated);
        event(new LinkCreated($link, $request->boolean('send_email')));
        return redirect()->route('projects.show', $project)->withFragment('links')->with('success', 'Link added.');
    }

    public function update(Request $request, Project $project, ProjectLink $project_link): RedirectResponse
    {
        if ($project_link->project_id !== $project->id) {
            abort(404);
        }
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:2048', 'url'],
            'login_username' => ['nullable', 'string', 'max:255'],
            'login_password' => ['nullable', 'string', 'max:255'],
            'is_public' => ['nullable', 'boolean'],
        ]);
        $validated['is_public'] = $request->boolean('is_public');
        $project_link->update($validated);
        return redirect()->route('projects.show', $project)->withFragment('links')->with('success', 'Link updated.');
    }

    public function destroy(Project $project, ProjectLink $project_link): RedirectResponse
    {
        if ($project_link->project_id !== $project->id) {
            abort(404);
        }
        $project_link->delete();
        return redirect()->route('projects.show', $project)->withFragment('links')->with('success', 'Link removed.');
    }
}
