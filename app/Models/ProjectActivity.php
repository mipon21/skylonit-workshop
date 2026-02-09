<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ProjectActivity extends Model
{
    public const VISIBILITY_INTERNAL = 'internal';
    public const VISIBILITY_CLIENT = 'client';
    /** Visible to assigned developers and sales (e.g. payment marked paid, project assignment). */
    public const VISIBILITY_DEVELOPER_SALES = 'developer_sales';

    protected $fillable = [
        'project_id',
        'user_id',
        'action_type',
        'description',
        'visibility',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Actor for display: role only (Admin, Developer, Client, Sales) – no names. */
    public function getActorNameAttribute(): string
    {
        if ($this->user_id && $this->relationLoaded('user') && $this->user) {
            return ucfirst($this->user->role);
        }
        if ($this->user_id) {
            return 'Admin';
        }
        return 'System';
    }

    /** Icon name/key by action_type for UI. */
    public static function iconFor(string $actionType): string
    {
        return match ($actionType) {
            'payment_created', 'payment_marked_paid' => 'payment',
            'task_created', 'task_status_changed' => 'task',
            'bug_created', 'bug_status_changed' => 'bug',
            'document_uploaded', 'document_deleted' => 'document',
            'note_created', 'note_updated' => 'note',
            'link_created', 'link_updated' => 'link',
            'expense_created' => 'expense',
            'project_created', 'project_status_changed' => 'project',
            'invoice_generated' => 'invoice',
            'contract_uploaded', 'contract_viewed', 'contract_signed' => 'document',
            default => 'activity',
        };
    }

    /** Log an activity for a project. */
    public static function log(
        int $projectId,
        string $actionType,
        string $description,
        string $visibility = self::VISIBILITY_INTERNAL
    ): self {
        return self::create([
            'project_id' => $projectId,
            'user_id' => Auth::id(),
            'action_type' => $actionType,
            'description' => $description,
            'visibility' => $visibility,
        ]);
    }
}
