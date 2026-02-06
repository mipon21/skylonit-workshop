<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestPortalLayout extends Component
{
    public string $whatsappNumber = '';

    public function __construct(
        public ?string $title = null
    ) {
        $raw = trim((string) (config('app.whatsapp_number') ?? ''));
        if ($raw === '') {
            $raw = trim((string) (env('WHATSAPP_NUMBER') ?? ''));
        }
        if ($raw === '') {
            $raw = trim((string) (config('mail.footer.whatsapp') ?? ''));
        }
        $digits = $raw !== '' ? preg_replace('/[^0-9]/', '', $raw) : '';
        $this->whatsappNumber = $digits !== '' ? $digits : '8801743233833';
    }

    public function render(): View
    {
        return view('layouts.guest-portal');
    }
}
