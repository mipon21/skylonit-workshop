<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $projects = Project::with('client')
            ->withCount(['tasks', 'tasks as tasks_done_count' => fn ($q) => $q->where('status', 'done')])
            ->orderByRaw('CASE WHEN project_code IS NULL OR project_code = "" THEN 1 ELSE 0 END')
            ->orderByDesc('project_code')
            ->get();
        $projectsData = $projects->map(fn (Project $p) => [
            'id' => $p->id,
            'project_name' => $p->project_name,
            'project_code' => $p->project_code ?? '',
            'client_name' => $p->client->name ?? '',
            'status' => $p->status ?? '',
            'payment_status' => $p->due <= 0 ? 'paid' : ($p->total_paid > 0 ? 'partial' : 'unpaid'),
        ])->values()->toArray();
        return view('projects.index', compact('projects', 'projectsData'));
    }

    public function create(): View
    {
        $clients = Client::orderBy('name')->get();
        return view('projects.create', compact('clients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'project_name' => ['required', 'string', 'max:255'],
            'project_code' => ['nullable', 'string', 'max:100'],
            'project_type' => ['nullable', 'string', 'in:' . implode(',', Project::PROJECT_TYPES)],
            'contract_amount' => ['required', 'numeric', 'min:0'],
            'contract_date' => ['nullable', 'date'],
            'delivery_date' => ['nullable', 'date'],
            'status' => ['required', 'in:Pending,Running,Complete,On Hold'],
            'exclude_from_overhead_profit' => ['nullable', 'boolean'],
        ]);
        $validated['exclude_from_overhead_profit'] = $request->boolean('exclude_from_overhead_profit');
        Project::create($validated);
        return redirect()->route('projects.index')->with('success', 'Project created.');
    }

    public function show(Project $project): View
    {
        $project->load(['client', 'payments', 'expenses', 'documents', 'tasks', 'bugs', 'projectNotes', 'projectLinks', 'projectPayouts'])
            ->loadCount(['tasks', 'tasks as tasks_done_count' => fn ($q) => $q->where('status', 'done')]);
        return view('projects.show', compact('project'));
    }

    public function edit(Project $project): View
    {
        $clients = Client::orderBy('name')->get();
        return view('projects.edit', compact('project', 'clients'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'project_name' => ['required', 'string', 'max:255'],
            'project_code' => ['nullable', 'string', 'max:100'],
            'project_type' => ['nullable', 'string', 'in:' . implode(',', Project::PROJECT_TYPES)],
            'contract_amount' => ['required', 'numeric', 'min:0'],
            'contract_date' => ['nullable', 'date'],
            'delivery_date' => ['nullable', 'date'],
            'status' => ['required', 'in:Pending,Running,Complete,On Hold'],
            'exclude_from_overhead_profit' => ['nullable', 'boolean'],
        ]);
        $validated['exclude_from_overhead_profit'] = $request->boolean('exclude_from_overhead_profit');
        $project->update($validated);
        return redirect()->route('projects.show', $project)->with('success', 'Project updated.');
    }

    public function updateStatus(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:Pending,Running,Complete,On Hold'],
        ]);
        $project->update($validated);
        return redirect()->route('projects.show', $project)->with('success', 'Status updated.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted.');
    }
}
