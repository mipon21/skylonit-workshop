<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\View\View;

class RevenueController extends Controller
{
    public function index(): View
    {
        $projects = Project::with(['client', 'projectPayouts'])
            ->orderByDesc('created_at')
            ->get();

        $projectsData = $projects->map(function (Project $p) {
            return [
                'id' => $p->id,
                'project_name' => $p->project_name,
                'project_code' => $p->project_code ?? '',
                'client_name' => $p->client->name ?? '',
                'contract_date' => $p->contract_date?->format('Y-m-d'),
                'created_at' => $p->created_at->format('Y-m-d'),
                'contract_amount' => (float) $p->contract_amount,
                'expense_total' => $p->expense_total,
                'net_base' => $p->net_base,
                'overhead' => $p->overhead,
                'realized_overhead' => $p->realized_overhead,
                'sales' => $p->sales,
                'realized_sales' => $p->realized_sales,
                'developer' => $p->developer,
                'realized_developer' => $p->realized_developer,
                'profit' => $p->profit,
                'realized_profit' => $p->realized_profit,
                'due' => $p->due,
            ];
        })->values()->toArray();

        return view('revenue.index', compact('projects', 'projectsData'));
    }
}
