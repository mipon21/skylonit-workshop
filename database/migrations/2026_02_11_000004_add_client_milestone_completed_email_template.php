<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('email_templates')->updateOrInsert(
            ['key' => 'client_milestone_completed'],
            [
                'name' => 'Client milestone completed',
                'subject' => 'Milestone completed: {{milestone_name}} – {{project_name}}',
                'body' => "<p>Hello {{client_name}},</p>\n<p>Congratulations! The following milestone has been completed for your project {{project_name}}.</p>\n<p><strong>Milestone:</strong> {{milestone_name}}</p>\n<p>View project: {{login_url}}</p>",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('email_templates')->where('key', 'client_milestone_completed')->delete();
    }
};
