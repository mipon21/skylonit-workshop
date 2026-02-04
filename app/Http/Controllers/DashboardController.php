<?php

namespace App\Http\Controllers;

use App\Models\Bug;
use App\Models\Document;
use App\Models\Project;
use App\Models\ProjectNote;
use App\Models\Task;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $totalRevenue = Project::sum('contract_amount');
        $totalProfit = Project::all()->sum(fn (Project $p) => $p->realized_profit);
        $totalDue = Project::all()->sum(fn (Project $p) => $p->due);
        $activeProjects = Project::whereIn('status', ['Pending', 'Running'])->count();
        $openBugs = Bug::whereIn('status', ['open', 'in_progress'])->count();
        $activeTasks = Task::where('status', '!=', 'done')->count();
        $documentsCount = Document::count();
        $notesCount = ProjectNote::count();

        $overviewChart = [
            'labels' => ['Open Bugs', 'Active Tasks', 'Documents', 'Notes'],
            'values' => [$openBugs, $activeTasks, $documentsCount, $notesCount],
        ];

        return view('dashboard', compact(
            'totalRevenue', 'totalProfit', 'totalDue', 'activeProjects',
            'openBugs', 'activeTasks', 'documentsCount', 'notesCount',
            'overviewChart'
        ));
    }
}
