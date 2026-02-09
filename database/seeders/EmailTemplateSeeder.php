<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'key' => 'client_account_created',
                'name' => 'Client account created',
                'subject' => 'Your client account has been created',
                'body' => "<p>Hello {{client_name}},</p>\n<p>Your client account has been created.</p>\n<p><strong>Login URL:</strong> {{login_url}}</p>\n<p><strong>Email:</strong> {{client_email}}</p>\n<p><strong>Password:</strong> {{client_password}}</p>\n<p>Please log in and change your password if you wish.</p>",
            ],
            [
                'key' => 'client_project_created',
                'name' => 'Client project created',
                'subject' => 'New project: {{project_name}}',
                'body' => "<p>Hello {{client_name}},</p>\n<p>A new project has been created for you.</p>\n<p><strong>Project:</strong> {{project_name}}</p>\n<p><strong>Code:</strong> {{project_code}}</p>\n<p>Log in to view details: {{login_url}}</p>",
            ],
            [
                'key' => 'client_payment_created',
                'name' => 'Client payment created (due)',
                'subject' => 'Payment due for {{project_name}}',
                'body' => "<p>Hello {{client_name}},</p>\n<p>A payment of ৳{{payment_amount}} is due for project {{project_name}}.</p>\n<p><strong>Pay here:</strong> <a href=\"{{payment_link}}\">{{payment_link}}</a></p>\n<p>Or log in: {{login_url}}</p>",
            ],
            [
                'key' => 'client_payment_success',
                'name' => 'Client payment successful',
                'subject' => 'Payment received – {{project_name}}',
                'body' => "<p>Hello {{client_name}},</p>\n<p>We have received your payment of ৳{{payment_amount}} for {{project_name}}.</p>\n<p>Your invoice is attached to this email.</p>\n<p>View invoices: {{invoice_link}}</p>",
            ],
            [
                'key' => 'client_document_uploaded',
                'name' => 'Client document uploaded',
                'subject' => 'New document for {{project_name}}: {{document_name}}',
                'body' => "<p>Hello {{client_name}},</p>\n<p>A new document has been added to your project {{project_name}}.</p>\n<p><strong>Document:</strong> {{document_name}}</p>\n<p>View it here: {{login_url}}</p>",
            ],
            [
                'key' => 'client_expense_created',
                'name' => 'Client expense created',
                'subject' => 'Expense added to {{project_name}}',
                'body' => "<p>Hello {{client_name}},</p>\n<p>An expense of ৳{{expense_amount}} has been recorded for project {{project_name}}.</p>\n<p>Log in to view: {{login_url}}</p>",
            ],
            [
                'key' => 'client_note_created',
                'name' => 'Client note created',
                'subject' => 'New note for {{project_name}}: {{note_title}}',
                'body' => "<p>Hello {{client_name}},</p>\n<p>A new note has been added to your project {{project_name}}.</p>\n<p><strong>Note:</strong> {{note_title}}</p>\n<p>View: {{login_url}}</p>",
            ],
            [
                'key' => 'client_link_created',
                'name' => 'Client link created',
                'subject' => 'New link for {{project_name}}',
                'body' => "<p>Hello {{client_name}},</p>\n<p>A new link has been shared for your project {{project_name}}.</p>\n<p><strong>Link:</strong> <a href=\"{{link_url}}\">{{link_url}}</a></p>\n<p>Or open from your project: {{login_url}}</p>",
            ],
            [
                'key' => 'client_bug_resolved',
                'name' => 'Client bug resolved',
                'subject' => 'Bug resolved: {{bug_title}} – {{project_name}}',
                'body' => "<p>Hello {{client_name}},</p>\n<p>The following bug has been marked as resolved for {{project_name}}.</p>\n<p><strong>Bug:</strong> {{bug_title}}</p>\n<p>View project: {{login_url}}</p>",
            ],
            [
                'key' => 'client_task_done',
                'name' => 'Client task done',
                'subject' => 'Task completed: {{task_title}} – {{project_name}}',
                'body' => "<p>Hello {{client_name}},</p>\n<p>The following task has been marked as done for {{project_name}}.</p>\n<p><strong>Task:</strong> {{task_title}}</p>\n<p>View project: {{login_url}}</p>",
            ],
            [
                'key' => 'client_contract_uploaded',
                'name' => 'Client contract uploaded (ready for signature)',
                'subject' => 'Contract ready for signature – {{project_name}}',
                'body' => "<p>Hello {{client_name}},</p>\n<p>A contract has been uploaded for your project <strong>{{project_name}}</strong> and is ready for your signature.</p>\n<p><strong>Sign here:</strong> <a href=\"{{contract_link}}\">{{contract_link}}</a></p>\n<p>Or log in to your portal: {{login_url}}</p>",
            ],
            [
                'key' => 'client_contract_signed',
                'name' => 'Client contract signed (confirmation)',
                'subject' => 'Contract signed – {{project_name}}',
                'body' => "<p>Hello {{client_name}},</p>\n<p>This is to confirm that the contract for project <strong>{{project_name}}</strong> was signed on {{signed_at}}.</p>\n<p>The signed copy is attached to this email (if applicable).</p>",
            ],
            [
                'key' => 'project_assigned',
                'name' => 'Developer/Sales assigned to project',
                'subject' => 'You have been assigned to project: {{project_name}}',
                'body' => "<p>Hello {{name}},</p>\n<p>You have been assigned to a project.</p>\n<p><strong>Project:</strong> {{project_name}}</p>\n<p><strong>Code:</strong> {{project_code}}</p>\n<p>Log in to view tasks, bugs, notes and links: <a href=\"{{login_url}}\">{{login_url}}</a></p>",
            ],
            [
                'key' => 'internal_account_created',
                'name' => 'Internal user (Developer/Sales) account created',
                'subject' => 'Your account has been created – {{name}}',
                'body' => "<p>Hello {{name}},</p>\n<p>Your account has been created.</p>\n<p><strong>Email:</strong> {{email}}</p>\n<p><strong>Password:</strong> {{password}}</p>\n<p>Log in here: <a href=\"{{login_url}}\">{{login_url}}</a></p>\n<p>Please change your password after first login.</p>",
            ],
            [
                'key' => 'developer_task_assigned',
                'name' => 'Developer task assigned',
                'subject' => 'Task assigned to you: {{task_title}} – {{project_name}}',
                'body' => "<p>Hello {{name}},</p>\n<p>A task has been assigned to you.</p>\n<p><strong>Project:</strong> {{project_name}} ({{project_code}})</p>\n<p><strong>Task:</strong> {{task_title}}</p>\n<p><a href=\"{{project_url}}\">View project &raquo;</a></p>",
            ],
            [
                'key' => 'developer_bug_assigned',
                'name' => 'Developer bug assigned',
                'subject' => 'Bug assigned to you: {{bug_title}} – {{project_name}}',
                'body' => "<p>Hello {{name}},</p>\n<p>A bug has been assigned to you.</p>\n<p><strong>Project:</strong> {{project_name}} ({{project_code}})</p>\n<p><strong>Bug:</strong> {{bug_title}}</p>\n<p><a href=\"{{project_url}}\">View project &raquo;</a></p>",
            ],
            [
                'key' => 'sales_project_complete',
                'name' => 'Sales – project status Complete',
                'subject' => 'Project marked Complete: {{project_name}}',
                'body' => "<p>Hello {{name}},</p>\n<p>The following project has been marked as Complete.</p>\n<p><strong>Project:</strong> {{project_name}} ({{project_code}})</p>\n<p><a href=\"{{project_url}}\">View project &raquo;</a></p>",
            ],
            [
                'key' => 'developer_payout_updated',
                'name' => 'Developer payout status updated',
                'subject' => 'Payment status updated for {{project_name}}',
                'body' => "<p>Hello {{name}},</p>\n<p>Your {{payout_type}} payment status for project <strong>{{project_name}}</strong> has been updated to: {{payout_status}}.</p>\n<p><a href=\"{{project_url}}\">View project &raquo;</a></p>",
            ],
            [
                'key' => 'sales_payout_updated',
                'name' => 'Sales payout status updated',
                'subject' => 'Payment status updated for {{project_name}}',
                'body' => "<p>Hello {{name}},</p>\n<p>Your {{payout_type}} payment status for project <strong>{{project_name}}</strong> has been updated to: {{payout_status}}.</p>\n<p><a href=\"{{project_url}}\">View project &raquo;</a></p>",
            ],
            [
                'key' => 'developer_note_added',
                'name' => 'Developer – new note on project',
                'subject' => 'New note for {{project_name}}: {{note_title}}',
                'body' => "<p>Hello {{name}},</p>\n<p>A new note has been added to a project you are assigned to.</p>\n<p><strong>Project:</strong> {{project_name}} ({{project_code}})</p>\n<p><strong>Note:</strong> {{note_title}}</p>\n<p><a href=\"{{project_url}}\">View project &raquo;</a></p>",
            ],
            [
                'key' => 'developer_link_added',
                'name' => 'Developer – new link on project',
                'subject' => 'New link for {{project_name}}: {{link_label}}',
                'body' => "<p>Hello {{name}},</p>\n<p>A new link has been added to a project you are assigned to.</p>\n<p><strong>Project:</strong> {{project_name}} ({{project_code}})</p>\n<p><strong>Link:</strong> {{link_label}}</p>\n<p><a href=\"{{project_url}}\">View project &raquo;</a></p>",
            ],
            [
                'key' => 'developer_document_uploaded',
                'name' => 'Developer – new document on project',
                'subject' => 'New document for {{project_name}}: {{document_name}}',
                'body' => "<p>Hello {{name}},</p>\n<p>A new document has been uploaded to a project you are assigned to.</p>\n<p><strong>Project:</strong> {{project_name}} ({{project_code}})</p>\n<p><strong>Document:</strong> {{document_name}}</p>\n<p><a href=\"{{project_url}}\">View project &raquo;</a></p>",
            ],
        ];

        foreach ($templates as $t) {
            EmailTemplate::updateOrCreate(
                ['key' => $t['key']],
                array_merge($t, ['is_active' => true])
            );
        }
    }
}
