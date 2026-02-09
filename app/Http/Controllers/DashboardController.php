<?php

namespace App\Http\Controllers;

use App\Models\Bug;
use App\Models\ClientNotification;
use App\Models\Document;
use App\Models\Project;
use App\Models\ProjectActivity;
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
        $isDeveloper = $user->isDeveloper();
        $isSales = $user->isSales();
        $projectQuery = Project::query();

        if ($isClient && $user->client) {
            $projectQuery->forClient($user->client->id);
        }
        if ($isDeveloper) {
            $projectQuery->forDeveloper($user->id);
        }
        if ($isSales) {
            $projectQuery->forSales($user->id);
        }

        $projects = $projectQuery->get();
        $projectIds = $projects->pluck('id')->toArray();

        if ($isClient) {
            $totalContractAmount = $projects->sum('contract_amount');
            $totalPaid = $projects->sum(fn (Project $p) => $p->total_paid);
            $totalDue = $projects->sum(fn (Project $p) => $p->due);
            $totalRevenue = $totalContractAmount;
            $totalProfit = $totalPaid;
        } elseif ($isDeveloper || $isSales) {
            $totalRevenue = 0;
            $totalProfit = 0;
            $totalPaid = 0;
            $totalDue = 0;
            $totalContractAmount = 0;
        } else {
            $totalRevenue = Project::sum('contract_amount');
            $totalProfit = Project::all()->sum(fn (Project $p) => $p->paid_profit);
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
                $documentsCount = $documentsCount->whereIn('project_id', $projectIds)->where('is_public', true)->count();
                $notesCount = $notesCount->whereIn('project_id', $projectIds)->where('visibility', 'client')->count();
            }
        } elseif ($isDeveloper) {
            if (empty($projectIds)) {
                $openBugs = 0;
                $activeTasks = 0;
                $documentsCount = 0;
                $notesCount = 0;
            } else {
                $openBugs = $openBugs->whereHas('project', fn ($q) => $q->whereIn('id', $projectIds))->where('assigned_to_user_id', $user->id)->count();
                $activeTasks = $activeTasks->whereHas('project', fn ($q) => $q->whereIn('id', $projectIds))->where('assigned_to_user_id', $user->id)->count();
                $documentsCount = $documentsCount->whereIn('project_id', $projectIds)->where('is_public', true)->count();
                $notesCount = $notesCount->whereIn('project_id', $projectIds)->where('visibility', 'client')->count();
            }
        } elseif ($isSales) {
            if (empty($projectIds)) {
                $openBugs = 0;
                $activeTasks = 0;
                $documentsCount = 0;
                $notesCount = 0;
            } else {
                $openBugs = $openBugs->whereHas('project', fn ($q) => $q->whereIn('id', $projectIds))->count();
                $activeTasks = $activeTasks->whereHas('project', fn ($q) => $q->whereIn('id', $projectIds))->count();
                $documentsCount = $documentsCount->whereIn('project_id', $projectIds)->where('is_public', true)->count();
                $notesCount = $notesCount->whereIn('project_id', $projectIds)->whereIn('visibility', ['client', 'internal'])->count();
            }
        } else {
            $openBugs = $openBugs->count();
            $activeTasks = $activeTasks->count();
            $documentsCount = $documentsCount->count();
            $notesCount = $notesCount->count();
        }

        $recentActivitiesQuery = ProjectActivity::with(['project', 'user'])
            ->orderByDesc('created_at')
            ->limit(50);
        if ($isClient) {
            if (empty($projectIds)) {
                $recentActivities = collect();
            } else {
                $recentActivities = $recentActivitiesQuery->whereIn('project_id', $projectIds)
                    ->where('visibility', 'client')
                    ->get();
            }
        } elseif ($isDeveloper || $isSales) {
            if (empty($projectIds)) {
                $recentActivities = collect();
            } else {
                $recentActivities = $recentActivitiesQuery->whereIn('project_id', $projectIds)
                    ->whereIn('visibility', [ProjectActivity::VISIBILITY_CLIENT, ProjectActivity::VISIBILITY_DEVELOPER_SALES])
                    ->get()
                    ->reject(fn ($a) => in_array($a->action_type, [
                        'payment_created',
                        'payment_marked_paid',
                        'invoice_generated',
                    ], true))
                    ->values();
            }
        } else {
            $recentActivities = $recentActivitiesQuery->get();
        }

        $clientNotifications = collect();
        if ($isClient && $user->client) {
            $clientNotifications = ClientNotification::where('client_id', $user->client->id)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();
        }

        $assignedTasksDone = 0;
        $assignedBugsSolved = 0;
        if ($isDeveloper && ! empty($projectIds)) {
            $assignedTasksDone = Task::whereIn('project_id', $projectIds)->where('assigned_to_user_id', $user->id)->where('status', 'done')->count();
            $assignedBugsSolved = Bug::whereIn('project_id', $projectIds)->where('assigned_to_user_id', $user->id)->whereIn('status', ['resolved'])->count();
        }

        return view('dashboard', compact(
            'isClient',
            'isDeveloper',
            'isSales',
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
            'recentActivities',
            'clientNotifications',
            'projects',
            'assignedTasksDone',
            'assignedBugsSolved'
        ));
    }
}
