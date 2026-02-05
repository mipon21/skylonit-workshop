<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $mailSubject ?? '' }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    @if(!empty($logoUrl))
    <div style="margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #e5e7eb;">
        <img src="{{ $logoUrl }}" alt="{{ config('app.name') }}" style="max-height: 48px; width: auto; display: block;" />
    </div>
    @endif
    {!! $body ?? '' !!}

    @php
        $f = $footer ?? [];
        $hasFooter = !empty($f['phone']) || !empty($f['website']) || !empty($f['facebook']) || !empty($f['whatsapp_link']);
        $iconStyle = 'width:18px;height:18px;vertical-align:middle;display:inline-block;margin-right:4px;border:0;';
        $iconBase = rtrim(config('mail.footer.icon_base', 'https://img.icons8.com/fluency/24'), '/');
        $iconGlobe = config('mail.footer.icon_globe') ?: $iconBase . '/internet.png';
    @endphp
    @if($hasFooter)
    <hr style="margin: 24px 0; border: none; border-top: 1px solid #e5e7eb;">
    <p style="text-align: center; font-family: Arial, sans-serif; font-size: 14px; margin: 0;">
        @if(!empty($f['phone']))
        <a href="tel:{{ preg_replace('/\D/', '', $f['phone']) }}" style="color: #2563eb; text-decoration: none; margin: 0 10px;"><img src="{{ $iconBase }}/phone.png" alt="" width="18" height="18" style="{{ $iconStyle }}">{{ $f['phone'] }}</a>
        @if(!empty($f['website']) || !empty($f['facebook']) || !empty($f['whatsapp_link']))<span style="color: #9ca3af;">|</span>@endif
        @endif
        @if(!empty($f['website']))
        <a href="{{ $f['website'] }}" style="color: #2563eb; text-decoration: none; margin: 0 10px;"><img src="{{ $iconGlobe }}" alt="" width="18" height="18" style="{{ $iconStyle }}">{{ parse_url($f['website'], PHP_URL_HOST) ?: preg_replace('#^https?://#', '', $f['website']) }}</a>
        @if(!empty($f['facebook']) || !empty($f['whatsapp_link']))<span style="color: #9ca3af;">|</span>@endif
        @endif
        @if(!empty($f['facebook']))
        <a href="{{ $f['facebook'] }}" style="color: #2563eb; text-decoration: none; margin: 0 10px;"><img src="{{ $iconBase }}/facebook.png" alt="" width="18" height="18" style="{{ $iconStyle }}">Facebook</a>
        @if(!empty($f['whatsapp_link']))<span style="color: #9ca3af;">|</span>@endif
        @endif
        @if(!empty($f['whatsapp_link']))
        <a href="{{ $f['whatsapp_link'] }}" style="color: #2563eb; text-decoration: none; margin: 0 10px;"><img src="{{ $iconBase }}/whatsapp.png" alt="" width="18" height="18" style="{{ $iconStyle }}">WhatsApp</a>
        @endif
    </p>
    @if(!empty($f['tagline']))
    <p style="text-align: center; margin-top: 16px; color: #6b7280; font-size: 14px;">{{ $f['tagline'] }}</p>
    @endif
    @endif
</body>
</html>
