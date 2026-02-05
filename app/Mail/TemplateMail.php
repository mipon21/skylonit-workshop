<?php

namespace App\Mail;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Schema;

class TemplateMail extends Mailable
{
    use Queueable, SerializesModels;

    /** Absolute URL for the app logo (shown at top of every email). */
    public ?string $logoUrl = null;

    /** Subject line (for envelope and for view). */
    public $mailSubject;

    /** @var string */
    public $body;

    /** @var string|null */
    public $attachmentPath;

    /** @var string|null */
    public $attachmentName;

    /** @var array{email:string|null,phone:string|null,website:string|null,facebook:string|null,whatsapp_link:string|null,tagline:string} */
    public array $footer = [];

    public function __construct(
        string $subject,
        string $body,
        ?string $attachmentPath = null,
        ?string $attachmentName = null
    ) {
        $this->mailSubject = $subject;
        $this->body = $body;
        $this->attachmentPath = $attachmentPath;
        $this->attachmentName = $attachmentName;

        $logoPath = Schema::hasTable('settings')
            ? Setting::get('app_logo')
            : null;

        // Use absolute, publicly reachable URL so the logo works in emails (no relative/localhost).
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

        // Footer: use Setting if available, else config
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

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (! $this->attachmentPath || ! is_file($this->attachmentPath)) {
            return [];
        }
        return [
            Attachment::fromPath($this->attachmentPath)
                ->as($this->attachmentName ?? basename($this->attachmentPath)),
        ];
    }
}
