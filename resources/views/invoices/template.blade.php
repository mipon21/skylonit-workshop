<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 0;
            size: A4 portrait;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 0;
            margin: 0 auto;
            background: white;
            position: relative;
        }

        .header {
            background: linear-gradient(135deg, #FF9966 0%, #FF6B6B 100%);
            height: 80mm;
            position: relative;
            padding: 20mm;
        }

        .logo {
            width: 50mm;
            height: 50mm;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24pt;
            font-weight: bold;
            color: #FF6B6B;
            position: absolute;
            top: 15mm;
            left: 15mm;
        }

        .company-info {
            position: absolute;
            top: 70mm;
            left: 20mm;
            right: 20mm;
            color: white;
        }

        .company-name {
            font-size: 32pt;
            font-weight: bold;
            margin-bottom: 3mm;
        }

        .company-tagline {
            font-size: 14pt;
            opacity: 0.95;
        }

        .content {
            padding: 15mm 20mm;
        }

        .invoice-title {
            text-align: center;
            font-size: 20pt;
            font-weight: bold;
            color: #FF6B6B;
            margin-bottom: 10mm;
            text-transform: uppercase;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 8mm;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 2mm 0;
            width: 35%;
        }

        .info-value {
            display: table-cell;
            padding: 2mm 0;
        }

        .section-title {
            font-size: 14pt;
            font-weight: bold;
            color: #FF6B6B;
            margin: 8mm 0 4mm 0;
            padding-bottom: 2mm;
            border-bottom: 2px solid #FF6B6B;
        }

        .message-box {
            background: #FFF5F0;
            border-left: 4px solid #FF6B6B;
            padding: 5mm;
            margin: 6mm 0;
            font-size: 10pt;
            line-height: 1.6;
        }

        .amount-box {
            background: #FFF5F0;
            border: 2px solid #FF6B6B;
            padding: 5mm;
            margin: 6mm 0;
            text-align: center;
        }

        .amount-label {
            font-size: 11pt;
            color: #666;
            margin-bottom: 2mm;
        }

        .amount-value {
            font-size: 24pt;
            font-weight: bold;
            color: #FF6B6B;
        }

        .conditions {
            font-size: 9pt;
            line-height: 1.6;
            margin-top: 8mm;
        }

        .conditions-title {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 3mm;
            color: #333;
        }

        .condition-item {
            margin-bottom: 3mm;
            padding-left: 5mm;
            text-indent: -5mm;
        }

        .note-box {
            background: #FFFACD;
            border: 1px solid #FFD700;
            padding: 4mm;
            margin-top: 8mm;
            font-size: 10pt;
        }

        .note-title {
            font-weight: bold;
            margin-bottom: 2mm;
        }

        .footer {
            position: absolute;
            bottom: 10mm;
            left: 20mm;
            right: 20mm;
            text-align: center;
            font-size: 9pt;
            color: #666;
            padding-top: 5mm;
            border-top: 1px solid #ddd;
        }

        .two-column {
            display: table;
            width: 100%;
        }

        .column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .column:first-child {
            padding-right: 5mm;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div class="logo">
                S-IT
            </div>
            <div class="company-info">
                <div class="company-name">Skylon-IT</div>
                <div class="company-tagline">Innovative Technology Solutions</div>
            </div>
        </div>

        <div class="content">
            <div class="invoice-title">
                Project Order Confirmation & Payment Receipt
            </div>

            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Invoice Date:</div>
                    <div class="info-value">{{ $invoice->invoice_date->format('d M Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Project Number:</div>
                    <div class="info-value">{{ $project->project_code }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Project Name:</div>
                    <div class="info-value">{{ $project->project_name }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Project Starting Date:</div>
                    <div class="info-value">{{ $project->contract_date ? $project->contract_date->format('d M Y') : 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Order Date:</div>
                    <div class="info-value">{{ $project->delivery_date ? $project->delivery_date->format('d M Y') : 'N/A' }}</div>
                </div>
            </div>

            <div class="section-title">Payment Details</div>

            <div class="two-column">
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-label">Contract Amount:</div>
                            <div class="info-value">{{ number_format($project->contract_amount, 0) }} BDT</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Advance (30%):</div>
                            <div class="info-value">{{ number_format($payment->amount, 0) }} BDT</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Payment By:</div>
                            <div class="info-value">{{ $client->name }}</div>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-label">Mobile:</div>
                            <div class="info-value">{{ $client->phone ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Email:</div>
                            <div class="info-value">{{ $client->email ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="message-box">
                <strong>BDT {{ number_format($payment->amount, 0) }} Has Been Received</strong>
                <br><br>
                The order has been confirmed by receiving the advance payment and the project will be delivered on time, InshaAllah.
            </div>

            <div class="conditions">
                <div class="conditions-title">Necessary conditions and attention:</div>

                <div class="condition-item">
                    • The probable date of project delivery has been mentioned. However, it may be slightly changed by mutual consent, considering the actual situation.
                </div>

                <div class="condition-item">
                    • Due to any technical issues or the delivery process of previous projects in the series, the delivery time of the said project may vary slightly with the consent of both parties.
                </div>

                <div class="condition-item">
                    • After project delivery, the client will retain ownership of all data (information/source code/other information) of the project and no information/source code or other information of the project will be reused by "Skylon-IT".
                </div>

                <div class="condition-item">
                    • The project must have the branding of "Skylon-IT" at the bottom. (If you do not want to keep it, you will not be provided with 6 months of free support later.)
                </div>

                <div class="condition-item">
                    • After the order of the project, if the client wants to cancel it for any special reason, the advance amount will not be refunded.
                </div>

                <div class="condition-item">
                    • After the delivery of the project, if any amendment is required in any part of the project, it has to be done within 7 days (excluding weekly holidays) from the date of delivery.
                </div>

                <div class="condition-item">
                    • The final handover of the project will be done after the payment of the full amount of the project.
                </div>
            </div>

            <div class="note-box">
                <div class="note-title">N:B:</div>
                <strong>Due Amount (70%): {{ number_format($dueAmount, 0) }} BDT</strong>
                <br>
                The due amount should be paid before final project delivery.
            </div>
        </div>

        <div class="footer">
            <strong>Skylon-IT</strong> | Innovative Technology Solutions<br>
            Email: info@skylon-it.com | Phone: +880 XXX-XXXXXXX<br>
            www.skylon-it.com
        </div>
    </div>
</body>
</html>
