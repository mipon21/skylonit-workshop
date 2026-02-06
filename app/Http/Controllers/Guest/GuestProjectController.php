<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestProjectController extends Controller
{
    /**
     * Public project list: only is_public = true. No client/budget/payment info.
     */
    public function index(): View
    {
        $projects = Project::where('is_public', true)
            ->with(['projectLinks' => fn ($q) => $q->where('is_public', true)])
            ->withCount(['tasks', 'tasks as tasks_done_count' => fn ($q) => $q->where('status', 'done')])
            ->orderByRaw('CASE WHEN project_code IS NULL OR project_code = "" THEN 1 ELSE 0 END')
            ->orderByDesc('project_code')
            ->get();

        return view('guest.projects.index', compact('projects'));
    }

    /**
     * Public project detail: sanitized. Only name, status, dates, public tasks, public bugs, public links/APK.
     */
    public function show(Request $request, Project $project): View
    {
        if (! $project->is_public) {
            abort(404);
        }

        $project->load([
            'tasks' => fn ($q) => $q->where('is_public', true)->orderByRaw("CASE status WHEN 'todo' THEN 1 WHEN 'doing' THEN 2 ELSE 3 END"),
            'bugs' => fn ($q) => $q->where('is_public', true),
            'projectLinks' => fn ($q) => $q->where('is_public', true),
        ]);
        $project->loadCount(['tasks as tasks_done_count' => fn ($q) => $q->where('is_public', true)->where('status', 'done')]);

        return view('guest.projects.show', compact('project'));
    }
}
