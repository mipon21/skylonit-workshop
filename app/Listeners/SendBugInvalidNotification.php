<?php

namespace App\Listeners;

use App\Events\BugValidityUpdated;
use App\Jobs\SendTemplateMailJob;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendBugInvalidNotification implements ShouldQueue
{
    public function handle(BugValidityUpdated $event): void
    {
        if ($event->newIsValid || ! $event->oldIsValid) {
            return;
        }

        $updater = $event->updatedByUserId ? User::find($event->updatedByUserId) : null;
        if (! $updater || ! $updater->isAdmin()) {
            return;
        }

        $bug = $event->bug->load(['project.client']);
        $project = $bug->project;
        $client = $project->client;
        if (! $client) {
            return;
        }

        $email = $client->user?->email ?? $client->email;
        if (! $email) {
            return;
        }

        SendTemplateMailJob::dispatch(
            'client_bug_invalid',
            $email,
            [
                'client_name' => $client->name,
                'client_email' => $email,
                'project_name' => $project->project_name,
                'project_code' => $project->project_code ?? '',
                'bug_title' => $bug->title,
                'invalid_note' => $bug->invalid_note ?: 'No note was provided.',
                'login_url' => route('login'),
            ],
            $bug->invalid_attachment_path,
            $bug->invalid_attachment_path ? basename($bug->invalid_attachment_path) : null
        );
    }
}
