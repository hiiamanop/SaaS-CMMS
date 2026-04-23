<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 0; }
        .header { border: 1px solid #000; margin-bottom: 8px; }
        .header-top { display: flex; justify-content: space-between; padding: 6px 10px; border-bottom: 1px solid #000; }
        .header-meta { display: flex; }
        .header-meta td { border: 1px solid #000; padding: 4px 8px; font-size: 10px; }
        h1 { text-align: center; font-size: 13px; text-transform: uppercase; margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #000; padding: 4px 6px; font-size: 10px; }
        th { background: #f0f0f0; font-weight: bold; text-align: left; }
        .badge-p { background: #d1fae5; color: #065f46; padding: 1px 6px; border-radius: 4px; font-weight: bold; }
        .badge-x { background: #fee2e2; color: #991b1b; padding: 1px 6px; border-radius: 4px; font-weight: bold; }
        .section-header { background: #e5e7eb; font-weight: bold; }
        .signatures { margin-top: 20px; }
        .sig-table { width: 100%; }
        .sig-table td { width: 33%; border: 1px solid #000; padding: 40px 10px 10px; text-align: center; }
        .footer { text-align: right; font-size: 9px; color: #555; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-top">
            <div>
                <strong>PREVENTIVE MAINTENANCE</strong><br>
                <span style="font-size:12px; font-weight:bold;">CHECKSHEET {{ strtoupper($session->schedule->category) }}</span>
            </div>
            <div style="text-align:right; font-size:10px;">FORMULIR</div>
        </div>
        <table class="header-meta" style="margin:0; border:none;">
            <tr>
                <td>No. Dokumen: CS-{{ strtoupper(substr($session->schedule->frequency, 0, 3)) }}-001</td>
                <td>No. Revisi: 00</td>
                <td>Tanggal Berlaku: {{ now()->format('d/m/Y') }}</td>
                <td>Halaman: 1/1</td>
            </tr>
            <tr>
                <td>PLTS: {{ $session->plts_location }}</td>
                <td>Lokasi: {{ $session->equipment_location ?? '-' }}</td>
                <td>Periode: {{ $session->period_label }}</td>
                <td>Tahun: {{ $session->year }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:4%">No</th>
                <th style="width:30%">Item Inspeksi</th>
                <th style="width:18%">Metode Inspeksi</th>
                <th style="width:22%">Standar Ketentuan</th>
                <th style="width:8%; text-align:center;">Hasil</th>
                <th style="width:18%">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $groupedItems = [];
                foreach($items as $i) {
                    $lok = is_array($i) ? ($i['lokasi_inspeksi'] ?? '') : '';
                    $groupedItems[$lok][] = $i;
                }
                $idx = 1;
            @endphp
            @foreach($groupedItems as $lokasi => $groupItems)
            @if($lokasi)
            <tr>
                <td colspan="6" style="background:#f9f9f9; font-weight:bold; text-align:left; font-size:11px;">LOKASI: {{ strtoupper($lokasi) }}</td>
            </tr>
            @endif
            @foreach($groupItems as $item)
            @php
                $itemKey    = is_array($item) ? ($item['name']    ?? '') : $item;
                $itemMetode = is_array($item) ? ($item['metode']  ?? '') : '';
                $itemStandar= is_array($item) ? ($item['standar'] ?? '') : '';
                $res = $results[$itemKey] ?? null;
            @endphp
            <tr>
                <td>{{ $idx++ }}</td>
                <td>{{ $itemKey }}</td>
                <td>{{ $itemMetode ?: '—' }}</td>
                <td>{{ $itemStandar ?: '—' }}</td>
                <td style="text-align:center;">
                    @if($res?->result === 'P')
                        <span class="badge-p">P</span>
                    @elseif($res?->result === 'X')
                        <span class="badge-x">X</span>
                    @else
                        —
                    @endif
                </td>
                <td>{{ $res?->notes ?? '' }}</td>
            </tr>
            @endforeach
            @endforeach
        </tbody>
    </table>

    @if($session->abnormals->isNotEmpty())
    <table>
        <thead>
            <tr class="section-header"><th colspan="5">Catatan Abnormal</th></tr>
            <tr>
                <th>Tanggal</th>
                <th>Deskripsi Abnormal</th>
                <th>Penanganan</th>
                <th>Tgl Selesai</th>
                <th>PIC</th>
            </tr>
        </thead>
        <tbody>
            @foreach($session->abnormals as $ab)
            <tr>
                <td>{{ $ab->tanggal?->format('d/m/Y') }}</td>
                <td>{{ $ab->abnormal_description }}</td>
                <td>{{ $ab->penanganan }}</td>
                <td>{{ $ab->tgl_selesai?->format('d/m/Y') }}</td>
                <td>{{ $ab->pic }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="signatures">
        <table class="sig-table">
            <tr>
                <td>
                    <strong>Dibuat oleh (Teknisi ONM)</strong><br><br><br>
                    {{ $session->signed_by_teknisi ?? '___________________' }}<br>
                    <small>{{ $session->signed_date_teknisi?->format('d M Y') ?? '' }}</small>
                </td>
                <td>
                    <strong>Diperiksa oleh (SPV ONM)</strong><br><br><br>
                    {{ $session->signed_by_spv ?? '___________________' }}<br>
                    <small>{{ $session->signed_date_spv?->format('d M Y') ?? '' }}</small>
                </td>
                <td>
                    <strong>Disetujui oleh (PM)</strong><br><br><br>
                    {{ $session->signed_by_pm ?? '___________________' }}<br>
                    <small>{{ $session->signed_date_pm?->format('d M Y') ?? '' }}</small>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">Digenerate oleh CMMS SaaS · {{ now()->format('d M Y H:i') }}</div>
</body>
</html>
