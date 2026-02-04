<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::all();
        if ($clients->isEmpty()) {
            return;
        }

        $projects = [
            ['project_name' => 'Company Website', 'project_code' => 'PRJ-001', 'contract_amount' => 150000, 'status' => 'Complete'],
            ['project_name' => 'E-commerce Platform', 'project_code' => 'PRJ-002', 'contract_amount' => 450000, 'status' => 'Running'],
            ['project_name' => 'Mobile App Backend', 'project_code' => 'PRJ-003', 'contract_amount' => 280000, 'status' => 'Pending'],
        ];

        foreach ($projects as $i => $data) {
            Project::firstOrCreate(
                ['project_code' => $data['project_code']],
                [
                    'client_id' => $clients->get($i % $clients->count())->id,
                    'project_name' => $data['project_name'],
                    'contract_amount' => $data['contract_amount'],
                    'contract_date' => now()->subMonths(rand(1, 6)),
                    'delivery_date' => now()->addMonths(rand(1, 4)),
                    'status' => $data['status'],
                ]
            );
        }
    }
}
