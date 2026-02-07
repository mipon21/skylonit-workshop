<?php

namespace App\Observers;

use App\Models\ClientNotification;
use App\Models\ProjectActivity;

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
        if ($activity->visibility !== ProjectActivity::VISIBILITY_CLIENT) {
            return;
        }

        if (in_array($activity->action_type, self::PAYMENT_ACTION_TYPES, true)) {
            return;
        }

        $this->createNotificationsForProjectClients($activity->project_id, [
            'activity_id' => $activity->id,
            'type' => ClientNotification::TYPE_NORMAL,
            'title' => $this->titleFromDescription($activity->description),
            'message' => $activity->description,
        ]);
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

        $base = array_merge($attrs, [
            'project_id' => $projectId,
            'is_read' => false,
        ]);

        foreach ($clientIds as $clientId) {
            ClientNotification::create(array_merge($base, ['client_id' => $clientId]));
        }
    }
}
