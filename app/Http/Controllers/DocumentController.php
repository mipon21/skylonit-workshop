<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;

class DocumentController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,png,jpg,jpeg,zip', 'max:512000'], // 500 MB (max in KB)
        ]);

        $file = $request->file('file');
        $path = $file->store('project-documents/' . $project->id, 'local');

        $project->documents()->create([
            'title' => $validated['title'],
            'file_path' => $path,
            'uploaded_at' => now(),
        ]);

        return redirect()->route('projects.show', $project)->withFragment('documents')->with('success', 'Document uploaded.');
    }

    /** Open document in the browser (inline display for PDF/images, etc.). */
    public function view(Project $project, Document $document): Response
    {
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
        return Storage::download($document->file_path, $document->title . '.' . pathinfo($document->file_path, PATHINFO_EXTENSION));
    }

    public function destroy(Project $project, Document $document): RedirectResponse
    {
        if ($document->project_id !== $project->id) {
            abort(404);
        }
        Storage::delete($document->file_path);
        $document->delete();
        return redirect()->route('projects.show', $project)->withFragment('documents')->with('success', 'Document deleted.');
    }
}
