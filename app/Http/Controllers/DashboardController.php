<?php

namespace App\Http\Controllers;

use App\Models\Bug;
use App\Models\Document;
use App\Models\Project;
use App\Models\ProjectNote;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = Auth::user();
        $isClient = $user->isClient();
        $projectQuery = Project::query();

        if ($isClient && $user->client) {
            $projectQuery->where('client_id', $user->client->id);
        }

        $projects = $projectQuery->get();
        $projectIds = $projects->pluck('id')->toArray();

        if ($isClient) {
            $totalContractAmount = $projects->sum('contract_amount');
            $totalPaid = $projects->sum(fn (Project $p) => $p->total_paid);
            $totalDue = $projects->sum(fn (Project $p) => $p->due);
            $totalRevenue = $totalContractAmount;
            $totalProfit = $totalPaid;
        } else {
            $totalRevenue = Project::sum('contract_amount');
            $totalProfit = Project::all()->sum(fn (Project $p) => $p->realized_profit);
            $totalPaid = Project::all()->sum(fn (Project $p) => $p->total_paid);
            $totalDue = Project::all()->sum(fn (Project $p) => $p->due);
            $totalContractAmount = $totalRevenue;
        }

        $activeProjects = (clone $projectQuery)->whereIn('status', ['Pending', 'Running'])->count();

        $openBugs = Bug::whereIn('status', ['open', 'in_progress']);
        $activeTasks = Task::where('status', '!=', 'done');
        $documentsCount = Document::query();
        $notesCount = ProjectNote::query();

        if ($isClient) {
            if (empty($projectIds)) {
                $openBugs = 0;
                $activeTasks = 0;
                $documentsCount = 0;
                $notesCount = 0;
            } else {
                $openBugs = $openBugs->whereHas('project', fn ($q) => $q->whereIn('id', $projectIds))->count();
                $activeTasks = $activeTasks->whereHas('project', fn ($q) => $q->whereIn('id', $projectIds))->count();
                // Only count public documents and client-visible notes for client dashboard
                $documentsCount = $documentsCount->whereIn('project_id', $projectIds)->where('is_public', true)->count();
                $notesCount = $notesCount->whereIn('project_id', $projectIds)->where('visibility', 'client')->count();
            }
        } else {
            $openBugs = $openBugs->count();
            $activeTasks = $activeTasks->count();
            $documentsCount = $documentsCount->count();
            $notesCount = $notesCount->count();
        }

        $overviewChart = [
            'labels' => ['Open Bugs', 'Active Tasks', 'Documents', 'Notes'],
            'values' => [$openBugs, $activeTasks, $documentsCount, $notesCount],
        ];

        return view('dashboard', compact(
            'isClient',
            'totalRevenue',
            'totalProfit',
            'totalDue',
            'totalPaid',
            'totalContractAmount',
            'activeProjects',
            'openBugs',
            'activeTasks',
            'documentsCount',
            'notesCount',
            'overviewChart'
        ));
    }
}
