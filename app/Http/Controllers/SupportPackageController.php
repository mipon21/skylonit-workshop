<?php

namespace App\Http\Controllers;

use App\Jobs\SendTemplateMailJob;
use App\Models\InternalFundLedger;
use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\SupportPackage;
use App\Services\SupportInvoiceService;
use App\Services\UddoktaPayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupportPackageController extends Controller
{
    public function __construct(
        protected UddoktaPayService $uddoktaPay,
        protected SupportInvoiceService $supportInvoiceService
    ) {
    }

    /**
     * Create package form (project support tab).
     */
    public function create(Project $project): View
    {
        $this->ensureAdmin();
        $project->load('client');
        return view('support-packages.create', compact('project'));
    }

    /**
     * Store a new support package.
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->ensureAdmin();
        $client = $project->client;
        if (! $client) {
            return redirect()->route('projects.show', $project)->withFragment('support')
                ->with('error', 'Project has no client.');
        }

        $validated = $request->validate([
            'package_duration' => ['required', 'string', 'in:1,3,6,12'],
            'start_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'send_email' => ['nullable', 'boolean'],
        ]);

        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = SupportPackage::calculateEndDate($startDate, $validated['package_duration']);
        $monthsCount = (int) $validated['package_duration'];
        $packageLabel = SupportPackage::generatePackageLabel($validated['package_duration'], $startDate);

        $supportPackage = SupportPackage::create([
            'project_id' => $project->id,
            'client_id' => $client->id,
            'package_duration' => $validated['package_duration'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'months_count' => $monthsCount,
            'package_label' => $packageLabel,
            'amount' => $validated['amount'],
            'payment_status' => SupportPackage::PAYMENT_STATUS_DUE,
            'created_by' => Auth::id(),
        ]);

        ProjectActivity::log(
            $project->id,
            'support_package_created',
            "Support package created: {$packageLabel} – ৳" . number_format($supportPackage->amount, 0),
            ProjectActivity::VISIBILITY_CLIENT
        );

        $sendEmail = (bool) ($validated['send_email'] ?? false);
        if ($sendEmail) {
            $this->sendPackageCreatedEmail($supportPackage);
        }

        return redirect()->route('projects.show', $project)->withFragment('support')
            ->with('success', 'Support package created. Generate payment link to enable online payment.');
    }

    /**
     * Generate UddoktaPay payment link for a DUE support package.
     */
    public function generateLink(Project $project, SupportPackage $supportPackage): RedirectResponse
    {
        $this->ensureAdmin();
        if ($supportPackage->project_id !== $project->id) {
            abort(404);
        }
        if ($supportPackage->payment_status !== SupportPackage::PAYMENT_STATUS_DUE) {
            return redirect()->route('projects.show', $project)->withFragment('support')
                ->with('error', 'Only DUE support packages can have a payment link generated.');
        }
        if ($supportPackage->payment_link) {
            return redirect()->route('projects.show', $project)->withFragment('support')
                ->with('info', 'This package already has a payment link.');
        }
        if (! $this->uddoktaPay->isConfigured()) {
            return redirect()->route('projects.show', $project)->withFragment('support')
                ->with('error', 'UddoktaPay is not configured.');
        }

        $redirectUrl = route('client.payment.success');
        $cancelUrl = route('client.payment.cancel');
        $webhookUrl = url('/api/uddoktapay/webhook');

        $result = $this->uddoktaPay->createChargeForSupportPackage($supportPackage, $redirectUrl, $cancelUrl, $webhookUrl);

        if ($result['success']) {
            $supportPackage->update([
                'payment_link' => $result['payment_url'],
                'gateway_invoice_id' => $result['invoice_id'] ?? null,
            ]);
            return redirect()->route('projects.show', $project)->withFragment('support')
                ->with('success', 'Payment link generated. Use "Send Email" to notify the client.');
        }

        return redirect()->route('projects.show', $project)->withFragment('support')
            ->with('error', 'Could not generate link: ' . ($result['message'] ?? 'Unknown error.'));
    }

    /**
     * Mark support package as paid (manual/cash).
     */
    public function markAsPaid(Project $project, SupportPackage $supportPackage): RedirectResponse
    {
        $this->ensureAdmin();
        if ($supportPackage->project_id !== $project->id) {
            abort(404);
        }
        if ($supportPackage->payment_status !== SupportPackage::PAYMENT_STATUS_DUE) {
            return redirect()->route('projects.show', $project)->withFragment('support')
                ->with('error', 'Only DUE packages can be marked as paid.');
        }

        $supportPackage->update([
            'payment_status' => SupportPackage::PAYMENT_STATUS_PAID,
            'paid_at' => now(),
        ]);

        $this->supportInvoiceService->generateInvoice($supportPackage->fresh());

        ProjectActivity::log(
            $project->id,
            'support_payment_completed',
            "Support package paid: {$supportPackage->package_label} – ৳" . number_format($supportPackage->amount, 0),
            ProjectActivity::VISIBILITY_CLIENT
        );

        event(new \App\Events\SupportPackagePaymentSuccess($supportPackage->fresh()));

        return redirect()->route('projects.show', $project)->withFragment('support')
            ->with('success', 'Support package marked as paid. Invoice generated.');
    }

    /**
     * Send payment link email to client.
     */
    public function sendPaymentLinkEmail(Project $project, SupportPackage $supportPackage): RedirectResponse
    {
        $this->ensureAdmin();
        if ($supportPackage->project_id !== $project->id) {
            abort(404);
        }
        if ($supportPackage->payment_status !== SupportPackage::PAYMENT_STATUS_DUE || ! $supportPackage->payment_link) {
            return redirect()->route('projects.show', $project)->withFragment('support')
                ->with('error', 'Can only send email for DUE packages that have a payment link.');
        }

        $supportPackage->load(['project.client', 'client']);
        $client = $supportPackage->client;
        $email = $client->user?->email ?? $client->email;
        if (! $email) {
            return redirect()->route('projects.show', $project)->withFragment('support')
                ->with('error', 'Client has no email address.');
        }

        SendTemplateMailJob::dispatch(
            'client_support_package_created',
            $email,
            [
                'client_name' => $client->name,
                'client_email' => $email,
                'project_name' => $supportPackage->project->project_name,
                'support_package_label' => $supportPackage->package_label,
                'payment_amount' => number_format($supportPackage->amount, 2),
                'payment_link' => $supportPackage->payment_link,
                'login_url' => route('login'),
            ]
        );

        return redirect()->route('projects.show', $project)->withFragment('support')
            ->with('success', 'Support package payment link email queued.');
    }

    /**
     * Download support package invoice.
     */
    public function downloadInvoice(Request $request, Project $project, SupportPackage $supportPackage)
    {
        if ($supportPackage->project_id !== $project->id) {
            abort(404);
        }

        $user = $request->user();
        if ($user->isClient()) {
            $client = $user->client;
            if (! $client || ! $project->hasClientAccess($client->id)) {
                abort(403);
            }
        } elseif ($user->isDeveloper() || $user->isSales()) {
            abort(403, 'Support invoices are not visible to developers or sales.');
        }

        if (! $supportPackage->isPaid() || ! $supportPackage->invoice_path) {
            abort(404, 'Invoice not yet generated.');
        }

        $path = $supportPackage->invoice_path;
        if (! \Illuminate\Support\Facades\Storage::exists($path)) {
            $this->supportInvoiceService->generateInvoice($supportPackage);
            $path = $supportPackage->fresh()->invoice_path;
        }

        $fileContents = \Illuminate\Support\Facades\Storage::get($path);
        $filename = sprintf('Support_Invoice_%s.pdf', $supportPackage->invoice_number);

        return response($fileContents, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', sprintf('attachment; filename="%s"', $filename));
    }

    /**
     * View support package invoice in browser.
     */
    public function viewInvoice(Request $request, Project $project, SupportPackage $supportPackage)
    {
        if ($supportPackage->project_id !== $project->id) {
            abort(404);
        }

        $user = $request->user();
        if ($user->isClient()) {
            $client = $user->client;
            if (! $client || ! $project->hasClientAccess($client->id)) {
                abort(403);
            }
        } elseif ($user->isDeveloper() || $user->isSales()) {
            abort(403, 'Support invoices are not visible to developers or sales.');
        }

        if (! $supportPackage->isPaid() || ! $supportPackage->invoice_path) {
            abort(404, 'Invoice not yet generated.');
        }

        $path = $supportPackage->invoice_path;
        if (! \Illuminate\Support\Facades\Storage::exists($path)) {
            $this->supportInvoiceService->generateInvoice($supportPackage);
            $path = $supportPackage->fresh()->invoice_path;
        }

        $fileContents = \Illuminate\Support\Facades\Storage::get($path);

        return response($fileContents, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline');
    }

    /**
     * Mark support package share as cleared (admin only, internal).
     */
    public function markShareCleared(Project $project, SupportPackage $supportPackage): RedirectResponse
    {
        $this->ensureAdmin();
        if ($supportPackage->project_id !== $project->id) {
            abort(404);
        }
        if (! $supportPackage->isPaid()) {
            return redirect()->route('projects.show', $project)->withFragment('support')
                ->with('error', 'Only paid support packages can have share cleared.');
        }
        if ($supportPackage->isShareCleared()) {
            return redirect()->route('projects.show', $project)->withFragment('support')
                ->with('info', 'Share already cleared for this package.');
        }

        $supportPackage->update(['share_cleared_at' => now()]);

        // Add cleared share amount to company overhead
        InternalFundLedger::create([
            'fund_type' => InternalFundLedger::FUND_OVERHEAD,
            'reference_type' => InternalFundLedger::REFERENCE_SUPPORT_PACKAGE_SHARE,
            'reference_id' => $supportPackage->id,
            'amount' => $supportPackage->amount,
            'direction' => InternalFundLedger::DIRECTION_CREDIT,
        ]);

        return redirect()->route('projects.show', $project)->withFragment('support')
            ->with('success', 'Share marked as cleared. Amount added to company overhead.');
    }

    /**
     * Delete a support package (admin only).
     * If share was cleared to overhead, remove that ledger entry so Support to Overhead stays correct.
     */
    public function destroy(Project $project, SupportPackage $supportPackage): RedirectResponse
    {
        $this->ensureAdmin();
        if ($supportPackage->project_id !== $project->id) {
            abort(404);
        }

        // Remove overhead credit for this package so "Support to Overhead" dashboard total is correct
        InternalFundLedger::where('reference_type', InternalFundLedger::REFERENCE_SUPPORT_PACKAGE_SHARE)
            ->where('reference_id', $supportPackage->id)
            ->delete();

        $supportPackage->delete();

        return redirect()->route('projects.show', $project)->withFragment('support')
            ->with('success', 'Support package deleted.');
    }

    protected function ensureAdmin(): void
    {
        $user = Auth::user();
        if ($user->isDeveloper() || $user->isSales()) {
            abort(403, 'Support packages are admin-only.');
        }
    }

    protected function sendPackageCreatedEmail(SupportPackage $supportPackage): void
    {
        $client = $supportPackage->client;
        $email = $client->user?->email ?? $client->email;
        if (! $email) {
            return;
        }
        SendTemplateMailJob::dispatch(
            'client_support_package_created',
            $email,
            [
                'client_name' => $client->name,
                'client_email' => $email,
                'project_name' => $supportPackage->project->project_name,
                'support_package_label' => $supportPackage->package_label,
                'payment_amount' => number_format($supportPackage->amount, 2),
                'payment_link' => $supportPackage->payment_link ?? route('login'),
                'login_url' => route('login'),
            ]
        );
    }
}
