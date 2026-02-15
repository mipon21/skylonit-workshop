<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $templates = [
            [
                'key' => 'client_task_done',
                'name' => 'Client task done',
                'subject' => 'Task completed: {{task_title}} – {{project_name}}',
                'body' => "<p>Hello {{client_name}},</p>\n<p>The following task has been marked as done for {{project_name}}.</p>\n<p><strong>Task:</strong> {{task_title}}</p>\n<p>View project: {{login_url}}</p>",
            ],
            [
                'key' => 'client_milestone_completed',
                'name' => 'Client milestone completed',
                'subject' => 'Milestone completed: {{milestone_name}} – {{project_name}}',
                'body' => "<p>Hello {{client_name}},</p>\n<p>Congratulations! The following milestone has been completed for your project {{project_name}}.</p>\n<p><strong>Milestone:</strong> {{milestone_name}}</p>\n<p>View project: {{login_url}}</p>",
            ],
        ];

        foreach ($templates as $t) {
            if (! DB::table('email_templates')->where('key', $t['key'])->exists()) {
                DB::table('email_templates')->insert(array_merge($t, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            } else {
                DB::table('email_templates')->where('key', $t['key'])->update([
                    'is_active' => true,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Leave templates in place on rollback
    }
};
