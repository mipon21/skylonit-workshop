<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    /**
     * Display list of invoices (Client Portal)
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Get client's projects
        $client = $user->client;

        if (!$client) {
            abort(403, 'You must be associated with a client account.');
        }

        // Get all invoices for client's projects
        $invoices = Invoice::whereHas('project', function ($query) use ($client) {
            $query->where('client_id', $client->id);
        })
            ->with(['project', 'payment'])
            ->orderBy('invoice_date', 'desc')
            ->paginate(20);

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
            if (!$client || $invoice->project->client_id !== $client->id) {
                abort(403, 'Unauthorized access to this invoice.');
            }
        }

        // Check if file exists
        if (!$invoice->file_path || !Storage::exists($invoice->file_path)) {
            abort(404, 'Invoice file not found.');
        }

        // Get file contents
        $fileContents = Storage::get($invoice->file_path);
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
            if (!$client || $invoice->project->client_id !== $client->id) {
                abort(403, 'Unauthorized access to this invoice.');
            }
        }

        // Check if file exists
        if (!$invoice->file_path || !Storage::exists($invoice->file_path)) {
            abort(404, 'Invoice file not found.');
        }

        // Get file contents
        $fileContents = Storage::get($invoice->file_path);

        return response($fileContents, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline');
    }
}
