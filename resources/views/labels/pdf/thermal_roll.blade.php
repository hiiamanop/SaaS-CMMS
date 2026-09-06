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
            width: 100%;
            height: 100%;
            padding: 2.2mm 2.8mm;
            page-break-after: always;
            position: relative;
        }
        .sticker-page:last-child {
            page-break-after: avoid;
        }
        .card {
            width: 100%;
            height: 100%;
            border: 1.2pt solid #000;
            border-radius: 2mm;
            padding: 1.8mm 2.2mm;
            position: relative;
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
            font-size: 7.5pt;
            font-weight: bold;
            letter-spacing: 0.3pt;
            text-transform: uppercase;
        }
        .brand-sub {
            display: table-cell;
            text-align: right;
            font-size: 5.5pt;
            font-weight: bold;
            color: #333;
        }
        .body-table {
            width: 100%;
            border-collapse: collapse;
        }
        .col-info {
            vertical-align: top;
            padding-right: 2mm;
        }
        .col-qr {
            width: 26mm;
            text-align: center;
            vertical-align: middle;
        }
        .item-name {
            font-size: 8.5pt;
            font-weight: bold;
            line-height: 1.15;
            max-height: 2.4em;
            overflow: hidden;
            margin-bottom: 1.5mm;
        }
        .badge-code {
            display: inline-block;
            font-family: 'Courier', monospace;
            font-size: 8pt;
            font-weight: bold;
            background: #000;
            color: #fff;
            padding: 0.6mm 1.6mm;
            border-radius: 1mm;
            margin-bottom: 1.2mm;
        }
        .meta-text {
            font-size: 6pt;
            line-height: 1.2;
            color: #222;
        }
        .meta-bold {
            font-weight: bold;
            color: #000;
        }
        .qr-image {
            width: 24mm;
            height: 24mm;
            display: block;
            margin: 0 auto;
        }
        /* Format 50x30 Compact adjustments */
        @if($format === 'roll_50x30')
        .sticker-page { padding: 1.5mm 1.8mm; }
        .card { padding: 1.2mm 1.5mm; border-width: 0.9pt; }
        .brand-title { font-size: 6pt; }
        .brand-sub { font-size: 5pt; }
        .col-qr { width: 18mm; }
        .qr-image { width: 16.5mm; height: 16.5mm; }
        .item-name { font-size: 6.8pt; }
        .badge-code { font-size: 6.5pt; padding: 0.4mm 1.2mm; }
        .meta-text { font-size: 5pt; }
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
