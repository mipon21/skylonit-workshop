<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
        ]);
        $project->expenses()->create($validated);
        return redirect()->route('projects.show', $project)->withFragment('expenses')->with('success', 'Expense added.');
    }

    public function destroy(Project $project, Expense $expense): RedirectResponse
    {
        $expense->delete();
        return redirect()->route('projects.show', $project)->withFragment('expenses')->with('success', 'Expense removed.');
    }
}
