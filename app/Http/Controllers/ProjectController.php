<?php

namespace App\Http\Controllers;

use App\Events\ProjectCreated;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $query = Project::with('client')
            ->withCount(['tasks', 'tasks as tasks_done_count' => fn ($q) => $q->where('status', 'done')])
            ->orderByRaw('CASE WHEN project_code IS NULL OR project_code = "" THEN 1 ELSE 0 END')
            ->orderByDesc('project_code');

        if (Auth::user()->isClient() && Auth::user()->client) {
            $query->forClient(Auth::user()->client->id);
        }

        $projects = $query->get();
        $nextProjectCode = Project::generateNextProjectCode();
        $projectsData = $projects->map(fn (Project $p) => [
            'id' => $p->id,
            'project_name' => $p->project_name,
            'project_code' => $p->project_code ?? '',
            'client_name' => $p->client->name ?? '',
            'status' => $p->status ?? '',
            'payment_status' => $p->due <= 0 ? 'paid' : ($p->total_paid > 0 ? 'partial' : 'unpaid'),
        ])->values()->toArray();
        $isClient = Auth::user()->isClient();
        return view('projects.index', compact('projects', 'projectsData', 'isClient', 'nextProjectCode'));
    }

    public function create(): View
    {
        $clients = Client::orderBy('name')->get();
        $nextProjectCode = Project::generateNextProjectCode();

        return view('projects.create', compact('clients', 'nextProjectCode'));
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
            'send_email' => ['nullable', 'boolean'],
        ]);
        $validated['exclude_from_overhead_profit'] = $request->boolean('exclude_from_overhead_profit');
        $validated['project_code'] = Project::generateNextProjectCode();
        $validated['is_public'] = true; // New projects are public (guest viewable) by default
        $project = Project::create($validated);
        event(new ProjectCreated($project, $request->boolean('send_email')));
        return redirect()->route('projects.index')->with('success', 'Project created.');
    }

    public function show(Project $project): View
    {
        $user = Auth::user();
        if ($user->isClient()) {
            if (! $user->client) {
                abort(403, 'You do not have access to this project.');
            }
            $hasAccess = $project->client_id === $user->client->id
                || $project->additionalClients()->where('clients.id', $user->client->id)->exists();
            if (! $hasAccess) {
                abort(403, 'You do not have access to this project.');
            }
        }

        $project->load(['client', 'additionalClients', 'payments', 'expenses', 'documents' => fn ($q) => $q->with('uploadedBy'), 'contracts' => fn ($q) => $q->with(['uploadedByUser', 'signedByUser', 'audits' => fn ($aq) => $aq->with('user')]), 'tasks', 'bugs', 'projectNotes', 'projectLinks', 'projectPayouts'])
            ->loadCount(['tasks', 'tasks as tasks_done_count' => fn ($q) => $q->where('status', 'done')]);

        $isClient = $user->isClient();
        if ($isClient) {
            $project->setRelation('projectNotes', $project->projectNotes->where('visibility', 'client'));
            $project->setRelation('expenses', $project->expenses->where('is_public', true));
            $project->setRelation('documents', $project->documents->where('is_public', true));
            $project->setRelation('projectLinks', $project->projectLinks->where('visible_to_client', true));
        }

        $activitiesQuery = $project->projectActivities()->with('user')->orderByDesc('created_at');
        if ($isClient) {
            $activitiesQuery->where('visibility', 'client');
        }
        $activities = $activitiesQuery->get();

        $clientsForDropdown = $user->isClient() ? collect() : Client::orderBy('name')->get();

        return view('projects.show', compact('project', 'isClient', 'activities', 'clientsForDropdown'));
    }

    public function updateClient(Request $request, Project $project): RedirectResponse
    {
        if (Auth::user()->isClient()) {
            abort(403);
        }
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
        ]);
        $project->update($validated);
        return redirect()->route('projects.show', $project)->withFragment('client')->with('success', 'Primary client updated.');
    }

    public function addClient(Request $request, Project $project): RedirectResponse
    {
        if (Auth::user()->isClient()) {
            abort(403);
        }
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
        ]);
        $clientId = (int) $validated['client_id'];
        if ($clientId === $project->client_id) {
            return redirect()->route('projects.show', $project)->withFragment('client')->with('info', 'That client is already the primary client.');
        }
        if ($project->additionalClients()->where('clients.id', $clientId)->exists()) {
            return redirect()->route('projects.show', $project)->withFragment('client')->with('info', 'That client is already linked.');
        }
        $project->additionalClients()->attach($clientId);
        return redirect()->route('projects.show', $project)->withFragment('client')->with('success', 'Client added to project.');
    }

    public function removeClient(Project $project, Client $client): RedirectResponse
    {
        if (Auth::user()->isClient()) {
            abort(403);
        }
        if ($project->client_id === $client->id) {
            return redirect()->route('projects.show', $project)->withFragment('client')->with('error', 'Cannot remove the primary client. Change primary client first.');
        }
        $project->additionalClients()->detach($client->id);
        return redirect()->route('projects.show', $project)->withFragment('client')->with('success', 'Client removed from project.');
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
            'is_public' => ['nullable', 'boolean'],
        ]);
        $validated['exclude_from_overhead_profit'] = $request->boolean('exclude_from_overhead_profit');
        $validated['is_public'] = $request->boolean('is_public');
        unset($validated['project_code']); // Project code is not editable; keep existing value
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
