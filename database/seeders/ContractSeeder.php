<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\ContractAudit;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Storage;

class ContractSeeder extends Seeder
{
    public function run(): void
    {
        $project = Project::first();
        if (! $project) {
            return;
        }
        $admin = User::where('role', 'admin')->first();
        if (! $admin) {
            return;
        }

        $dir = 'contracts/' . $project->id;
        if (! Storage::disk('local')->exists($dir)) {
            Storage::disk('local')->makeDirectory($dir);
        }
        $filename = 'demo-contract-' . uniqid() . '.pdf';
        $path = $dir . '/' . $filename;

        $pdf = new Fpdi();
        $pdf->AddPage();
        $pdf->SetFont('Helvetica', '', 12);
        $pdf->Cell(0, 10, 'Demo Contract', 0, 1);
        $pdf->Ln(4);
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->MultiCell(0, 6, "This is a sample contract for testing the e-sign feature.\n\nProject: " . $project->project_name . "\n\nSign this contract in the Client Portal to test the digital signature flow.");
        $content = $pdf->Output('S');
        Storage::disk('local')->put($path, $content);

        $contract = Contract::create([
            'project_id' => $project->id,
            'file_path' => $path,
            'status' => Contract::STATUS_PENDING,
            'uploaded_by' => $admin->id,
        ]);

        ContractAudit::create([
            'contract_id' => $contract->id,
            'action' => ContractAudit::ACTION_UPLOADED,
            'user_id' => $admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Seeder',
            'created_at' => now(),
        ]);
    }
}
