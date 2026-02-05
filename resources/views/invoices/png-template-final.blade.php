{{-- Final Payment invoice – uses invoice-final.png. Add your image to public/images/invoice-final.png --}}
@php
    $bgPath = public_path('images/invoice-final.png');
    if (!file_exists($bgPath)) {
        $bgPath = public_path('images/invoice.png');
    }
    $bgBase64 = (file_exists($bgPath)) ? base64_encode(file_get_contents($bgPath)) : '';
    $wmColor = '#0D7E36';
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
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
        .watermark { font-family: "Noto Serif Bengali", serif; position: absolute; top: 50%; left: 50%; margin-left: -100pt; margin-top: -40pt; font-size: 100pt; font-weight: bold; opacity: 0.22; z-index: 1; color: {{ $wmColor }}; white-space: nowrap; transform: rotate(-30deg); }
    </style>
</head>
<body>
    <div class="page">
        @if($bgBase64)
        <img src="data:image/png;base64,{{ $bgBase64 }}" alt="" class="bg-img" />
        @endif
        <div class="watermark">PAID</div>
        <div class="field" style="top: 20.4%; left: 78%; font-weight: bold;">{{ $invoice->invoice_number }}</div>
        <div class="field" style="top: 20.4%; left: 25%; font-weight: bold;">{{ $project->project_code ?? '' }}</div>
        <div class="field" style="top: 23.5%; left: 80%; font-weight: bold;">{{ $invoice->invoice_date->format('d/m/Y') }}</div>
        <div class="field" style="top: 23.5%; left: 28%; font-weight: bold;">{{ $project->project_name ?? '' }}</div>
        <div class="field" style="top: 37.5%; left: 35%; font-weight: bold;">{{ number_format($project->contract_amount, 0) }} BDT</div>
        <div class="field" style="top: 40.5%; left: 26%; font-weight: bold;">{{ number_format($payment->amount, 0) }} BDT</div>
        <div class="field" style="top: 43.5%; left: 35%; font-weight: bold;">{{ $client->name ?? '' }}</div>
        <div class="field" style="top: 46.5%; left: 20%; font-weight: bold;">{{ $client->phone ?? '' }}</div>
        <div class="field" style="top: 46.5%; left: 60%; font-weight: bold;">{{ $client->email ?? '' }}</div>
    </div>
</body>
</html>
