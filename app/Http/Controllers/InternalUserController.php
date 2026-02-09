<?php

namespace App\Http\Controllers;

use App\Events\InternalUserCreated;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class InternalUserController extends Controller
{
    private function role(): string
    {
        $name = request()->route()?->getName() ?? '';
        if (str_starts_with($name, 'developers.')) {
            return 'developer';
        }
        if (str_starts_with($name, 'sales.')) {
            return 'sales';
        }
        abort(404);
    }

    private function roleLabel(): string
    {
        return ucfirst($this->role());
    }

    public function index(): View
    {
        $role = $this->role();
        $users = User::where('role', $role)
            ->orderBy('name')
            ->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
            ]);
        return view('internal-users.index', [
            'users' => User::where('role', $role)->orderBy('name')->get(),
            'usersData' => $users->values()->toArray(),
            'role' => $role,
            'roleLabel' => $this->roleLabel(),
        ]);
    }

    public function create(): View
    {
        return view('internal-users.create', [
            'role' => $this->role(),
            'roleLabel' => $this->roleLabel(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $role = $this->role();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'send_email' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $role,
        ]);

        event(new InternalUserCreated($user, $request->boolean('send_email'), $validated['password']));

        $routeName = $role === 'developer' ? 'developers.index' : 'sales.index';
        return redirect()->route($routeName)->with('success', $this->roleLabel() . ' account created.' . ($request->boolean('send_email') ? ' Email notification queued if template is enabled.' : ''));
    }

    public function show(): View
    {
        $user = request()->route()->parameter('developer') ?? request()->route()->parameter('sale');
        if (! $user instanceof User) {
            abort(404);
        }
        $role = $this->role();
        if ($user->role !== $role) {
            abort(404);
        }
        $user->load(['paymentMethods', 'projectsAsDeveloper', 'projectsAsSales']);
        return view('internal-users.show', [
            'user' => $user,
            'role' => $role,
            'roleLabel' => $this->roleLabel(),
        ]);
    }

    public function edit(): View
    {
        $user = request()->route()->parameter('developer') ?? request()->route()->parameter('sale');
        if (! $user instanceof User) {
            abort(404);
        }
        $role = $this->role();
        if ($user->role !== $role) {
            abort(404);
        }
        return view('internal-users.edit', [
            'user' => $user,
            'role' => $role,
            'roleLabel' => $this->roleLabel(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = request()->route()->parameter('developer') ?? request()->route()->parameter('sale');
        if (! $user instanceof User) {
            abort(404);
        }
        $role = $this->role();
        if ($user->role !== $role) {
            abort(404);
        }
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'confirmed', Password::defaults()],
            'send_email' => ['nullable', 'boolean'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        $routeName = $role === 'developer' ? 'developers.index' : 'sales.index';
        $msg = $this->roleLabel() . ' account updated.';
        if ($request->boolean('send_email') && ! empty($validated['password'])) {
            event(new InternalUserCreated($user->fresh(), true, $validated['password']));
            $msg .= ' Email with new password queued if template is enabled.';
        }
        return redirect()->route($routeName)->with('success', $msg);
    }

    public function destroy(): RedirectResponse
    {
        $user = request()->route()->parameter('developer') ?? request()->route()->parameter('sale');
        if (! $user instanceof User) {
            abort(404);
        }
        $role = $this->role();
        if ($user->role !== $role) {
            abort(404);
        }
        $user->delete();
        $routeName = $role === 'developer' ? 'developers.index' : 'sales.index';
        return redirect()->route($routeName)->with('success', $this->roleLabel() . ' account deleted.');
    }
}
