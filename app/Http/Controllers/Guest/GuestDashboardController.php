<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Bug;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GuestDashboardController extends Controller
{
    /**
     * Guest (public) dashboard: only public projects, tasks, bugs. No revenue/profit/due/payments/expenses/clients.
     * If user is already logged in (client or admin), redirect to their dashboard.
     */
    public function __invoke(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $publicProjectIds = Project::where('is_public', true)->pluck('id')->toArray();

        $totalPublicProjects = count($publicProjectIds);
        $runningPublicProjects = Project::where('is_public', true)->whereIn('status', ['Pending', 'Running'])->count();
        $openPublicTasks = Task::where('is_public', true)->where('status', '!=', 'done')
            ->whereIn('project_id', $publicProjectIds)
            ->count();
        $openPublicBugs = Bug::where('is_public', true)->whereIn('status', ['open', 'in_progress'])
            ->whereIn('project_id', $publicProjectIds)
            ->count();

        return view('guest.dashboard', compact(
            'totalPublicProjects',
            'runningPublicProjects',
            'openPublicTasks',
            'openPublicBugs'
        ));
    }
}
