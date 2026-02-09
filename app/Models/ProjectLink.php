<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectLink extends Model
{
    use HasFactory;

    public const TYPE_URL = 'url';
    public const TYPE_APK = 'apk';

    /** Visibility: who can see this link. Admin always sees all. */
    public const VISIBILITY_ADMIN_ONLY = 'admin_only';
    public const VISIBILITY_ADMIN_DEVELOPER = 'admin_developer'; // Admin + Developer only
    public const VISIBILITY_CLIENT = 'client';       // Admin + Client (no guest)
    public const VISIBILITY_GUEST = 'guest';        // Admin + Guest (no client)
    public const VISIBILITY_ALL = 'all';             // Admin + Client + Guest

    protected $fillable = [
        'project_id',
        'created_by',
        'link_type',
        'label',
        'url',
        'file_path',
        'file_name',
        'login_username',
        'login_password',
        'is_public',
        'visible_to_client',
        'visible_to_developer',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'visible_to_client' => 'boolean',
        'visible_to_developer' => 'boolean',
    ];

    /** Current visibility slug from is_public + visible_to_client + visible_to_developer. */
    public function getVisibilityAttribute(): string
    {
        $client = (bool) ($this->visible_to_client ?? false);
        $guest = (bool) ($this->is_public ?? false);
        $developer = (bool) ($this->visible_to_developer ?? false);
        if (! $client && ! $guest && $developer) {
            return self::VISIBILITY_ADMIN_DEVELOPER;
        }
        if (! $client && ! $guest) {
            return self::VISIBILITY_ADMIN_ONLY;
        }
        if ($client && ! $guest) {
            return self::VISIBILITY_CLIENT;
        }
        if (! $client && $guest) {
            return self::VISIBILITY_GUEST;
        }
        return self::VISIBILITY_ALL;
    }

    public static function visibilityLabels(): array
    {
        return [
            self::VISIBILITY_ADMIN_ONLY => 'Admin only',
            self::VISIBILITY_ADMIN_DEVELOPER => 'Admin & Developer only',
            self::VISIBILITY_CLIENT => 'Admin & Client (no guest)',
            self::VISIBILITY_GUEST => 'Admin & Guest (no client)',
            self::VISIBILITY_ALL => 'Everyone (Admin, Client & Guest)',
        ];
    }

    public function isApk(): bool
    {
        return ($this->link_type ?? self::TYPE_URL) === self::TYPE_APK;
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
