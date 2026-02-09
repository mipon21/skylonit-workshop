<?php

namespace App\Http\Controllers;

use App\Events\BugAssigned;
use App\Events\BugStatusUpdated;
use App\Mail\ClientBugReportedMail;
use App\Models\Bug;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BugController extends Controller
{
    private function authorizeProjectForClient(Project $project): void
    {
        if (Auth::user()->isClient() && (! Auth::user()->client || ! $project->hasClientAccess(Auth::user()->client->id))) {
            abort(403, 'You do not have access to this project.');
        }
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeProjectForClient($project);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'severity' => ['required', 'in:minor,major,critical'],
            'status' => ['required', 'in:open,in_progress,resolved'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,png,jpg,jpeg,zip,txt', 'max:10240'],
            'is_public' => ['nullable', 'boolean'],
            'assigned_to_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('bug-attachments/' . $project->id, 'local');
            $validated['attachment_path'] = $path;
        }
        unset($validated['attachment']);
        $validated['is_public'] = $request->boolean('is_public', true);
        $validated['assigned_to_user_id'] = $request->input('assigned_to_user_id') ?: null;
        if (in_array($validated['status'] ?? 'open', ['in_progress', 'resolved'], true)) {
            $validated['status_updated_at'] = now();
        }

        $bug = $project->bugs()->create($validated);
        $bug->setRelation('project', $project);

        // When admin creates a bug with assignee, notify the assigned developer (client cannot assign)
        if (Auth::user()->isAdmin() && $bug->assigned_to_user_id) {
            $assignee = User::find($bug->assigned_to_user_id);
            if ($assignee && $assignee->isDeveloper()) {
                event(new BugAssigned($bug->load('project'), $assignee));
            }
        }

        // When a client reports a bug, send notification to admin users and SMTP-configured inbox
        if (Auth::user()->isClient()) {
            $reporterName = Auth::user()->name ?? Auth::user()->client?->name ?? 'Client';
            $reporterEmail = Auth::user()->email ?? Auth::user()->client?->email ?? '';
            $recipients = User::where('role', 'admin')->pluck('email')->filter()->unique()->values()->all();
            $fromAddress = config('mail.from.address');
            if (! empty($fromAddress) && is_string($fromAddress)) {
                $fromAddress = trim($fromAddress);
                if ($fromAddress !== '' && ! in_array($fromAddress, $recipients, true)) {
                    $recipients[] = $fromAddress;
                }
            }
            $bugNotificationTo = config('mail.bug_notification_to', []);
            if (! empty($bugNotificationTo) && is_array($bugNotificationTo)) {
                foreach ($bugNotificationTo as $addr) {
                    if (is_string($addr) && trim($addr) !== '' && ! in_array($addr, $recipients, true)) {
                        $recipients[] = trim($addr);
                    }
                }
            }
            $projectActivityNotificationTo = config('mail.project_activity_notification_to', []);
            if (! empty($projectActivityNotificationTo) && is_array($projectActivityNotificationTo)) {
                foreach ($projectActivityNotificationTo as $addr) {
                    if (is_string($addr) && trim($addr) !== '' && ! in_array($addr, $recipients, true)) {
                        $recipients[] = trim($addr);
                    }
                }
            }
            $recipients = array_values(array_unique(array_filter(array_map(function ($e) {
                return is_string($e) ? trim($e) : '';
            }, $recipients))));
            if (empty($recipients) && ! empty($fromAddress)) {
                $recipients = [trim($fromAddress)];
            }
            if (! empty($recipients)) {
                try {
                    Mail::to($recipients)->send(new ClientBugReportedMail($bug, $reporterName, $reporterEmail));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        return redirect()->route('projects.show', $project)->withFragment('bugs')->with('success', 'Bug report added.');
    }

    public function downloadAttachment(Project $project, Bug $bug): StreamedResponse
    {
        $this->authorizeProjectForClient($project);
        if ($bug->project_id !== $project->id || ! $bug->attachment_path) {
            abort(404);
        }
        $name = basename($bug->attachment_path);
        return Storage::download($bug->attachment_path, $name);
    }

    public function update(Request $request, Project $project, Bug $bug): RedirectResponse
    {
        if ($bug->project_id !== $project->id) {
            abort(404);
        }
        $user = Auth::user();
        $isDeveloper = $user->isDeveloper();
        if ($isDeveloper) {
            if ($bug->assigned_to_user_id !== $user->id) {
                abort(403, 'You can only update bugs assigned to you.');
            }
            $validated = $request->validate([
                'status' => ['required', 'in:open,in_progress,resolved'],
            ]);
            $oldStatus = $bug->status;
            $newStatus = $validated['status'];
            $validated['status_updated_at'] = in_array($newStatus, ['in_progress', 'resolved'], true) ? now() : $bug->status_updated_at;
            $bug->update($validated);
            event(new BugStatusUpdated($bug->fresh(), false, $oldStatus, $newStatus, $user->id));
            return redirect()->route('projects.show', $project)->withFragment('bugs')->with('success', 'Bug status updated.');
        }
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'severity' => ['sometimes', 'required', 'in:minor,major,critical'],
            'status' => ['sometimes', 'required', 'in:open,in_progress,resolved'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,png,jpg,jpeg,zip,txt', 'max:10240'],
            'remove_attachment' => ['nullable', 'boolean'],
            'is_public' => ['nullable', 'boolean'],
            'send_email' => ['nullable', 'boolean'],
            'assigned_to_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);
        $oldStatus = $bug->status;
        $newStatus = $validated['status'] ?? $oldStatus;
        if ($newStatus !== $oldStatus && in_array($newStatus, ['in_progress', 'resolved'], true)) {
            $validated['status_updated_at'] = now();
        }
        if ($request->boolean('remove_attachment') || $request->hasFile('attachment')) {
            if ($bug->attachment_path) {
                Storage::delete($bug->attachment_path);
            }
            $validated['attachment_path'] = null;
        }
        if ($request->hasFile('attachment')) {
            $validated['attachment_path'] = $request->file('attachment')->store('bug-attachments/' . $project->id, 'local');
        }
        unset($validated['attachment'], $validated['remove_attachment'], $validated['send_email']);
        if (array_key_exists('is_public', $validated)) {
            $validated['is_public'] = $request->boolean('is_public');
        }
        $previousAssigneeId = $bug->assigned_to_user_id;
        $validated['assigned_to_user_id'] = $request->input('assigned_to_user_id') ?: null;
        $bug->update($validated);
        $newStatus = $bug->fresh()->status ?? $newStatus;
        event(new BugStatusUpdated($bug->fresh(), $request->boolean('send_email'), $oldStatus, $newStatus, Auth::id()));

        $newAssigneeId = $bug->fresh()->assigned_to_user_id;
        if ($newAssigneeId && $newAssigneeId !== $previousAssigneeId) {
            $assignee = User::find($newAssigneeId);
            if ($assignee && $assignee->isDeveloper()) {
                event(new BugAssigned($bug->fresh()->load('project'), $assignee));
            }
        }

        return redirect()->route('projects.show', $project)->withFragment('bugs')->with('success', 'Bug updated.');
    }

    public function destroy(Project $project, Bug $bug): RedirectResponse
    {
        if ($bug->project_id !== $project->id) {
            abort(404);
        }
        if (Auth::user()->isDeveloper() || Auth::user()->isSales()) {
            abort(403, 'Only admin can delete bugs.');
        }
        if ($bug->attachment_path) {
            Storage::delete($bug->attachment_path);
        }
        $bug->delete();
        return redirect()->route('projects.show', $project)->withFragment('bugs')->with('success', 'Bug deleted.');
    }
}
