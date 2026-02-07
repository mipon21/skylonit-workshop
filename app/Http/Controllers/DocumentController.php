<?php

namespace App\Http\Controllers;

use App\Events\DocumentUploaded;
use App\Models\Document;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
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
            'file' => ['required', 'file', 'max:512000'], // 500 MB (max in KB) — any type (apk, aab, pdf, zip, 7z, rar, etc.)
            'is_public' => ['nullable', 'boolean'],
            'send_email' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension() ?: 'bin';
        $path = $file->storeAs(
            'project-documents/' . $project->id,
            Str::random(40) . '.' . $extension,
            'local'
        );

        $isPublic = Auth::user()->isClient()
            ? true
            : $request->boolean('is_public');

        $document = $project->documents()->create([
            'title' => $validated['title'],
            'file_path' => $path,
            'uploaded_at' => now(),
            'uploaded_by_user_id' => Auth::id(),
            'is_public' => $isPublic,
        ]);
        event(new DocumentUploaded($document, $request->boolean('send_email')));

        return redirect()->route('projects.show', $project)->withFragment('documents')->with('success', 'Document uploaded.');
    }

    public function update(Request $request, Project $project, Document $document): RedirectResponse
    {
        if ($document->project_id !== $project->id) {
            abort(404);
        }
        $this->authorizeProjectForClient($project);
        $validated = $request->validate([
            'is_public' => ['required', 'boolean'],
        ]);
        $document->update($validated);
        return redirect()->route('projects.show', $project)->withFragment('documents')->with('success', 'Document visibility updated.');
    }

    /** Open document in the browser (inline display for PDF/images, etc.). */
    public function view(Project $project, Document $document): Response
    {
        $this->authorizeProjectForClient($project);
        if ($document->project_id !== $project->id) {
            abort(404);
        }
        if (!Storage::exists($document->file_path)) {
            abort(404);
        }
        $path = Storage::path($document->file_path);
        $filename = $document->title . '.' . pathinfo($document->file_path, PATHINFO_EXTENSION);
        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="' . basename($filename) . '"',
        ]);
    }

    public function download(Project $project, Document $document): StreamedResponse
    {
        if ($document->project_id !== $project->id) {
            abort(404);
        }
        $disk = Storage::disk('local');
        if (! $disk->exists($document->file_path)) {
            abort(404);
        }
        $filename = $document->title . '.' . pathinfo($document->file_path, PATHINFO_EXTENSION);
        // Stream download without requiring file_size metadata (avoids Flysystem UnableToRetrieveMetadata)
        return response()->streamDownload(function () use ($disk, $document) {
            $stream = $disk->readStream($document->file_path);
            if (is_resource($stream)) {
                fpassthru($stream);
                fclose($stream);
            }
        }, $filename, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    public function destroy(Project $project, Document $document): RedirectResponse
    {
        $this->authorizeProjectForClient($project);
        if ($document->project_id !== $project->id) {
            abort(404);
        }
        Storage::delete($document->file_path);
        $document->delete();
        return redirect()->route('projects.show', $project)->withFragment('documents')->with('success', 'Document deleted.');
    }
}
