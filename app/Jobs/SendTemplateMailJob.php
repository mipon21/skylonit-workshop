<?php

namespace App\Jobs;

use App\Mail\TemplateMail;
use App\Services\TemplateMailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendTemplateMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $templateKey,
        public string $toEmail,
        public array $placeholderData,
        public ?string $attachmentStoragePath = null,
        public ?string $attachmentDisplayName = null
    ) {
    }

    public function handle(TemplateMailService $templateMail): void
    {
        $rendered = $templateMail->renderTemplate($this->templateKey, $this->placeholderData);
        if (! $rendered) {
            \Illuminate\Support\Facades\Log::warning('Email template not found or inactive', [
                'template' => $this->templateKey,
                'to' => $this->toEmail,
            ]);
            return;
        }

        $attachmentPath = null;
        if ($this->attachmentStoragePath && Storage::disk('local')->exists($this->attachmentStoragePath)) {
            $attachmentPath = Storage::disk('local')->path($this->attachmentStoragePath);
        }

        $mailable = new TemplateMail(
            subject: $rendered['subject'],
            body: $rendered['body'],
            attachmentPath: $attachmentPath,
            attachmentName: $this->attachmentDisplayName
        );

        Mail::to($this->toEmail)->send($mailable);
    }
}
