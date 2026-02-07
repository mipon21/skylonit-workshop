<?php

namespace App\Http\Controllers;

use App\Jobs\SendTemplateMailJob;
use App\Models\ClientNotification;
use App\Models\Contract;
use App\Models\ContractAudit;
use App\Models\Project;
use App\Models\ProjectActivity;
use App\Services\ContractSigningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContractController extends Controller
{
    private function authorizeProjectForClient(Project $project): void
    {
        if (Auth::user()->isClient() && (! Auth::user()->client || ! $project->hasClientAccess(Auth::user()->client->id))) {
            abort(403, 'You do not have access to this project.');
        }
    }

    private function authorizeContract(Project $project, Contract $contract): void
    {
        if ($contract->project_id !== $project->id) {
            abort(404);
        }
        $this->authorizeProjectForClient($project);
    }

    private function logAudit(Contract $contract, string $action): void
    {
        ContractAudit::create([
            'contract_id' => $contract->id,
            'action' => $action,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeProjectForClient($project);
        if (Auth::user()->isClient()) {
            abort(403, 'Only admin can upload contracts.');
        }

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:512000'],
            'send_email' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('file');
        $ext = $file->getClientOriginalExtension() ?: 'pdf';
        $path = $file->storeAs(
            'contracts/' . $project->id,
            Str::random(40) . '.' . $ext,
            'local'
        );

        $contract = $project->contracts()->create([
            'file_path' => $path,
            'status' => Contract::STATUS_PENDING,
            'uploaded_by' => Auth::id(),
        ]);

        $this->logAudit($contract, ContractAudit::ACTION_UPLOADED);
        ProjectActivity::log(
            $project->id,
            'contract_uploaded',
            'Contract uploaded for signature.',
            ProjectActivity::VISIBILITY_CLIENT
        );

        $client = $project->client;
        if ($client) {
            ClientNotification::create([
                'client_id' => $client->id,
                'project_id' => $project->id,
                'type' => ClientNotification::TYPE_NORMAL,
                'title' => 'Contract ready for signature',
                'message' => 'A contract has been uploaded for project: ' . $project->project_name,
                'is_read' => false,
                'link' => route('projects.show', $project) . '#contracts',
            ]);
        }

        if ($request->boolean('send_email') && $client) {
            $email = $client->user?->email ?? $client->email;
            if ($email) {
                SendTemplateMailJob::dispatch(
                    'client_contract_uploaded',
                    $email,
                    [
                        'client_name' => $client->name,
                        'client_email' => $email,
                        'project_name' => $project->project_name,
                        'project_code' => $project->project_code ?? '',
                        'contract_link' => route('projects.show', $project) . '#contracts',
                        'login_url' => route('login'),
                    ]
                );
            }
        }

        return redirect()->route('projects.show', $project)->withFragment('contracts')->with('success', 'Contract uploaded.');
    }

    public function view(Project $project, Contract $contract): Response
    {
        $this->authorizeContract($project, $contract);
        $path = $contract->isSigned() && $contract->signed_file_path
            ? $contract->signed_file_path
            : $contract->file_path;
        if (! Storage::disk('local')->exists($path)) {
            abort(404);
        }
        $this->logAudit($contract, ContractAudit::ACTION_VIEWED);
        ProjectActivity::log(
            $project->id,
            'contract_viewed',
            'Contract was viewed.',
            ProjectActivity::VISIBILITY_CLIENT
        );

        $fullPath = Storage::disk('local')->path($path);
        $filename = 'contract-' . $contract->id . '.' . pathinfo($path, PATHINFO_EXTENSION);
        return response()->file($fullPath, [
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function download(Project $project, Contract $contract): StreamedResponse
    {
        $this->authorizeContract($project, $contract);
        $path = $contract->isSigned() && $contract->signed_file_path
            ? $contract->signed_file_path
            : $contract->file_path;
        $name = ($contract->isSigned() ? 'signed-contract-' : 'contract-') . $contract->id . '.' . pathinfo($path, PATHINFO_EXTENSION);
        return Storage::disk('local')->download($path, $name);
    }

    public function signForm(Project $project, Contract $contract): View|RedirectResponse
    {
        $this->authorizeContract($project, $contract);
        if ($contract->isSigned()) {
            return redirect()->route('projects.show', $project)->withFragment('contracts')->with('info', 'Contract already signed.');
        }
        return view('projects.contracts.sign', compact('project', 'contract'));
    }

    public function sign(Request $request, Project $project, Contract $contract): RedirectResponse
    {
        $this->authorizeContract($project, $contract);
        if ($contract->isSigned()) {
            return redirect()->route('projects.show', $project)->withFragment('contracts')->with('info', 'Contract already signed.');
        }
        if (! Auth::user()->isClient()) {
            abort(403, 'Only the client can sign this contract.');
        }

        $validated = $request->validate([
            'signature_data' => ['nullable', 'string'], // base64 PNG from canvas
            'signature_text' => ['required_without:signature_data', 'string', 'max:255'], // typed name
            'agree' => ['required', 'accepted'],
        ]);

        $signerName = Auth::user()->name;
        $signedAt = now()->format('Y-m-d H:i:s');
        $ipAddress = $request->ip();

        $sourcePath = Storage::disk('local')->path($contract->file_path);
        $ext = strtolower(pathinfo($contract->file_path, PATHINFO_EXTENSION));
        $signedPath = null;

        if ($ext === 'pdf') {
            $signatureImagePath = null;
            if (! empty($validated['signature_data'])) {
                $png = $validated['signature_data'];
                if (preg_match('/^data:image\/png;base64,/', $png)) {
                    $png = substr($png, strpos($png, ',') + 1);
                }
                $decoded = base64_decode($png, true);
                if ($decoded !== false) {
                    $sigPath = 'contracts/signatures/' . $contract->id . '-' . Str::random(8) . '.png';
                    Storage::disk('local')->put($sigPath, $decoded);
                    $signatureImagePath = Storage::disk('local')->path($sigPath);
                }
            }
            if (! $signatureImagePath) {
                $signatureImagePath = $this->createTextSignatureImage($validated['signature_text'] ?? $signerName);
            }
            $outputPath = 'contracts/' . $project->id . '/signed-' . $contract->id . '-' . Str::random(8) . '.pdf';
            $signingService = app(ContractSigningService::class);
            $signedPath = $signingService->stampSignature(
                $sourcePath,
                $signatureImagePath,
                $signerName,
                $signedAt,
                $ipAddress,
                $outputPath
            );
            if (isset($sigPath) && Storage::disk('local')->exists($sigPath)) {
                Storage::disk('local')->delete($sigPath);
            }
            if (is_string($signatureImagePath) && file_exists($signatureImagePath)) {
                @unlink($signatureImagePath);
            }
        } else {
            $signedPath = $contract->file_path;
        }

        $contract->update([
            'signed_file_path' => $signedPath,
            'status' => Contract::STATUS_SIGNED,
            'signed_by' => Auth::id(),
            'signed_at' => now(),
        ]);

        $this->logAudit($contract, ContractAudit::ACTION_SIGNED);
        ProjectActivity::log(
            $project->id,
            'contract_signed',
            'Contract was signed by client.',
            ProjectActivity::VISIBILITY_CLIENT
        );

        $client = $project->client;
        if ($client) {
            ClientNotification::create([
                'client_id' => $client->id,
                'project_id' => $project->id,
                'type' => ClientNotification::TYPE_NORMAL,
                'title' => 'Contract signed',
                'message' => 'You signed the contract for project: ' . $project->project_name,
                'is_read' => false,
                'link' => route('projects.show', $project) . '#contracts',
            ]);
        }

        $signedFullPath = $contract->signed_file_path && Storage::disk('local')->exists($contract->signed_file_path)
            ? Storage::disk('local')->path($contract->signed_file_path)
            : null;
        $adminUsers = \App\Models\User::where('role', 'admin')->pluck('email')->filter()->toArray();
        foreach ($adminUsers as $adminEmail) {
            SendTemplateMailJob::dispatch(
                'client_contract_signed',
                $adminEmail,
                [
                    'client_name' => $client?->name ?? 'Client',
                    'project_name' => $project->project_name,
                    'project_code' => $project->project_code ?? '',
                    'signed_at' => $signedAt,
                ],
                $signedFullPath && is_file($signedFullPath) ? $contract->signed_file_path : null,
                'signed-contract-' . $project->project_code . '.pdf'
            );
        }
        $clientEmail = $client->user?->email ?? $client->email ?? null;
        if ($clientEmail) {
            SendTemplateMailJob::dispatch(
                'client_contract_signed',
                $clientEmail,
                [
                    'client_name' => $client->name,
                    'project_name' => $project->project_name,
                    'project_code' => $project->project_code ?? '',
                    'signed_at' => $signedAt,
                ],
                $signedFullPath && is_file($signedFullPath) ? $contract->signed_file_path : null,
                'signed-contract-' . $project->project_code . '.pdf'
            );
        }

        return redirect()->route('projects.show', $project)->withFragment('contracts')->with('success', 'Contract signed successfully.');
    }

    private function createTextSignatureImage(string $text): string
    {
        $dir = storage_path('app/contracts/signatures');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $path = $dir . '/sig-' . Str::random(8) . '.png';
        $width = 400;
        $height = 120;
        $img = imagecreatetruecolor($width, $height);
        if (! $img) {
            throw new \RuntimeException('Could not create signature image.');
        }
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        imagefill($img, 0, 0, $white);
        imagestring($img, 5, 20, 50, $text, $black);
        imagepng($img, $path);
        imagedestroy($img);
        return $path;
    }

    public function destroy(Project $project, Contract $contract): RedirectResponse
    {
        $this->authorizeContract($project, $contract);
        if (Auth::user()->isClient()) {
            abort(403, 'Only admin can delete contracts.');
        }

        if (Storage::disk('local')->exists($contract->file_path)) {
            Storage::disk('local')->delete($contract->file_path);
        }
        if ($contract->signed_file_path && Storage::disk('local')->exists($contract->signed_file_path)) {
            Storage::disk('local')->delete($contract->signed_file_path);
        }

        $contract->delete();

        return redirect()->route('projects.show', $project)->withFragment('contracts')->with('success', 'Contract deleted.');
    }

    public function sendEmail(Project $project, Contract $contract): RedirectResponse
    {
        $this->authorizeContract($project, $contract);
        if (Auth::user()->isClient()) {
            abort(403, 'Only admin can send for signature.');
        }
        $client = $project->client;
        if (! $client) {
            return redirect()->back()->with('error', 'No client linked to this project.');
        }
        $email = $client->user?->email ?? $client->email;
        if (! $email) {
            return redirect()->back()->with('error', 'Client has no email.');
        }
        SendTemplateMailJob::dispatch(
            'client_contract_uploaded',
            $email,
            [
                'client_name' => $client->name,
                'client_email' => $email,
                'project_name' => $project->project_name,
                'project_code' => $project->project_code ?? '',
                'contract_link' => route('projects.show', $project) . '#contracts',
                'login_url' => route('login'),
            ]
        );
        return redirect()->route('projects.show', $project)->withFragment('contracts')->with('success', 'Signature request email sent.');
    }
}
