<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        $clients = Client::withCount('projects')->orderBy('name')->get();
        $clientsData = $clients->map(fn (Client $c) => [
            'id' => $c->id,
            'name' => $c->name ?? '',
            'phone' => $c->phone ?? '',
            'email' => $c->email ?? '',
        ])->values()->toArray();
        return view('clients.index', compact('clients', 'clientsData'));
    }

    public function create(): View
    {
        return view('clients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'fb_link' => ['nullable', 'string', 'max:255'],
            'kyc' => ['nullable', 'string', 'max:255'],
        ]);
        Client::create($validated);
        return redirect()->route('clients.index')->with('success', 'Client created.');
    }

    public function show(Client $client): View
    {
        $client->load(['projects' => function ($q) {
            $q->withCount(['tasks', 'tasks as tasks_done_count' => fn ($q) => $q->where('status', 'done')]);
        }]);
        $projectsData = $client->projects->map(fn (Project $p) => [
            'id' => $p->id,
            'project_name' => $p->project_name,
            'project_code' => $p->project_code ?? '',
            'client_name' => $client->name,
            'status' => $p->status ?? '',
            'payment_status' => $p->due <= 0 ? 'paid' : ($p->total_paid > 0 ? 'partial' : 'unpaid'),
        ])->values()->toArray();
        return view('clients.show', compact('client', 'projectsData'));
    }

    public function edit(Client $client): View
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'fb_link' => ['nullable', 'string', 'max:255'],
            'kyc' => ['nullable', 'string', 'max:255'],
        ]);
        $client->update($validated);
        return redirect()->route('clients.index')->with('success', 'Client updated.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Client deleted.');
    }
}
