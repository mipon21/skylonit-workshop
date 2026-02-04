<?php

use App\Models\Project;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Project::query()->each(function (Project $project) {
            $project->createDefaultPayouts();
        });
    }

    public function down(): void
    {
        // Optional: leave payouts as-is on rollback
    }
};
