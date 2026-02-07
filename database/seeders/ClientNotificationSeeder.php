<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientNotification;
use App\Models\Project;
use Illuminate\Database\Seeder;

class ClientNotificationSeeder extends Seeder
{
    /**
     * Seed demo client notifications (unread) for clients that have projects.
     */
    public function run(): void
    {
        $projects = Project::with('client')->limit(10)->get();
        $clientIds = $projects->pluck('client_id')->filter()->unique()->take(3)->values();

        if ($clientIds->isEmpty()) {
            return;
        }

        $demoNormal = [
            ['title' => 'Task completed', 'message' => "Task 'Homepage UI' marked as Done"],
            ['title' => 'Document uploaded', 'message' => "Document 'Contract.pdf' uploaded"],
            ['title' => 'Note added', 'message' => "Note 'Kickoff meeting' created"],
        ];
        $demoPayment = [
            ['title' => 'Payment created', 'message' => 'Payment ৳5,000 is due for your project.'],
            ['title' => 'Invoice generated', 'message' => 'Invoice INV-2026-0001 has been generated for your project.'],
        ];

        foreach ($clientIds as $i => $clientId) {
            $project = $projects->firstWhere('client_id', $clientId) ?? $projects->first();
            ClientNotification::create([
                'client_id' => $clientId,
                'project_id' => $project->id,
                'activity_id' => null,
                'type' => ClientNotification::TYPE_NORMAL,
                'title' => $demoNormal[$i % count($demoNormal)]['title'],
                'message' => $demoNormal[$i % count($demoNormal)]['message'],
                'is_read' => false,
            ]);
            ClientNotification::create([
                'client_id' => $clientId,
                'project_id' => $project->id,
                'activity_id' => null,
                'type' => ClientNotification::TYPE_PAYMENT,
                'title' => $demoPayment[$i % count($demoPayment)]['title'],
                'message' => $demoPayment[$i % count($demoPayment)]['message'],
                'is_read' => false,
            ]);
        }
    }
}
