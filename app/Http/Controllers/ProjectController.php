<?php

namespace App\Http\Controllers;

use App\Events\ProjectCreated;
use App\Jobs\SendTemplateMailJob;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\ProjectActivityView;
use App\Models\User;
use App\Services\ProjectDistributionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProjectController extends Controller
{
    private function sendClientProjectCreatedEmail(Project $project, Client $client): void
    {
        $email = $client->user?->email ?? $client->email;
        if (! $email) {
            return;
        }

        SendTemplateMailJob::dispatch(
            'client_project_created',
            $email,
            [
                'client_name' => $client->name,
                'client_email' => $email,
                'project_name' => $project->project_name,
                'project_code' => $project->project_code ?? '',
                'login_url' => route('login'),
            ]
        );
    }

    public function index(): View
    {
        $user = Auth::user();
        $query = Project::with(['client', 'projectPayouts'])
            ->withCount(['tasks', 'tasks as tasks_done_count' => fn ($q) => $q->where('status', 'done')])
            ->orderByDesc('is_pinned')
            ->orderByRaw('CASE WHEN project_code IS NULL OR project_code = "" THEN 1 ELSE 0 END')
            ->orderByDesc('project_code');

        if ($user->isClient() && $user->client) {
            $query->forClient($user->client->id);
        }
        if ($user->isDeveloper()) {
            $query->forDeveloper($user->id);
        }
        if ($user->isSales()) {
            $query->forSales($user->id);
        }

        $projects = $query->get();
        $nextProjectCode = Project::generateNextProjectCode();
        $isClient = $user->isClient();
        $isDeveloper = $user->isDeveloper();
        $isSales = $user->isSales();
        $projectsData = $projects->map(function (Project $p) use ($isDeveloper, $isSales) {
            $paymentStatus = $p->due <= 0 ? 'paid' : ($p->total_paid > 0 ? 'partial' : 'unpaid');
            if ($isDeveloper) {
                $payout = $p->getPayoutFor(\App\Models\ProjectPayout::TYPE_DEVELOPER);
                $paymentStatus = ($payout?->status ?? 'not_paid') === 'paid' ? 'paid' : (($payout?->status ?? '') === 'partial' ? 'partial' : 'unpaid');
            }
            if ($isSales) {
                $payout = $p->getPayoutFor(\App\Models\ProjectPayout::TYPE_SALES);
                $paymentStatus = ($payout?->status ?? 'not_paid') === 'paid' ? 'paid' : (($payout?->status ?? '') === 'partial' ? 'partial' : 'unpaid');
            }
            return [
                'id' => $p->id,
                'project_name' => $p->project_name,
                'project_code' => $p->project_code ?? '',
                'client_name' => ($isDeveloper || $isSales) ? '' : ($p->client->name ?? ''),
                'status' => $p->status ?? '',
                'payment_status' => $paymentStatus,
                'is_pinned' => $p->is_pinned,
            ];
        })->values()->toArray();
        $developers = User::where('role', 'developer')->orderBy('name')->get();
        $sales = User::where('role', 'sales')->orderBy('name')->get();
        return view('projects.index', compact('projects', 'projectsData', 'isClient', 'isDeveloper', 'isSales', 'nextProjectCode', 'developers', 'sales'));
    }

    public function create(): View
    {
        $clients = Client::orderBy('name')->get();
        $nextProjectCode = Project::generateNextProjectCode();
        $developers = User::where('role', 'developer')->orderBy('name')->get();
        $sales = User::where('role', 'sales')->orderBy('name')->get();

        return view('projects.create', compact('clients', 'nextProjectCode', 'developers', 'sales'));
    }

    public function store(Request $request, ProjectDistributionService $distribution): RedirectResponse
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
            'developer_sales_mode' => ['nullable', 'boolean'],
            'sales_commission_enabled' => ['nullable', 'boolean'],
            'sales_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'developer_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'guest_description' => ['nullable', 'string', 'max:10000'],
            'send_email' => ['nullable', 'boolean'],
            'developer_ids' => ['nullable', 'array'],
            'developer_ids.*' => ['integer', 'exists:users,id'],
            'sales_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $devSalesMode = $request->boolean('developer_sales_mode');
        $salesPct = $request->has('sales_percentage') ? (float) $request->input('sales_percentage') : null;
        $devPct = $request->has('developer_percentage') ? (float) $request->input('developer_percentage') : null;
        $validationErrors = $distribution->validateDistribution($devSalesMode, $salesPct, $devPct);
        if (! empty($validationErrors)) {
            return redirect()->back()->withInput($request->input())->withErrors($validationErrors);
        }

        $validated['exclude_from_overhead_profit'] = $request->boolean('exclude_from_overhead_profit');
        $validated['developer_sales_mode'] = $devSalesMode;
        $validated['sales_commission_enabled'] = $request->boolean('sales_commission_enabled');
        $validated['sales_percentage'] = $salesPct ?? ProjectDistributionService::DEFAULT_SALES_PERCENT;
        $validated['developer_percentage'] = $devPct ?? ProjectDistributionService::DEFAULT_DEVELOPER_PERCENT;
        $validated['project_code'] = Project::generateNextProjectCode();
        $validated['is_public'] = true;
        $project = Project::create($validated);

        $developerIds = array_filter(array_map('intval', $request->input('developer_ids', [])));
        $salesIds = $request->filled('sales_id') ? [(int) $request->input('sales_id')] : [];
        $project->developers()->sync($developerIds);
        $project->sales()->sync($salesIds);
        $this->notifyProjectAssigned($project, array_merge($developerIds, $salesIds));

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
        if ($user->isDeveloper() && ! $project->hasDeveloperAssigned($user->id)) {
            abort(403, 'You do not have access to this project.');
        }
        if ($user->isSales() && ! $project->hasSalesAssigned($user->id)) {
            abort(403, 'You do not have access to this project.');
        }

        $project->load(['client', 'additionalClients', 'payments', 'expenses', 'documents' => fn ($q) => $q->with('uploadedBy'), 'contracts' => fn ($q) => $q->with(['uploadedByUser', 'signedByUser', 'audits' => fn ($aq) => $aq->with('user')]), 'tasks' => fn ($q) => $q->with('milestone'), 'milestones', 'bugs', 'projectNotes' => fn ($q) => $q->with('creator'), 'projectLinks', 'projectPayouts'])
            ->loadCount(['tasks', 'tasks as tasks_done_count' => fn ($q) => $q->where('status', 'done')]);

        $isClient = $user->isClient();
        $isDeveloper = $user->isDeveloper();
        $isSales = $user->isSales();
        if ($isClient) {
            $project->setRelation('projectNotes', $project->projectNotes->where('visibility', 'client'));
            $project->setRelation('expenses', $project->expenses->where('is_public', true));
            $project->setRelation('documents', $project->documents->where('is_public', true));
            $project->setRelation('projectLinks', $project->projectLinks->where('visible_to_client', true));
        }
        if ($isDeveloper || $isSales) {
            $project->setRelation('projectNotes', $project->projectNotes->whereIn('visibility', ['client', 'internal_developer']));
            // Developers and sales see links visible to client, guest, or developer
            $project->setRelation('projectLinks', $project->projectLinks->filter(fn ($l) => $l->visible_to_client || $l->is_public || ($l->visible_to_developer ?? false)));
            if ($isDeveloper) {
                $project->setRelation('tasks', $project->tasks->filter(fn ($t) => $t->is_public || $t->assigned_to_user_id === $user->id));
                $project->setRelation('bugs', $project->bugs->filter(fn ($b) => $b->is_public || $b->assigned_to_user_id === $user->id));
            }
        }

        $activitiesQuery = $project->projectActivities()->with('user')->orderByDesc('created_at');
        if ($isClient) {
            $activitiesQuery->where('visibility', 'client');
        }
        if ($isDeveloper || $isSales) {
            $activitiesQuery->whereIn('visibility', [ProjectActivity::VISIBILITY_CLIENT, ProjectActivity::VISIBILITY_DEVELOPER_SALES]);
        }
        $activities = $activitiesQuery->get();

        // Developer and Sales must not see client payment–related activity (payments, invoices)
        if ($isDeveloper || $isSales) {
            $activities = $activities->reject(fn ($a) => in_array($a->action_type, [
                'payment_created',
                'payment_marked_paid',
                'invoice_generated',
            ], true))->values();
        }

        $activityView = ProjectActivityView::where('user_id', $user->id)->where('project_id', $project->id)->first();
        $unviewedQuery = $project->projectActivities()
            ->when($isClient, fn ($q) => $q->where('visibility', ProjectActivity::VISIBILITY_CLIENT))
            ->when($isDeveloper || $isSales, fn ($q) => $q->whereIn('visibility', [ProjectActivity::VISIBILITY_CLIENT, ProjectActivity::VISIBILITY_DEVELOPER_SALES])
                ->whereNotIn('action_type', ['payment_created', 'payment_marked_paid', 'invoice_generated']))
            ->when($activityView, fn ($q) => $q->where('created_at', '>', $activityView->viewed_at));
        $unviewedActivityCount = $unviewedQuery->count();

        $clientsForDropdown = ($user->isClient() || $user->isDeveloper() || $user->isSales()) ? collect() : Client::orderBy('name')->get();
        $developersForAssign = ($user->isAdmin()) ? User::where('role', 'developer')->orderBy('name')->get() : collect();

        return view('projects.show', compact('project', 'isClient', 'isDeveloper', 'isSales', 'activities', 'clientsForDropdown', 'unviewedActivityCount', 'developersForAssign'));
    }

    public function markActivityViewed(Project $project): \Illuminate\Http\JsonResponse
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
        if ($user->isDeveloper() && ! $project->hasDeveloperAssigned($user->id)) {
            abort(403, 'You do not have access to this project.');
        }
        if ($user->isSales() && ! $project->hasSalesAssigned($user->id)) {
            abort(403, 'You do not have access to this project.');
        }

        ProjectActivityView::updateOrCreate(
            ['user_id' => $user->id, 'project_id' => $project->id],
            ['viewed_at' => now()]
        );

        return response()->json(['ok' => true]);
    }

    public function updateClient(Request $request, Project $project): RedirectResponse
    {
        if (Auth::user()->isClient()) {
            abort(403);
        }
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
        ]);
        $newClient = Client::find($validated['client_id']);
        $project->update($validated);
        if ($newClient) {
            $this->sendClientProjectCreatedEmail($project, $newClient);
        }
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
        $addedClient = Client::find($clientId);
        if ($addedClient) {
            $this->sendClientProjectCreatedEmail($project, $addedClient);
        }
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
        $developers = User::where('role', 'developer')->orderBy('name')->get();
        $sales = User::where('role', 'sales')->orderBy('name')->get();
        return view('projects.edit', compact('project', 'clients', 'developers', 'sales'));
    }

    public function update(Request $request, Project $project, ProjectDistributionService $distribution): RedirectResponse
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
            'developer_sales_mode' => ['nullable', 'boolean'],
            'sales_commission_enabled' => ['nullable', 'boolean'],
            'sales_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'developer_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_public' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'featured_image' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:2048'],
            'remove_featured_image' => ['nullable', 'boolean'],
            'tech_stack' => ['nullable', 'string', 'max:255'],
            'guest_description' => ['nullable', 'string', 'max:10000'],
            'developer_ids' => ['nullable', 'array'],
            'developer_ids.*' => ['integer', 'exists:users,id'],
            'sales_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $validated['featured_image_path'] = $project->featured_image_path;
        if ($request->boolean('remove_featured_image')) {
            $this->deleteFeaturedImageIfExists($project->featured_image_path);
            $validated['featured_image_path'] = null;
        }
        if ($request->hasFile('featured_image')) {
            $this->deleteFeaturedImageIfExists($project->featured_image_path);
            $path = $request->file('featured_image')->store('featured-projects', 'public');
            $validated['featured_image_path'] = 'storage/' . $path;
        }

        $devSalesMode = $request->boolean('developer_sales_mode');
        $salesPct = $request->has('sales_percentage') ? (float) $request->input('sales_percentage') : null;
        $devPct = $request->has('developer_percentage') ? (float) $request->input('developer_percentage') : null;
        $validationErrors = $distribution->validateDistribution($devSalesMode, $salesPct, $devPct);
        if (! empty($validationErrors)) {
            return redirect()->back()->withInput($request->input())->withErrors($validationErrors);
        }

        $validated['exclude_from_overhead_profit'] = $request->boolean('exclude_from_overhead_profit');
        $validated['developer_sales_mode'] = $devSalesMode;
        $validated['sales_commission_enabled'] = $request->boolean('sales_commission_enabled');
        $validated['sales_percentage'] = $salesPct ?? $project->sales_percentage ?? ProjectDistributionService::DEFAULT_SALES_PERCENT;
        $validated['developer_percentage'] = $devPct ?? $project->developer_percentage ?? ProjectDistributionService::DEFAULT_DEVELOPER_PERCENT;
        $validated['is_public'] = $request->boolean('is_public');
        $validated['is_featured'] = $request->boolean('is_featured');
        unset($validated['project_code']);
        $project->update($validated);

        $developerIds = array_filter(array_map('intval', $request->input('developer_ids', [])));
        $salesIds = $request->filled('sales_id') ? [(int) $request->input('sales_id')] : [];
        $previousDeveloperIds = $project->developers()->pluck('users.id')->all();
        $previousSalesIds = $project->sales()->pluck('users.id')->all();
        $project->developers()->sync($developerIds);
        $project->sales()->sync($salesIds);
        $newlyAssigned = array_merge(
            array_diff($developerIds, $previousDeveloperIds),
            array_diff($salesIds, $previousSalesIds)
        );
        $this->notifyProjectAssigned($project, array_unique($newlyAssigned));

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

    public function togglePin(Project $project): RedirectResponse
    {
        $project->update(['is_pinned' => ! $project->is_pinned]);
        return redirect()->back()->with('success', $project->is_pinned ? 'Project pinned.' : 'Project unpinned.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->deleteFeaturedImageIfExists($project->featured_image_path);
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted.');
    }

    private function deleteFeaturedImageIfExists(?string $path): void
    {
        if (! $path || str_starts_with($path, 'http')) {
            return;
        }
        $storagePath = preg_replace('#^storage/#', '', $path);
        if ($storagePath && Storage::disk('public')->exists($storagePath)) {
            Storage::disk('public')->delete($storagePath);
        }
    }

    /** Send project-assigned email to given user IDs (developers/sales). */
    private function notifyProjectAssigned(Project $project, array $userIds): void
    {
        $users = User::whereIn('id', $userIds)->whereIn('role', ['developer', 'sales'])->get();
        $loginUrl = route('login');
        foreach ($users as $user) {
            if (! $user->email) {
                continue;
            }
            SendTemplateMailJob::dispatch(
                'project_assigned',
                $user->email,
                [
                    'name' => $user->name,
                    'project_name' => $project->project_name,
                    'project_code' => $project->project_code ?? '',
                    'login_url' => $loginUrl,
                ]
            );
        }
    }
}
