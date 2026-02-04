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
            'is_public' => ['nullable', 'boolean'],
        ]);
        $validated['is_public'] = $request->boolean('is_public');
        $project->expenses()->create($validated);
        return redirect()->route('projects.show', $project)->withFragment('expenses')->with('success', 'Expense added.');
    }

    public function update(Request $request, Project $project, Expense $expense): RedirectResponse
    {
        if ($expense->project_id !== $project->id) {
            abort(404);
        }
        $validated = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'is_public' => ['nullable', 'boolean'],
        ]);
        $data = [];
        if ($request->has('amount')) {
            $data['amount'] = (float) $request->input('amount');
        }
        if ($request->has('note')) {
            $data['note'] = $request->input('note');
        }
        if ($request->has('is_public')) {
            $data['is_public'] = $request->boolean('is_public');
        }
        $expense->update($data);
        $message = count($data) > 1 || isset($data['amount']) || isset($data['note'])
            ? 'Expense updated.'
            : 'Expense visibility updated.';
        return redirect()->route('projects.show', $project)->withFragment('expenses')->with('success', $message);
    }

    public function destroy(Project $project, Expense $expense): RedirectResponse
    {
        $expense->delete();
        return redirect()->route('projects.show', $project)->withFragment('expenses')->with('success', 'Expense removed.');
    }
}
