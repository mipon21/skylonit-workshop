<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientNotification extends Model
{
    public const TYPE_NORMAL = 'normal';
    public const TYPE_PAYMENT = 'payment';

    protected $fillable = [
        'client_id',
        'project_id',
        'activity_id',
        'type',
        'title',
        'message',
        'is_read',
        'payment_id',
        'invoice_id',
        'link',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(ProjectActivity::class, 'activity_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    /** Whether this is a payment-type notification (fullscreen popup). */
    public function isPaymentType(): bool
    {
        return $this->type === self::TYPE_PAYMENT;
    }

    /**
     * Get unread notifications for popups only: latest 2 normal (small) + latest 2 payment (large).
     * Marks all other unread as read so they never show on refresh.
     */
    public static function getLatestUnreadForPopups(int $clientId)
    {
        $base = static::where('client_id', $clientId)
            ->where('is_read', false)
            ->orderByDesc('created_at');

        $latestNormal = (clone $base)->where('type', '!=', self::TYPE_PAYMENT)->limit(2)->get();
        $latestPayment = (clone $base)->where('type', self::TYPE_PAYMENT)->limit(2)->get();

        $forPopups = $latestNormal->concat($latestPayment)->sortByDesc('created_at')->values();
        $idsToShow = $forPopups->pluck('id')->all();

        if (! empty($idsToShow)) {
            static::where('client_id', $clientId)
                ->where('is_read', false)
                ->whereNotIn('id', $idsToShow)
                ->update(['is_read' => true]);
        }

        return $forPopups;
    }
}
