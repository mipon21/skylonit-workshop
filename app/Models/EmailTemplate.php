<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'key',
        'name',
        'subject',
        'body',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** Placeholders supported across templates (for admin UI). */
    public static function availablePlaceholders(): array
    {
        return [
            'name',
            'client_name',
            'client_email',
            'project_name',
            'project_code',
            'payment_amount',
            'payment_link',
            'invoice_link',
            'document_name',
            'expense_amount',
            'note_title',
            'link_url',
            'task_title',
            'milestone_name',
            'bug_title',
            'login_url',
            'client_password',
            'contract_link',
            'signed_at',
        ];
    }
}
