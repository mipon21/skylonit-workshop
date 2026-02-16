{{-- Support Package invoice – uses invoice-support.png --}}
@php
    $bgPath = public_path('images/invoice-support.png');
    if (!file_exists($bgPath)) {
        $bgPath = public_path('images/invoice.png');
    }
    $bgBase64 = (file_exists($bgPath)) ? base64_encode(file_get_contents($bgPath)) : '';
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Support Invoice {{ $supportPackage->invoice_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @page { margin: 0; size: A4 portrait; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Noto Serif Bengali", serif; font-size: 12pt; color: #333; }
        .page { width: 210mm; min-height: 297mm; position: relative; overflow: hidden; }
        .bg-img { position: absolute; top: 0; left: 0; width: 100%; height: auto; z-index: 0; }
        .field { position: absolute; font-size: 12pt; color: #333; z-index: 2; font-family: "Noto Serif Bengali", serif; }
    </style>
</head>
<body>
    <div class="page">
        @if($bgBase64)
        <img src="data:image/png;base64,{{ $bgBase64 }}" alt="" class="bg-img" />
        @endif
        {{-- Project Number --}}
        <div class="field" style="top: 20.4%; left: 25%; font-weight: bold;">{{ $project->project_code ?? $project->formatted_id ?? '' }}</div>
        {{-- INV NO --}}
        <div class="field" style="top: 20.4%; left: 78%; font-weight: bold;">{{ $supportPackage->invoice_number ?? '' }}</div>
        {{-- Project Name --}}
        <div class="field" style="top: 23.5%; left: 28%; font-weight: bold;">{{ $project->project_name ?? '' }}</div>
        {{-- Date (Payment date) --}}
        <div class="field" style="top: 23.5%; left: 80%; font-weight: bold;">{{ $supportPackage->paid_at ? $supportPackage->paid_at->format('d/m/Y') : now()->format('d/m/Y') }}</div>
        {{-- Support Package (Payment For: Support Facility Support Package) --}}
        <div class="field" style="top: 37.5%; left: 58%; font-weight: bold; max-width: 75%;">{{ $supportPackage->package_label ?? '' }}</div>
        {{-- Payment Amount --}}
        <div class="field" style="top: 40.5%; left: 30%; font-weight: bold;">{{ number_format($supportPackage->amount, 0) }} BDT</div>
        {{-- Payment By (Client name) --}}
        <div class="field" style="top: 43.5%; left: 35%; font-weight: bold;">{{ $client->name ?? '' }}</div>
        {{-- Mobile --}}
        <div class="field" style="top: 46.5%; left: 20%; font-weight: bold;">{{ $client->phone ?? '' }}</div>
        {{-- Email --}}
        <div class="field" style="top: 46.5%; left: 60%; font-weight: bold;">{{ $client->user?->email ?? $client->email ?? '' }}</div>
    </div>
</body>
</html>
