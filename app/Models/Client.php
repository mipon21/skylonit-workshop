<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'email',
        'address',
        'fb_link',
        'whatsapp_number',
        'kyc',
    ];

    /**
     * WhatsApp chat link (https://api.whatsapp.com/send/?phone=...). Phone is digits only.
     */
    public function getWhatsappLinkAttribute(): ?string
    {
        if (empty($this->attributes['whatsapp_number'] ?? null)) {
            return null;
        }
        $digits = preg_replace('/\D/', '', $this->attributes['whatsapp_number']);
        return $digits !== '' ? 'https://api.whatsapp.com/send/?phone=' . $digits : null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /** Projects where this client is linked as additional (not primary). */
    public function additionalProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_clients')->withTimestamps();
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(ClientNotification::class)->orderByDesc('created_at');
    }

    public function devices(): HasMany
    {
        return $this->hasMany(ClientDevice::class);
    }
}
