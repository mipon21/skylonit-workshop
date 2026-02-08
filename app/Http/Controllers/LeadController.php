<?php

namespace App\Http\Controllers;

use App\Exports\LeadsExport;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LeadController extends Controller
{
    /**
     * Admin: Marketing → Leads list.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search', '');
        $query = Lead::orderByDesc('created_at');

        if ($search !== '') {
            $term = '%' . preg_replace('/\s+/', '%', trim($search)) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('interested_project_type', 'like', $term)
                    ->orWhere('message', 'like', $term)
                    ->orWhere('id', 'like', $term);
            });
        }

        $leads = $query->paginate(20)->withQueryString();

        return view('leads.index', compact('leads', 'search'));
    }

    /**
     * Export leads as CSV.
     */
    public function export()
    {
        $leads = Lead::orderByDesc('created_at')->get();
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="leads-' . now()->format('Y-m-d-His') . '.csv"',
        ];
        $callback = function () use ($leads) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Email', 'Phone', 'Interested type', 'Message', 'Status', 'Date']);
            foreach ($leads as $lead) {
                fputcsv($out, [
                    $lead->name,
                    $lead->email,
                    $lead->phone ?? '',
                    $lead->interested_project_type ?? '',
                    $lead->message ?? '',
                    Lead::statusLabel($lead->status ?? 'new'),
                    $lead->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($out);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export leads as Excel (.xlsx).
     */
    public function exportExcel(): BinaryFileResponse
    {
        $filename = 'leads-' . now()->format('Y-m-d-His') . '.xlsx';

        return Excel::download(new LeadsExport, $filename, \Maatwebsite\Excel\Excel::XLSX);
    }

    /**
     * Update lead (status) and optionally return for modal refresh.
     */
    public function update(Request $request, Lead $lead): JsonResponse|RedirectResponse
    {
        $request->validate(['status' => 'required|in:new,contacted,closed']);

        $lead->update(['status' => $request->status]);

        if ($request->wantsJson()) {
            return response()->json(['status' => $lead->status, 'label' => Lead::statusLabel($lead->status)]);
        }

        return back()->with('success', 'Lead status updated.');
    }
}
