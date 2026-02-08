<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\HotOffer;
use App\Models\Project;
use App\Models\Testimonial;
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

        $featuredProjects = Project::where('is_public', true)
            ->where('is_featured', true)
            ->orderByDesc('updated_at')
            ->limit(12)
            ->get();

        $hotOffers = HotOffer::active()->orderBy('id')->get();
        $testimonials = Testimonial::active()->orderBy('id')->get();

        return view('guest.dashboard', compact(
            'totalPublicProjects',
            'runningPublicProjects',
            'featuredProjects',
            'hotOffers',
            'testimonials'
        ));
    }
}
