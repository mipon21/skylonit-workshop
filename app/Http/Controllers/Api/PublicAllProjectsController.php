<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;

class PublicAllProjectsController extends Controller
{
    /**
     * Public API: all public projects for use by external marketing site (e.g. skf).
     * Same data as Guest Portal project list – no client/payment info. For "All Projects" section.
     */
    public function __invoke(): JsonResponse
    {
        $projects = Project::where('is_public', true)
            ->withCount(['tasks', 'tasks as tasks_done_count' => fn ($q) => $q->where('status', 'done')])
            ->orderByRaw('CASE WHEN project_code IS NULL OR project_code = "" THEN 1 ELSE 0 END')
            ->orderByDesc('project_code')
            ->get();

        $data = $projects->map(function (Project $project) {
            $tasksTotal = $project->tasks_count ?? 0;
            $tasksDone = $project->tasks_done_count ?? 0;
            $progressPercent = $tasksTotal > 0 ? (int) round(($tasksDone / $tasksTotal) * 100) : 0;

            return [
                'id' => $project->id,
                'name' => $project->project_name,
                'code' => $project->project_code ?: $project->formatted_id,
                'project_type' => $project->project_type ?? null,
                'status' => $project->status ?? null,
                'progress_percent' => $progressPercent,
                'tasks_count' => $tasksTotal,
                'tasks_done_count' => $tasksDone,
                'start_date' => $project->contract_date?->format('M j, Y'),
                'delivery_date' => $project->delivery_date?->format('M j, Y'),
                'detail_url' => route('guest.projects.show', $project),
            ];
        });

        return response()->json([
            'data' => $data,
        ]);
    }
}
