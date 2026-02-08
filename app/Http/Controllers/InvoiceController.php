<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Project;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    /**
     * Display list of invoices. Admin: all; Client: own project invoices only.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Invoice::with(['project.client', 'payment'])->orderBy('invoice_date', 'desc');

        if ($user->isClient()) {
            $client = $user->client;
            if (! $client) {
                abort(403, 'You must be associated with a client account.');
            }
            $query->whereHas('project', fn ($q) => $q->forClient($client->id));
        }

        $search = $request->input('search');
        if ($search && is_string($search)) {
            $term = trim($search);
            if ($term !== '') {
                $query->where(function ($q) use ($term) {
                    $q->where('invoice_number', 'like', '%' . $term . '%')
                        ->orWhereHas('project', function ($p) use ($term) {
                            $p->where('project_code', 'like', '%' . $term . '%')
                                ->orWhere('project_name', 'like', '%' . $term . '%')
                                ->orWhereHas('client', fn ($c) => $c->where('name', 'like', '%' . $term . '%'));
                        });
                });
            }
        }

        $invoices = $query->paginate(20)->withQueryString();

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('invoices.partials.list', compact('invoices'));
        }

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Download invoice PDF
     */
    public function download(Request $request, Invoice $invoice): Response
    {
        $user = $request->user();

        // Security check: Admin can download any invoice, Client can only download their own
        if ($user->role === 'client') {
            $client = $user->client;
            if (!$client || !$invoice->project->hasClientAccess($client->id)) {
                abort(403, 'Unauthorized access to this invoice.');
            }
        }

        // Regenerate PDF if missing (e.g. after storage:clear-uploads)
        if (!$invoice->file_path || !Storage::disk('local')->exists($invoice->file_path)) {
            $invoice = app(InvoiceService::class)->regenerateInvoice($invoice);
        }

        $fileContents = Storage::disk('local')->get($invoice->file_path);
        $filename = sprintf('Invoice_%s.pdf', $invoice->invoice_number);

        return response($fileContents, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', sprintf('attachment; filename="%s"', $filename));
    }

    /**
     * View invoice in browser
     */
    public function view(Request $request, Invoice $invoice): Response
    {
        $user = $request->user();

        // Security check: Admin can view any invoice, Client can only view their own
        if ($user->role === 'client') {
            $client = $user->client;
            if (!$client || !$invoice->project->hasClientAccess($client->id)) {
                abort(403, 'Unauthorized access to this invoice.');
            }
        }

        // Regenerate PDF if missing (e.g. after storage:clear-uploads)
        if (!$invoice->file_path || !Storage::disk('local')->exists($invoice->file_path)) {
            $invoice = app(InvoiceService::class)->regenerateInvoice($invoice);
        }

        $fileContents = Storage::disk('local')->get($invoice->file_path);

        return response($fileContents, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline');
    }
}
