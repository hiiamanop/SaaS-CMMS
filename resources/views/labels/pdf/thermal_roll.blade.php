<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stiker Label Thermal</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #000;
            background: #fff;
        }
        .sticker-page {
            padding: 1.2mm 1.5mm;
            page-break-after: always;
            box-sizing: border-box;
        }
        .sticker-page:last-child {
            page-break-after: avoid;
        }
        .card {
            border: 1pt solid #000;
            border-radius: 1.5mm;
            padding: 1.2mm 1.5mm;
            box-sizing: border-box;
        }
        .brand-header {
            border-bottom: 0.8pt solid #000;
            padding-bottom: 1mm;
            margin-bottom: 1.2mm;
            display: table;
            width: 100%;
        }
        .brand-title {
            display: table-cell;
            font-size: 7pt;
            font-weight: bold;
            letter-spacing: 0.3pt;
            text-transform: uppercase;
        }
        .brand-sub {
            display: table-cell;
            text-align: right;
            font-size: 5pt;
            font-weight: bold;
            color: #333;
        }
        .body-table {
            width: 100%;
            border-collapse: collapse;
        }
        .col-info {
            vertical-align: top;
            padding-right: 1.5mm;
        }
        .col-qr {
            width: 22mm;
            text-align: right;
            vertical-align: middle;
        }
        .item-name {
            font-size: 7.5pt;
            font-weight: bold;
            line-height: 1.15;
            height: 18pt;
            overflow: hidden;
            margin-bottom: 0.8mm;
        }
        .badge-code {
            display: inline-block;
            font-family: 'Courier', monospace;
            font-size: 7pt;
            font-weight: bold;
            background: #000;
            color: #fff;
            padding: 0.3mm 1.2mm;
            border-radius: 0.6mm;
            margin-bottom: 0.8mm;
        }
        .meta-text {
            font-size: 5.5pt;
            line-height: 1.2;
            color: #222;
        }
        .meta-bold {
            font-weight: bold;
            color: #000;
        }
        .qr-image {
            width: 21mm;
            height: 21mm;
            display: block;
            margin-left: auto;
        }

        /* ── Format 50 x 30 mm (Mini / String Tag) ── */
        @if($format === 'roll_50x30')
        .sticker-page {
            padding: 1mm 1.2mm;
        }
        .card {
            border-width: 0.8pt;
            border-radius: 1mm;
            padding: 0.8mm 1mm;
        }
        .brand-header {
            border-bottom-width: 0.4pt;
            padding-bottom: 0.3mm;
            margin-bottom: 0.6mm;
        }
        .brand-title {
            font-size: 5.5pt;
        }
        .brand-sub {
            font-size: 4.5pt;
        }
        .col-qr {
            width: 16mm;
        }
        .qr-image {
            width: 15mm;
            height: 15mm;
        }
        .item-name {
            font-size: 6.2pt;
            height: 14pt;
            margin-bottom: 0.5mm;
        }
        .badge-code {
            font-size: 5.8pt;
            padding: 0.2mm 0.8mm;
            margin-bottom: 0.5mm;
        }
        .meta-text {
            font-size: 4.8pt;
            line-height: 1.15;
        }
        @endif

        /* ── Format 100 x 50 mm (Besar / Trafo / Inverter) ── */
        @if($format === 'roll_100x50')
        .sticker-page {
            padding: 2mm 2.5mm;
        }
        .card {
            border-width: 1.2pt;
            border-radius: 2mm;
            padding: 1.8mm 2.2mm;
        }
        .brand-title {
            font-size: 9.5pt;
        }
        .brand-sub {
            font-size: 6.5pt;
        }
        .col-qr {
            width: 32mm;
        }
        .qr-image {
            width: 30mm;
            height: 30mm;
        }
        .item-name {
            font-size: 10.5pt;
            height: 24pt;
            margin-bottom: 1.5mm;
        }
        .badge-code {
            font-size: 9pt;
            padding: 0.5mm 1.8mm;
            margin-bottom: 1.5mm;
        }
        .meta-text {
            font-size: 7pt;
            line-height: 1.25;
        }
        @endif
    </style>
</head>
<body>
    @foreach($labels as $label)
    <div class="sticker-page">
        <div class="card">
            <div class="brand-header">
                <div class="brand-title">ARUNA CMMS</div>
                <div class="brand-sub">PT ARUNA HIJAU POWER</div>
            </div>
            <table class="body-table">
                <tr>
                    <td class="col-info">
                        <div class="item-name">{{ $label['name'] }}</div>
                        <div>
                            <span class="badge-code">{{ $label['code'] }}</span>
                        </div>
                        <div class="meta-text">
                            <div><span class="meta-bold">Kat:</span> {{ $label['category'] }}</div>
                            <div><span class="meta-bold">Lok:</span> {{ $label['location'] }}</div>
                            @if(!empty($label['meta']))
                            <div>{{ $label['meta'] }}</div>
                            @endif
                        </div>
                    </td>
                    <td class="col-qr">
                        <img src="{{ $label['qr_base64'] }}" class="qr-image" alt="QR" />
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @endforeach
</body>
</html>
