<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    public const STATUS_PAID = 'PAID';
    public const STATUS_PARTIAL = 'PARTIAL';
    public const STATUS_DUE = 'DUE';

    protected $fillable = [
        'project_id',
        'payment_id',
        'invoice_number',
        'invoice_date',
        'payment_status',
        'file_path',
    ];

    protected $casts = [
        'invoice_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Generate unique invoice number format: INV-YYYY-XXXX
     */
    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $lastInvoice = self::whereYear('invoice_date', $year)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastInvoice ? (intval(substr($lastInvoice->invoice_number, -4)) + 1) : 1;

        return sprintf('INV-%s-%04d', $year, $nextNumber);
    }

    /**
     * Get watermark color based on payment status
     */
    public function getWatermarkColorAttribute(): string
    {
        return match ($this->payment_status) {
            self::STATUS_PAID => '#22C55E',    // green
            self::STATUS_PARTIAL => '#F97316', // orange
            self::STATUS_DUE => '#EF4444',     // red
            default => '#9CA3AF',              // gray
        };
    }

    /**
     * Get watermark text
     */
    public function getWatermarkTextAttribute(): string
    {
        return $this->payment_status;
    }
}
