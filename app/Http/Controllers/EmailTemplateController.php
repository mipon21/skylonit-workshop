<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailTemplateController extends Controller
{
    public function index(): View
    {
        $templates = EmailTemplate::orderBy('name')->get();
        return view('email-templates.index', compact('templates'));
    }

    public function edit(EmailTemplate $email_template): View
    {
        $placeholders = EmailTemplate::availablePlaceholders();
        $placeholderExamples = implode(', ', array_map(fn (string $p) => '{{' . $p . '}}', $placeholders));
        return view('email-templates.edit', [
            'template' => $email_template,
            'placeholders' => $placeholders,
            'placeholderExamples' => $placeholderExamples,
            'placeholderExampleShort' => '{{placeholder}}',
        ]);
    }

    /**
     * Preview the template with sample data. No email is sent.
     * Renders the full email layout (logo, body, footer) so it matches what recipients see.
     */
    public function preview(EmailTemplate $email_template): View
    {
        $sampleData = [
            'client_name' => 'Sample Client',
            'client_email' => 'client@example.com',
            'project_name' => 'Sample Project',
            'project_code' => 'PRJ-001',
            'payment_amount' => '5,000.00',
            'payment_link' => url('/client/payments'),
            'invoice_link' => url('/invoices'),
            'document_name' => 'Sample Document.pdf',
            'expense_amount' => '1,200.00',
            'note_title' => 'Sample Note',
            'link_url' => 'https://example.com',
            'task_title' => 'Sample Task',
            'bug_title' => 'Sample Bug',
            'login_url' => route('login'),
            'client_password' => '••••••••',
        ];
        $service = app(\App\Services\TemplateMailService::class);
        $rendered = $service->replacePlaceholders($email_template->subject, $email_template->body, $sampleData);

        $mailable = new \App\Mail\TemplateMail($rendered['subject'], $rendered['body']);
        $html = $mailable->render();

        return view('email-templates.preview', [
            'template' => $email_template,
            'subject' => $rendered['subject'],
            'body' => $rendered['body'],
            'fullEmailHtml' => $html,
        ]);
    }

    public function update(Request $request, EmailTemplate $email_template): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active');

        $email_template->update($validated);

        return redirect()->route('email-templates.index')
            ->with('success', 'Email template updated.');
    }
}
