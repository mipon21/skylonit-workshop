@php
    $bgPath = public_path('images/invoice.png');
    $bgBase64 = (file_exists($bgPath)) ? base64_encode(file_get_contents($bgPath)) : '';
    $wmColor = $invoice->payment_status === 'PAID' ? '#0D7E36' : ($invoice->payment_status === 'PARTIAL' ? '#f59e0b' : '#ef4444');
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
        body {
            font-family: "Noto Serif Bengali", serif;
            font-optical-sizing: auto;
            font-weight: 400;
            font-style: normal;
            font-variation-settings: "wdth" 100;
            font-size: 12pt;
            color: #333;
        }
        .page {
            width: 210mm;
            min-height: 297mm;
            position: relative;
            overflow: hidden;
        }
        .bg-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: auto;
            z-index: 0;
        }
        .field {
            position: absolute;
            font-size: 12pt;
            color: #333;
            z-index: 2;
            font-family: "Noto Serif Bengali", serif;
            font-optical-sizing: auto;
            font-weight: 400;
            font-style: normal;
            font-variation-settings: "wdth" 100;
        }
        .watermark {
            font-family: "Noto Serif Bengali", serif;
            position: absolute;
            top: 50%;
            left: 50%;
            margin-left: -100pt;
            margin-top: -40pt;
            font-size: 100pt;
            font-weight: bold;
            opacity: 0.12;
            z-index: 1;
            color: {{ $wmColor }};
            white-space: nowrap;
            -webkit-transform: rotate(-30deg);
            transform: rotate(-30deg);
        }
    </style>
</head>
<body>
    <div class="page">
        @if($bgBase64)
        <img src="data:image/png;base64,{{ $bgBase64 }}" alt="" class="bg-img" />
        @endif
        <div class="watermark">{{ $invoice->payment_status }}</div>
        <div class="field" style="top: 20.4%; left: 78%; font-weight: bold;">{{ $invoice->invoice_number }}</div>
        <div class="field" style="top: 20.4%; left: 25%; font-weight: bold;">{{ $project->project_code ?? '' }}</div>
        <div class="field" style="top: 23.5%; left: 80%; font-weight: bold;">{{ $invoice->invoice_date->format('d/m/Y') }}</div>
        <div class="field" style="top: 23.5%; left: 28%; font-weight: bold;">{{ $project->project_name ?? '' }}</div>
        <div class="field" style="top: 27.5%; left: 28%; font-weight: bold;">{{ $project->contract_date ? $project->contract_date->format('d/m/Y') : '' }}</div>
        <div class="field" style="top: 27.5%; left: 80%; font-weight: bold;">{{ $project->delivery_date ? $project->delivery_date->format('d/m/Y') : '' }}</div>
        <div class="field" style="top: 37.5%; left: 35%; font-weight: bold;">{{ number_format($project->contract_amount, 0) }} BDT</div>
        <div class="field" style="top: 40.5%; left: 26%; font-weight: bold;">{{ number_format($payment->amount, 0) }} BDT</div>
        <div class="field" style="top: 43.5%; left: 35%; font-weight: bold;">{{ $client->name ?? '' }}</div>
        <div class="field" style="top: 46.5%; left: 20%; font-weight: bold;">{{ $client->phone ?? '' }}</div>
        <div class="field" style="top: 46.5%; left: 60%; font-size: 12pt; font-weight: bold;">{{ $client->email ?? '' }}</div>
        <div class="field" style="top: 56.7%; left: 28%; font-weight: bold; color: #cc0000;">{{ number_format($due, 0) }} BDT</div>
    </div>
</body>
</html>
