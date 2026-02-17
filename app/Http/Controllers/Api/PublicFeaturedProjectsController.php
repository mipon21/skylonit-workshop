<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;

class PublicFeaturedProjectsController extends Controller
{
    /**
     * Public API: featured projects for use by external marketing site (e.g. skf).
     * Same logic as Guest Portal featured carousel: is_public + is_featured.
     * Returns JSON with full image URLs so the consumer can display from another domain.
     */
    public function __invoke(): JsonResponse
    {
        $projects = Project::where('is_public', true)
            ->where('is_featured', true)
            ->orderByDesc('updated_at')
            ->limit(12)
            ->get();

        $data = $projects->map(function (Project $project) {
            $path = $project->featured_image_path ? trim($project->featured_image_path) : null;
            $isExternal = $path && (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'));
            $featuredImageUrl = $path
                ? ($isExternal ? $path : url($path))
                : null;

            $techStack = $project->tech_stack
                ? array_values(array_filter(array_map('trim', explode(',', $project->tech_stack))))
                : [];

            return [
                'id' => $project->id,
                'name' => $project->project_name,
                'short_description' => $project->short_description ?: null,
                'featured_image_url' => $featuredImageUrl,
                'tech_stack' => $techStack,
                'project_type' => $project->project_type ?? null,
                'detail_url' => route('guest.projects.show', $project),
            ];
        });

        return response()->json([
            'data' => $data,
        ]);
    }
}
