<?php

namespace App\Http\Controllers;

use App\Events\LinkCreated;
use App\Models\Project;
use App\Models\ProjectLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectLinkController extends Controller
{
    private static function applyVisibilityToValidated(array &$validated, string $visibility): void
    {
        switch ($visibility) {
            case ProjectLink::VISIBILITY_ADMIN_ONLY:
                $validated['visible_to_client'] = false;
                $validated['is_public'] = false;
                break;
            case ProjectLink::VISIBILITY_CLIENT:
                $validated['visible_to_client'] = true;
                $validated['is_public'] = false;
                break;
            case ProjectLink::VISIBILITY_GUEST:
                $validated['visible_to_client'] = false;
                $validated['is_public'] = true;
                break;
            case ProjectLink::VISIBILITY_ALL:
            default:
                $validated['visible_to_client'] = true;
                $validated['is_public'] = true;
                break;
        }
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'link_type' => ['required', 'in:url,apk'],
            'label' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:2048'],
            'apk_file' => ['nullable', 'file', 'max:512000'], // 500MB, accept .apk
            'login_username' => ['nullable', 'string', 'max:255'],
            'login_password' => ['nullable', 'string', 'max:255'],
            'visibility' => ['required', 'in:admin_only,client,guest,all'],
            'send_email' => ['nullable', 'boolean'],
        ]);
        if (($validated['link_type'] ?? 'url') === ProjectLink::TYPE_URL && ! filter_var($validated['url'] ?? '', FILTER_VALIDATE_URL)) {
            return redirect()->back()->withInput()->withErrors(['url' => 'A valid URL is required for link type.']);
        }

        $linkType = $validated['link_type'] ?? ProjectLink::TYPE_URL;
        $validated['link_type'] = $linkType;
        self::applyVisibilityToValidated($validated, $request->input('visibility', 'all'));
        unset($validated['visibility']);

        if ($linkType === ProjectLink::TYPE_APK) {
            if (! $request->hasFile('apk_file')) {
                return redirect()->back()->withInput()->withErrors(['apk_file' => 'APK file is required for APK type.']);
            }
            $file = $request->file('apk_file');
            $ext = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'apk');
            if (! in_array($ext, ['apk', 'aab'], true)) {
                $ext = 'apk';
            }
            $path = $file->storeAs('apk-downloads/' . $project->id, Str::uuid() . '.' . $ext, 'local');
            $validated['file_path'] = $path;
            $validated['file_name'] = self::safeDownloadFileName($file->getClientOriginalName(), $ext);
            $validated['url'] = '#';
        } else {
            if (empty($validated['url'] ?? null)) {
                return redirect()->back()->withInput()->withErrors(['url' => 'URL is required for link type.']);
            }
        }

        unset($validated['apk_file'], $validated['send_email']);
        $link = $project->projectLinks()->create($validated);
        event(new LinkCreated($link, $request->boolean('send_email')));
        return redirect()->route('projects.show', $project)->withFragment('links')->with('success', 'Link added.');
    }

    public function update(Request $request, Project $project, ProjectLink $project_link): RedirectResponse
    {
        if ($project_link->project_id !== $project->id) {
            abort(404);
        }
        $validated = $request->validate([
            'link_type' => ['required', 'in:url,apk'],
            'label' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:2048'],
            'apk_file' => ['nullable', 'file', 'max:512000'],
            'login_username' => ['nullable', 'string', 'max:255'],
            'login_password' => ['nullable', 'string', 'max:255'],
            'visibility' => ['required', 'in:admin_only,client,guest,all'],
        ]);
        if (($validated['link_type'] ?? 'url') === ProjectLink::TYPE_URL && ! filter_var($validated['url'] ?? '', FILTER_VALIDATE_URL)) {
            return redirect()->back()->withInput()->withErrors(['url' => 'A valid URL is required for link type.']);
        }

        $linkType = $validated['link_type'] ?? $project_link->link_type;
        $validated['link_type'] = $linkType;
        self::applyVisibilityToValidated($validated, $request->input('visibility', 'all'));
        unset($validated['visibility']);

        if ($linkType === ProjectLink::TYPE_APK) {
            if ($request->hasFile('apk_file')) {
                if ($project_link->file_path) {
                    Storage::disk('local')->delete($project_link->file_path);
                }
                $file = $request->file('apk_file');
                $ext = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'apk');
                if (! in_array($ext, ['apk', 'aab'], true)) {
                    $ext = 'apk';
                }
                $path = $file->storeAs('apk-downloads/' . $project->id, Str::uuid() . '.' . $ext, 'local');
                $validated['file_path'] = $path;
                $validated['file_name'] = self::safeDownloadFileName($file->getClientOriginalName(), $ext);
            }
            $validated['url'] = $validated['url'] ?? '#';
        } else {
            if (empty($validated['url'] ?? null)) {
                return redirect()->back()->withInput()->withErrors(['url' => 'URL is required for link type.']);
            }
            $validated['file_path'] = null;
            $validated['file_name'] = null;
            if ($project_link->file_path) {
                Storage::disk('local')->delete($project_link->file_path);
            }
        }

        unset($validated['apk_file']);
        $project_link->update($validated);
        return redirect()->route('projects.show', $project)->withFragment('links')->with('success', 'Link updated.');
    }

    private static function safeDownloadFileName(string $originalName, string $ext): string
    {
        $base = pathinfo($originalName, PATHINFO_FILENAME);
        $base = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $base) ?: 'download';
        $base = trim($base, '._');
        return strlen($base) > 200 ? substr($base, 0, 200) . '.' . $ext : $base . '.' . $ext;
    }

    /**
     * Download APK for this project link (auth: admin or project client with visibility).
     */
    public function download(Project $project, ProjectLink $project_link): BinaryFileResponse
    {
        if ($project_link->project_id !== $project->id) {
            abort(404);
        }
        if (! $project_link->isApk() || ! $project_link->file_path) {
            abort(404);
        }
        $user = Auth::user();
        if (! $user->isAdmin()) {
            if (! $user->isClient() || ! $user->client) {
                abort(404);
            }
            $hasAccess = $project->client_id === $user->client->id
                || $project->additionalClients()->where('clients.id', $user->client->id)->exists();
            if (! $hasAccess || ! $project_link->visible_to_client) {
                abort(404);
            }
        }
        $path = storage_path('app/' . $project_link->file_path);
        if (! is_file($path)) {
            abort(404);
        }
        $downloadName = $project_link->file_name ?? basename($project_link->file_path);
        $ext = strtolower(pathinfo($downloadName, PATHINFO_EXTENSION));
        $mime = $ext === 'aab'
            ? 'application/x-authorware-bin'
            : 'application/vnd.android.package-archive';
        return response()->download($path, $downloadName, [
            'Content-Type' => $mime,
        ]);
    }

    public function destroy(Project $project, ProjectLink $project_link): RedirectResponse
    {
        if ($project_link->project_id !== $project->id) {
            abort(404);
        }
        $project_link->delete();
        return redirect()->route('projects.show', $project)->withFragment('links')->with('success', 'Link removed.');
    }
}
