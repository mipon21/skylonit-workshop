<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;

/**
 * Public API: company stats for marketing site (skf).
 * Projects Delivered, Years Experience, Happy Clients, Products Live –
 * driven by mini erp data; years auto-increment from founded year.
 */
class PublicStatsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $foundedYear = (int) Config::get('company.founded_year', date('Y') - 5);
        $currentYear = (int) date('Y');
        $yearsExperience = max(0, $currentYear - $foundedYear);

        return response()->json([
            'data' => [
                'projects_delivered' => Project::count(),
                'years_experience' => $yearsExperience,
                'happy_clients' => Client::count(),
                'products_live' => Project::where('status', 'Complete')->count(),
            ],
        ]);
    }
}
