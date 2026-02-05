@php
    $logoPath = public_path('images/invoice-background.jpg');
    $logoBase64 = (file_exists($logoPath)) ? base64_encode(file_get_contents($logoPath)) : '';
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        @page { margin: 0; size: A4 portrait; }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000;
            background: white;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 0;
            margin: 0 auto;
            background: white;
            position: relative;
        }

        /* Header - solid orange (DOMPDF does not render gradients) */
        .header {
            background-color: #FF9933;
            padding: 18px 20px;
            color: white;
            display: table;
            width: 100%;
            border-bottom: 3px solid #CC6600;
        }

        .header-left { display: table-cell; vertical-align: middle; width: 70%; }
        .header-right { display: table-cell; vertical-align: middle; width: 30%; text-align: right; }

        .header-logo {
            max-width: 90px;
            max-height: 90px;
            vertical-align: middle;
        }

        .company-name { font-size: 26pt; font-weight: bold; letter-spacing: 2px; color: #fff; }
        .tagline { font-size: 11pt; margin-top: 3px; color: #fff; }
        .address { font-size: 9pt; margin-top: 4px; color: #fff; }

        /* Title - centered in header area */
        .title {
            text-align: center;
            background-color: #f8f8f8;
            padding: 10px;
            font-size: 14pt;
            font-weight: bold;
            border-bottom: 2px solid #FF9933;
        }

        /* Content */
        .content { padding: 15px 20px; }

        /* Info rows */
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .info-label {
            display: table-cell;
            font-weight: bold;
            width: 30%;
            padding: 5px 10px 5px 0;
            vertical-align: top;
        }

        .info-value {
            display: table-cell;
            width: 70%;
            padding: 5px 0;
            border-bottom: 1px dotted #ccc;
        }

        /* Section box */
        .section {
            background: #f9f9f9;
            border: 2px solid #ddd;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }

        .section-title {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
            text-transform: uppercase;
        }

        .checkbox-item { margin: 8px 0; padding-left: 20px; position: relative; }
        .checkbox-item:before { content: "○"; position: absolute; left: 0; font-size: 14pt; }

        /* Two columns */
        .two-cols { display: table; width: 100%; }
        .col-50 { display: table-cell; width: 50%; padding-right: 10px; vertical-align: top; }
        .col-50:last-child { padding-right: 0; padding-left: 10px; }

        /* Conditions */
        .conditions { margin: 15px 0; }
        .condition-item {
            margin: 6px 0 6px 15px;
            padding-left: 10px;
            position: relative;
            font-size: 9pt;
            line-height: 1.5;
        }
        .condition-item:before { content: "○"; position: absolute; left: -10px; }

        /* Due box */
        .due-box {
            background: #f0f0f0;
            border: 2px solid #666;
            padding: 12px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .due-box .label { font-weight: bold; font-size: 11pt; }
        .due-box .amount { font-size: 14pt; font-weight: bold; color: #cc0000; }

        /* Agreement */
        .agreement { margin: 20px 0; font-size: 9pt; text-align: center; }
        .signature-line { border-top: 1px solid #000; width: 200px; margin: 30px auto 5px auto; }

        /* Footer */
        .footer {
            text-align: center;
            font-size: 8pt;
            color: #666;
            padding: 10px;
            border-top: 2px solid #FF9933;
            margin-top: 20px;
        }

        /* Watermark */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            margin-top: -60pt;
            margin-left: -120pt;
            font-size: 120pt;
            font-weight: bold;
            opacity: 0.12;
            z-index: -1;
            color: {{ $invoice->payment_status === 'PAID' ? '#22c55e' : ($invoice->payment_status === 'PARTIAL' ? '#f59e0b' : '#ef4444') }};
            white-space: nowrap;
            transform: rotate(-30deg);
        }

        .note { font-style: italic; font-size: 9pt; color: #666; text-align: center; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="watermark">{{ $invoice->payment_status }}</div>

    <div class="page">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="company-name">SKYLON-IT</div>
                <div class="tagline">App &amp; Web Development Service</div>
                <div class="address">40/14 R/A Jalalabad, Sylhet Sadar, Sylhet – 3100</div>
            </div>
            <div class="header-right">
                @if($logoBase64)
                <img src="data:image/jpeg;base64,{{ $logoBase64 }}" alt="Skylon-IT" class="header-logo" />
                @else
                <div style="width: 80px; height: 80px; background: #003366; display: inline-block; border-radius: 4px;"></div>
                @endif
            </div>
        </div>

        <!-- Title -->
        <div class="title">Project Order Confirmation & Payment Receipt</div>

        <!-- Content -->
        <div class="content">
            <div class="info-row">
                <div class="info-label">Project Number</div>
                <div class="info-value">{{ $project->project_code ?? '_________' }}
                    <span style="float: right; font-weight: bold;">Order Date: {{ $invoice->invoice_date->format('d/m/Y') }}</span>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">Project Name:</div>
                <div class="info-value">{{ $project->project_name ?? '_________' }}</div>
            </div>

            <div class="info-row">
                <div class="info-label">Project Starting Date:</div>
                <div class="info-value">{{ $project->contract_date ? $project->contract_date->format('d/m/Y') : '_________' }}
                    <span style="margin-left: 40px;">Delivery Date(Roughly): {{ $project->delivery_date ? $project->delivery_date->format('d/m/Y') : '_________' }}</span>
                </div>
            </div>

            <!-- Order Confirmation Section -->
            <div class="section">
                <div class="section-title">Order Confirmation Details:</div>

                <div class="checkbox-item">
                    Your project order has been officially confirmed in accordance with the previously agreed "T & C".
                </div>

                <div class="two-cols" style="margin-top: 10px;">
                    <div class="col-50">
                        <div class="info-row">
                            <div class="info-label">Contract Amount:</div>
                            <div class="info-value" style="font-weight: bold;">{{ number_format($project->contract_amount, 0) }} BDT</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Advance({{ round(($payment->amount / $project->contract_amount) * 100) }}%):</div>
                            <div class="info-value" style="font-weight: bold;">{{ number_format($payment->amount, 0) }} BDT Has Been Received</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Payment By:</div>
                            <div class="info-value">{{ $client->name ?? '_________' }}</div>
                        </div>
                    </div>
                    <div class="col-50">
                        <div class="info-row">
                            <div class="info-label">Mobile:</div>
                            <div class="info-value">{{ $client->phone ?? '_________' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Email:</div>
                            <div class="info-value" style="font-size: 9pt;">{{ $client->email ?? '_________' }}</div>
                        </div>
                    </div>
                </div>

                <div class="checkbox-item" style="margin-top: 10px;">
                    The order has been confirmed by receiving the advance payment and the project will be delivered on time, InshaAllah.
                </div>
            </div>

            <!-- Necessary Conditions -->
            <div class="conditions">
                <div style="font-weight: bold; margin-bottom: 8px;">Necessary conditions and attention:</div>
                <div class="condition-item">The probable date of project delivery has been mentioned. However, it may be slightly changed by mutual consent, considering the actual situation.</div>
                <div class="condition-item">Due to any technical issues or the delivery process of previous projects in the series, the delivery time of the said project may vary slightly with the consent of both parties.</div>
                <div class="condition-item">After project delivery, the client will retain ownership of all data of the project and no information will be reused by "Skylon-IT".</div>
                <div class="condition-item">The project must have the branding of "Skylon-IT" at the bottom. (If you do not want to keep it, you will not be provided with 6 months of free support later.)</div>
                <div class="condition-item">Advance payments made after project confirmation and commencement are non-refundable.</div>
                <div class="condition-item">"Skylon-IT" will be bound to fulfill all the conditions mentioned in the proposal details.</div>
                <div class="condition-item">"Skylon-IT" will not accept any liability if any illegal/hate crime is committed in connection with the project after the project is handed over.</div>
            </div>

            <!-- Due Amount Box -->
            <div class="due-box">
                <div class="label">N:B:</div>
                <div class="amount">Due Amount ({{ round((($project->contract_amount - $payment->amount) / $project->contract_amount) * 100) }}%): {{ number_format($due, 0) }} BDT</div>
                <div style="margin-top: 5px; font-size: 9pt;">must be paid at the time of project delivery.</div>
            </div>

            <!-- Agreement -->
            <div class="agreement">
                <strong>AGREEMENT</strong><br>
                By signing below, I acknowledge that I have read, understood, and agree to the checklist and the hash lit procedure described above.
                <div class="signature-line"></div>
                <div>[This is an Auto Generated Payment Receipt and Doesn't Needs a Signature]</div>
            </div>

            <div class="note">Invoice Number: {{ $invoice->invoice_number }} | Generated: {{ $invoice->invoice_date->format('d M Y') }}</div>
        </div>

        <!-- Footer -->
        <div class="footer"><strong>Skylon-IT | Innovative Technology Solutions</strong></div>
    </div>
</body>
</html>
