<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        $clients = Client::withCount('projects')->with('user')->orderBy('name')->get();
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
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'address' => ['nullable', 'string'],
            'fb_link' => ['nullable', 'string', 'max:255'],
            'kyc' => ['nullable', 'string', 'max:255'],
        ];
        $validated = $request->validate($rules);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'client',
        ]);

        $client = Client::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'],
            'address' => $validated['address'] ?? null,
            'fb_link' => $validated['fb_link'] ?? null,
            'kyc' => $validated['kyc'] ?? null,
        ]);

        return redirect()->route('clients.index')->with('success', 'Client created. Send email + password to client manually.');
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
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'password' => [$client->user_id ? 'nullable' : 'required', 'string', 'confirmed', Password::defaults()],
            'address' => ['nullable', 'string'],
            'fb_link' => ['nullable', 'string', 'max:255'],
            'kyc' => ['nullable', 'string', 'max:255'],
        ];
        if ($client->user_id) {
            $rules['email'][] = 'unique:users,email,' . $client->user_id;
        } else {
            $rules['email'][] = 'unique:users,email';
        }
        $validated = $request->validate($rules);

        $client->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'],
            'address' => $validated['address'] ?? null,
            'fb_link' => $validated['fb_link'] ?? null,
            'kyc' => $validated['kyc'] ?? null,
        ]);

        if ($client->user_id) {
            $user = $client->user;
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            if (! empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }
            $user->save();
        } else {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'client',
            ]);
            $client->update(['user_id' => $user->id]);
        }

        return redirect()->route('clients.index')->with('success', 'Client updated.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        if ($client->user_id) {
            $client->user()->delete();
        }
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Client deleted.');
    }
}
