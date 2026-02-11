<?php

namespace App\Observers;

use App\Mail\ProjectActivityNotificationMail;
use App\Models\ClientNotification;
use App\Models\ProjectActivity;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ProjectActivityObserver
{
    /** Action types that are handled by Payment/Invoice observers with payment_id/invoice_id. */
    private const PAYMENT_ACTION_TYPES = [
        'payment_created',
        'payment_marked_paid',
        'invoice_generated',
    ];

    public function created(ProjectActivity $activity): void
    {
        if ($activity->visibility === ProjectActivity::VISIBILITY_CLIENT) {
            if (! in_array($activity->action_type, self::PAYMENT_ACTION_TYPES, true)) {
                $this->createNotificationsForProjectClients($activity->project_id, [
                    'activity_id' => $activity->id,
                    'type' => ClientNotification::TYPE_NORMAL,
                    'title' => $this->titleFromDescription($activity->description),
                    'message' => $activity->description,
                ]);
            }
        }

        $recipients = config('mail.project_activity_notification_to', []);
        if (! empty($recipients) && is_array($recipients)) {
            $addresses = array_values(array_unique(array_filter(array_map(function ($e) {
                return is_string($e) ? trim($e) : '';
            }, $recipients))));
            if (! empty($addresses)) {
                try {
                    Mail::to($addresses)->queue(new ProjectActivityNotificationMail($activity));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }
    }

    private function titleFromDescription(string $description): string
    {
        if (strlen($description) <= 60) {
            return $description;
        }

        return substr($description, 0, 57) . '...';
    }

    /**
     * Create a ClientNotification for each client linked to the project (primary + additional).
     *
     * @param  array<string, mixed>  $attrs  Must include type, title, message; optional activity_id, project_id, payment_id, invoice_id, link
     */
    public static function createNotificationsForProjectClients(int $projectId, array $attrs): void
    {
        $project = \App\Models\Project::with('client', 'additionalClients')->find($projectId);
        if (! $project) {
            return;
        }

        $clientIds = collect([$project->client_id])
            ->merge($project->additionalClients->pluck('id'))
            ->filter()
            ->unique()
            ->values();

        // Ensure title/message are ASCII-safe to avoid charset errors (full content stays in project_notes)
        $safeTitle = Str::ascii($attrs['title'] ?? '') ?: 'Notification';
        $safeMessage = Str::ascii($attrs['message'] ?? '') ?: 'New project activity.';
        $base = array_merge($attrs, [
            'title' => $safeTitle,
            'message' => $safeMessage,
            'project_id' => $projectId,
            'is_read' => false,
        ]);

        foreach ($clientIds as $clientId) {
            try {
                ClientNotification::create(array_merge($base, ['client_id' => $clientId]));
            } catch (\Throwable $e) {
                report($e);
                // Don't rethrow: note/activity already saved; notification is supplementary
            }
        }
    }
}
