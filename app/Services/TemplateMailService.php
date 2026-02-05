<?php

namespace App\Services;

use App\Models\EmailTemplate;

class TemplateMailService
{
    /**
     * Get template by key. Returns null if not found or inactive.
     */
    public function getActiveTemplate(string $key): ?EmailTemplate
    {
        $template = EmailTemplate::where('key', $key)->first();

        return $template && $template->is_active ? $template : null;
    }

    /**
     * Replace {{placeholder}} in subject and body with values from $data.
     * Keys in $data should match placeholder names (e.g. client_name, payment_amount).
     */
    public function replacePlaceholders(string $subject, string $body, array $data): array
    {
        $replacer = function ($text) use ($data) {
            foreach ($data as $key => $value) {
                $text = str_replace('{{' . $key . '}}', (string) $value, $text);
            }
            return $text;
        };

        return [
            'subject' => $replacer($subject),
            'body' => $replacer($body),
        ];
    }

    /**
     * Get rendered subject and body for a template key with given data.
     * Returns null if template not found or inactive.
     */
    public function renderTemplate(string $key, array $data): ?array
    {
        $template = $this->getActiveTemplate($key);
        if (! $template) {
            return null;
        }

        return $this->replacePlaceholders($template->subject, $template->body, $data);
    }
}
