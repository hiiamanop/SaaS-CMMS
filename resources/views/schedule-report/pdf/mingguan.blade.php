<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 9px; margin: 10px; }
        h1 { text-align: center; font-size: 12px; text-transform: uppercase; }
        .meta { text-align: center; font-size: 10px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #999; padding: 2px 4px; font-size: 8px; }
        th { background: #e5e7eb; font-weight: bold; text-align: center; }
        .section-header td { background: #e5e7eb; font-weight: bold; }
        .badge-p { background: #d1fae5; color: #065f46; padding: 0 4px; border-radius: 3px; font-weight: bold; }
        .badge-x { background: #fee2e2; color: #991b1b; padding: 0 4px; border-radius: 3px; font-weight: bold; }
    </style>
</head>
<body>
    <div style="text-align:right; font-size:9px; margin-bottom:4px;">FORMULIR</div>
    <h1>Checksheet Mingguan</h1>
    <div class="meta">{{ $pltsLocation ?: 'Semua Lokasi' }} · Tahun {{ $year }}</div>
    @php
        $weeklyType = // removed
        $weeklySessions = // removed
        $templates = $weeklyType ? \App\Models\// removed
        $grouped = $templates->groupBy('lokasi_inspeksi');
        $monthNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    @endphp
    <table>
        <thead>
            <tr>
                <th style="min-width:80px">Lokasi Inspeksi</th>
                <th style="min-width:150px">Item Inspeksi</th>
                <th style="min-width:100px">Metode</th>
                <th style="min-width:120px">Standar</th>
                @foreach(range(1,12) as $m)
                <th colspan="4">{{ $monthNames[$m-1] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($grouped as $lokasi => $items)
            <tr class="section-header"><td colspan="{{ 4+48 }}">{{ $lokasi }}</td></tr>
            @foreach($items as $template)
            <tr>
                <td></td>
                <td>{{ $template->item_inspeksi }}</td>
                <td>{{ $template->metode_inspeksi }}</td>
                <td>{{ $template->standar_ketentuan }}</td>
                @foreach(range(1,12) as $month)
                    @foreach([1,2,3,4] as $week)
                    @php $session = $weeklySessions->first(fn($s) => $s->month == $month && $s->week_number == $week); $result = $session ? $session->results->firstWhere('template_id', $template->id) : null; @endphp
                    <td style="text-align:center">
                        @if($result?->result === 'P') <span class="badge-p">P</span>
                        @elseif($result?->result === 'X') <span class="badge-x">X</span>
                        @else —
                        @endif
                    </td>
                    @endforeach
                @endforeach
            </tr>
            @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>
