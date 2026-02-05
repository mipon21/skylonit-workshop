<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $mailSubject ?? '' }}</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    @if(!empty($logoUrl))
    <div style="margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #e5e7eb;">
        <img src="{{ $logoUrl }}" alt="{{ config('app.name') }}" style="max-height: 48px; width: auto; display: block;" />
    </div>
    @endif
    {!! $body ?? '' !!}
</body>
</html>
