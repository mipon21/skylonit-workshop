<?php

namespace App\Mail;

use App\Models\Bug;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Schema;

class ClientBugReportedMail extends Mailable
{
    use Queueable, SerializesModels;

    public ?string $logoUrl = null;

    public string $mailSubject;

    public string $body;

    /** @var array<string, string|null> */
    public array $footer = [];

    public function __construct(Bug $bug, string $reporterName, string $reporterEmail)
    {
        $project = $bug->project;
        $projectName = e($project->project_name);
        $projectCode = e($project->project_code ?? $project->formatted_id);
        $title = e($bug->title);
        $severity = e(ucfirst($bug->severity ?? ''));
        $description = $bug->description ? e($bug->description) : '—';
        $projectUrl = route('projects.show', $project);

        $this->mailSubject = '[Bug Report] ' . $title . ' — ' . $projectName;

        $this->body = '<p>A client has reported a new bug from the client portal.</p>';
        $this->body .= '<p><strong>Project:</strong> ' . $projectName . ' (' . $projectCode . ')</p>';
        $this->body .= '<p><strong>Bug title:</strong> ' . $title . '</p>';
        $this->body .= '<p><strong>Severity:</strong> ' . $severity . '</p>';
        $this->body .= '<p><strong>Reported by:</strong> ' . e($reporterName) . ' &lt;' . e($reporterEmail) . '&gt;</p>';
        $this->body .= '<p><strong>Description:</strong></p><p style="white-space: pre-wrap;">' . $description . '</p>';
        $this->body .= '<p><a href="' . e($projectUrl) . '" style="color: #2563eb;">View project &raquo;</a></p>';

        $logoPath = Schema::hasTable('settings') ? Setting::get('app_logo') : null;
        $baseUrl = rtrim(config('app.url'), '/');
        if (config('app.env') === 'production' && str_starts_with($baseUrl, 'http://')) {
            $baseUrl = 'https://' . substr($baseUrl, 7);
        }
        if ($logoPath) {
            $this->logoUrl = $baseUrl . '/storage/' . ltrim($logoPath, '/');
        } elseif (config('app.logo')) {
            $path = ltrim(config('app.logo'), '/');
            $this->logoUrl = $path ? $baseUrl . '/' . $path : null;
        }

        $footerConfig = config('mail.footer', []);
        $this->footer = [
            'email' => Schema::hasTable('settings') ? (Setting::get('footer_email') ?? $footerConfig['email'] ?? null) : ($footerConfig['email'] ?? null),
            'phone' => Schema::hasTable('settings') ? (Setting::get('footer_phone') ?? $footerConfig['phone'] ?? null) : ($footerConfig['phone'] ?? null),
            'website' => Schema::hasTable('settings') ? (Setting::get('footer_website') ?? $footerConfig['website'] ?? null) : ($footerConfig['website'] ?? null),
            'facebook' => Schema::hasTable('settings') ? (Setting::get('footer_facebook') ?? $footerConfig['facebook'] ?? null) : ($footerConfig['facebook'] ?? null),
            'whatsapp' => Schema::hasTable('settings') ? (Setting::get('footer_whatsapp') ?? $footerConfig['whatsapp'] ?? null) : ($footerConfig['whatsapp'] ?? null),
            'tagline' => Schema::hasTable('settings') ? (Setting::get('footer_tagline') ?? $footerConfig['tagline'] ?? 'Thank you for staying with us.') : ($footerConfig['tagline'] ?? 'Thank you for staying with us.'),
        ];
        $wa = $this->footer['whatsapp'] ?? '';
        $this->footer['whatsapp_link'] = $wa !== '' ? 'https://api.whatsapp.com/send/?phone=' . preg_replace('/\D/', '', $wa) : null;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.template',
        );
    }
}
