<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\View\View;

class LeadController extends Controller
{
    /**
     * Admin: Marketing → Leads list.
     */
    public function index(): View
    {
        $leads = Lead::orderByDesc('created_at')->paginate(20);

        return view('leads.index', compact('leads'));
    }
}
