<?php

namespace App\Mail;

use App\Models\ProjectActivity;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Schema;

class ProjectActivityNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public ?string $logoUrl = null;

    public string $mailSubject;

    public string $body;

    /** @var array<string, string|null> */
    public array $footer = [];

    public function __construct(ProjectActivity $activity)
    {
        $activity->loadMissing(['project', 'user']);
        $project = $activity->project;
        if (! $project) {
            $this->mailSubject = '[Project Activity] Unknown project';
            $this->body = '<p>An activity was recorded but the project could not be loaded.</p>';
            $this->setFooter();
            return;
        }

        $projectName = e($project->project_name);
        $projectCode = e($project->project_code ?? $project->formatted_id ?? '');
        $description = e($activity->description);
        $actorName = e($activity->actor_name);
        $actionLabel = $this->actionTypeLabel($activity->action_type);
        $projectUrl = route('projects.show', $project);

        $this->mailSubject = '[Project Activity] ' . $actionLabel . ' — ' . $projectName;

        $this->body = '<p>A project activity has been recorded.</p>';
        $this->body .= '<p><strong>Project:</strong> ' . $projectName . ($projectCode !== '' ? ' (' . $projectCode . ')' : '') . '</p>';
        $this->body .= '<p><strong>Activity:</strong> ' . $actionLabel . '</p>';
        $this->body .= '<p><strong>Description:</strong> ' . $description . '</p>';
        $this->body .= '<p><strong>Actor:</strong> ' . $actorName . '</p>';
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

        $this->setFooter();
    }

    private function setFooter(): void
    {
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

    private function actionTypeLabel(string $actionType): string
    {
        return match ($actionType) {
            'payment_created' => 'Payment created',
            'payment_marked_paid' => 'Payment marked paid',
            'task_created' => 'Task created',
            'task_status_changed' => 'Task status changed',
            'bug_created' => 'Bug created',
            'bug_status_changed' => 'Bug status changed',
            'document_uploaded' => 'Document uploaded',
            'document_deleted' => 'Document deleted',
            'note_created' => 'Note created',
            'note_updated' => 'Note updated',
            'link_created' => 'Link created',
            'link_updated' => 'Link updated',
            'expense_created' => 'Expense created',
            'project_created' => 'Project created',
            'project_status_changed' => 'Project status changed',
            'invoice_generated' => 'Invoice generated',
            'contract_uploaded' => 'Contract uploaded',
            'contract_viewed' => 'Contract viewed',
            'contract_signed' => 'Contract signed',
            default => 'Activity',
        };
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
