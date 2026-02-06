<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\ProjectLink;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GuestLinkController extends Controller
{
    /**
     * Public live links and APK downloads: all public links across projects.
     */
    public function index(): View
    {
        $links = ProjectLink::where('is_public', true)
            ->with('project:id,project_name,is_public')
            ->whereHas('project', fn ($q) => $q->where('is_public', true))
            ->orderBy('project_id')
            ->orderBy('label')
            ->get();

        return view('guest.links.index', compact('links'));
    }

    /**
     * Download APK file (public link only).
     */
    public function download(ProjectLink $project_link): BinaryFileResponse
    {
        if (! $project_link->is_public || ! $project_link->isApk() || ! $project_link->file_path) {
            abort(404);
        }
        if ($project_link->project && ! $project_link->project->is_public) {
            abort(404);
        }

        $path = storage_path('app/' . $project_link->file_path);
        if (! is_file($path)) {
            abort(404);
        }

        $downloadName = $project_link->file_name
            ?? basename($project_link->file_path);

        $ext = strtolower(pathinfo($downloadName, PATHINFO_EXTENSION));
        $mime = $ext === 'aab'
            ? 'application/x-authorware-bin'
            : 'application/vnd.android.package-archive';

        return response()->download($path, $downloadName, [
            'Content-Type' => $mime,
        ]);
    }
}
