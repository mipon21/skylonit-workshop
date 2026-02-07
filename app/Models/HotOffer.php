<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'price',
        'cta_text',
        'is_active',
    ];

    protected $casts = [
        'price' => 'float',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Format plain-text description for display: paragraphs, numbered lists (Bengali/English), and basic structure.
     */
    public function getFormattedDescriptionAttribute(): string
    {
        $description = $this->description ?? '';
        if ($description === '') {
            return '';
        }

        $lines = preg_split('/\r\n|\r|\n/', $description);
        $html = '';
        $inList = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                if ($inList) {
                    $html .= '</ul>';
                    $inList = false;
                }
                $html .= '<br>';
                continue;
            }

            $isListItem = (bool) preg_match('/^[০-৯\d]+[।.]\s*/u', $trimmed) || (bool) preg_match('/^\d+[.\)]\s*/', $trimmed);

            if ($isListItem) {
                if (! $inList) {
                    $html .= '<ul class="hot-offer-list">';
                    $inList = true;
                }
                $html .= '<li>'.e($trimmed).'</li>';
            } else {
                if ($inList) {
                    $html .= '</ul>';
                    $inList = false;
                }
                $isHighlight = str_starts_with($trimmed, '🔴') || str_starts_with($trimmed, '🔑');
                $class = $isHighlight ? 'hot-offer-highlight' : 'hot-offer-paragraph';
                $html .= '<p class="'.$class.'">'.e($trimmed).'</p>';
            }
        }

        if ($inList) {
            $html .= '</ul>';
        }

        return $html;
    }
}
