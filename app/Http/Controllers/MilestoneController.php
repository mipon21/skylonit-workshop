<?php

namespace App\Http\Controllers;

use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MilestoneController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);
        $maxOrder = $project->milestones()->max('sort_order') ?? 0;
        $project->milestones()->create([
            'name' => $validated['name'],
            'sort_order' => $maxOrder + 1,
        ]);
        return redirect()->route('projects.show', $project)->withFragment('tasks')->with('success', 'Milestone added.');
    }

    public function update(Request $request, Project $project, Milestone $milestone): RedirectResponse
    {
        if ($milestone->project_id !== $project->id) {
            abort(404);
        }
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);
        $milestone->update($validated);
        return redirect()->route('projects.show', $project)->withFragment('tasks')->with('success', 'Milestone updated.');
    }

    public function destroy(Project $project, Milestone $milestone): RedirectResponse
    {
        if ($milestone->project_id !== $project->id) {
            abort(404);
        }
        $milestone->tasks()->update(['milestone_id' => null]);
        $milestone->delete();
        return redirect()->route('projects.show', $project)->withFragment('tasks')->with('success', 'Milestone removed.');
    }
}
