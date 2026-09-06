<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stiker Label Lembar A4</title>
    <style>
        @page {
            margin: 10mm 8mm;
            size: a4 portrait;
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
        .grid-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 2.5mm 3mm;
        }
        .sticker-cell {
            width: 33.33%;
            height: 31mm;
            vertical-align: top;
            padding: 0;
        }
        .card {
            border: 0.8pt dashed #9ca3af;
            border-radius: 1.5mm;
            padding: 1.8mm 2.2mm;
            height: 100%;
            position: relative;
        }
        .brand-header {
            border-bottom: 0.5pt solid #9ca3af;
            padding-bottom: 0.8mm;
            margin-bottom: 1.2mm;
            display: table;
            width: 100%;
        }
        .brand-title {
            display: table-cell;
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .brand-sub {
            display: table-cell;
            text-align: right;
            font-size: 5pt;
            color: #4b5563;
            font-weight: bold;
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
            width: 20mm;
            text-align: center;
            vertical-align: middle;
        }
        .item-name {
            font-size: 7.2pt;
            font-weight: bold;
            line-height: 1.15;
            max-height: 2.3em;
            overflow: hidden;
            margin-bottom: 1mm;
        }
        .badge-code {
            display: inline-block;
            font-family: 'Courier', monospace;
            font-size: 6.5pt;
            font-weight: bold;
            background: #000;
            color: #fff;
            padding: 0.4mm 1.2mm;
            border-radius: 0.8mm;
            margin-bottom: 1mm;
        }
        .meta-text {
            font-size: 5.2pt;
            line-height: 1.2;
            color: #374151;
        }
        .meta-bold {
            font-weight: bold;
            color: #000;
        }
        .qr-image {
            width: 19mm;
            height: 19mm;
            display: block;
            margin: 0 auto;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    @php
        $chunks = $labels->chunk(24); // 24 labels per A4 page (3 cols x 8 rows)
    @endphp

    @foreach($chunks as $pageIndex => $pageLabels)
    <div class="{{ $loop->last ? '' : 'page-break' }}">
        <table class="grid-table">
            @php
                $rows = $pageLabels->chunk(3);
            @endphp
            @foreach($rows as $row)
            <tr>
                @foreach($row as $label)
                <td class="sticker-cell">
                    <div class="card">
                        <div class="brand-header">
                            <div class="brand-title">ARUNA CMMS</div>
                            <div class="brand-sub">PT AHP</div>
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
                </td>
                @endforeach
                @for($i = 0; $i < (3 - count($row)); $i++)
                <td class="sticker-cell"></td>
                @endfor
            </tr>
            @endforeach
        </table>
    </div>
    @endforeach
</body>
</html>
