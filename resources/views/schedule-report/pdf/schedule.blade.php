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
        .section-header td { background: #fef3c7; font-weight: bold; }
        .footer { text-align: right; font-size: 8px; color: #666; margin-top: 8px; }
    </style>
</head>
<body>
    <div style="text-align:right; font-size:9px; margin-bottom:4px;">FORMULIR</div>
    <h1>Preventive Maintenance Schedule</h1>
    <div class="meta">{{ $pltsLocation ?: 'Semua Lokasi' }} · Tahun {{ $year }}</div>
    @php
        $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $grouped = $schedules->groupBy('category');
    @endphp
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Alat</th>
                <th>Item Pekerjaan</th>
                <th>Tipe</th>
                <th>Shutdown</th>
                @foreach($months as $m)<th colspan="4">{{ $m }}</th>@endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($grouped as $cat => $items)
            <tr class="section-header"><td colspan="{{ 5 + 48 }}">{{ strtoupper($cat) }}</td></tr>
            @foreach($items as $i => $sched)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $sched->equipment_name }}</td>
                <td>{{ $sched->item_pekerjaan_text }}</td>
                <td>{{ $sched->type }}</td>
                <td>{{ $sched->shutdown_required ? 'Y' : 'N' }}</td>
                @php $plannedWeeks = $sched->planned_weeks ?? []; $plannedSet = collect($plannedWeeks)->map(fn($p) => $p['month'].'-'.$p['week'])->all(); @endphp
                @foreach(range(1,12) as $month)
                    @foreach(range(1,4) as $week)
                    <td style="text-align:center">{{ in_array("$month-$week", $plannedSet) ? '•' : '' }}</td>
                    @endforeach
                @endforeach
            </tr>
            @endforeach
            @endforeach
        </tbody>
    </table>
    <div class="footer">CMMS SaaS · {{ now()->format('d M Y H:i') }}</div>
</body>
</html>
